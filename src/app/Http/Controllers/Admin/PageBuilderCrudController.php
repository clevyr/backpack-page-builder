<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderBaseController as CrudController;
use Clevyr\PageBuilder\app\Http\Requests\PageCrud\PageCreateRequest;
use Clevyr\PageBuilder\app\Http\Requests\PageCrud\PageUpdateRequest;
use Clevyr\PageBuilder\app\Models\Page;
use Clevyr\PageBuilder\app\Models\PageSection;
use Clevyr\PageBuilder\app\Models\PageSectionsPivot;
use Clevyr\PageBuilder\app\Models\PageView;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
class PageBuilderCrudController extends CrudController
{
    use AuthorizesRequests;
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ReorderOperation;

    /**
     * @var PageView $page_view
     */
    private PageView $page_view;

    /**
     * PageBuilderCrudController constructor.
     */
    public function __construct()
    {
        $this->page_view = new PageView();

        parent::__construct();
    }

    /**
     * Setup
     *
     * @throws Exception
     */
    public function setup()
    {
        $this->crud->setModel(config('backpack.pagebuilder.page_model_class', 'Clevyr\PageBuilder\app\Models\Page'));
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/pages');
        $this->crud->setEntityNameStrings('page', 'pages');

        $this->crud->setCreateView('pagebuilder::crud.pages.create');
        $this->crud->setEditView('pagebuilder::crud.pages.edit');

        parent::setup();
    }

    /**
     * Setup List Operation
     *
     * @throws AuthorizationException
     */
    protected function setupListOperation()
    {
        $this->authorize('view', Page::class);

        $this->crud->addClause('whereHas', 'activeViews');
        $this->crud->orderBy('lft', 'ASC');

        $this->crud->addColumn([
            'name' => 'title',
            'label' => 'Title',
            'limit' => 255,
        ]);

        $this->crud->addColumn([
            'name' => 'slug',
            'label' => 'Slug',
            'limit' => 255,
        ]);

        $this->crud->addColumn([
            'type' => 'relationship',
            'label' => 'Template',
            'name' => 'view',
            'entity' => 'view',
            'attribute' => 'name',
            'model' => PageView::class
        ]);

        // Buttons
        $this->crud->removeButton('delete');
        $this->crud->removeButton('reorder');

        $this->crud->addButtonFromView('top', 'menu-builder-button', 'menu-builder-button', 'end');
        $this->crud->addButtonFromView('line', 'delete-page-button', 'delete-page-button', 'end');
        $this->crud->addButtonFromView('line', 'preview-page-button', 'preview-page-button', 'beginning');

        // Filters
        $this->crud->addFilter([
            'type' => 'simple',
            'name' => 'trash',
            'label' => 'Trash'
        ],
            false,
            function () {
                $this->crud->addClause('onlyTrashed');

                // Buttons
                $this->crud->removeButton( 'delete-page-button', 'line');
                $this->crud->removeButton( 'update', 'line');

                $this->crud->addButtonFromView('line', 'restore-page-button', 'restore-page-button', 'beginning');
                $this->crud->addButtonFromView('line', 'force-delete-page-button', 'force-delete-page-button', 'end');
            });
    }

    /**
     * Display all rows in the database for this entity.
     *
     * @return View
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->crud->hasAccessOrFail('list');
        $this->authorize('view', Page::class);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getListView(), $this->data);
    }

    /**
     * Setup Create Operation
     *
     * @throws AuthorizationException
     */
    protected function setupCreateOperation()
    {
        $this->authorize('create', Page::class);

        $this->crud->field('title')
            ->type('text');

        $this->crud->field('slug')
            ->type('text');

        $this->crud->field('page_view_id')
            ->label('View')
            ->type('select_from_array')
            ->allows_null(false)
            ->options($this->page_view->viewOptions());

        $this->crud->setValidation(PageCreateRequest::class);
    }

    /**
     * Setup Update Operation
     *
     * @throws AuthorizationException
     */
    protected function setupUpdateOperation()
    {
        $this->authorize('update', Page::class);

        $this->crud->field('title')
            ->type('text')
            ->tab('Page Settings');

        $this->crud->field('slug')
            ->type('text')
            ->tab('Page Settings');

        $this->crud->field('hide_on_menu')
            ->type('checkbox')
            ->hint('If this is checked the page will not show on the navigation bar')
            ->tab('Page Settings');

        $this->crud->field('published_at')
            ->type('hidden');

        $this->crud->field('page_view_id')
            ->label('View')
            ->type('select_from_array')
            ->options($this->page_view->viewOptions())
            ->allows_null(false)
            ->tab('Page Settings');

         $this->crud->removeAllSaveActions();
//        $this->crud->setOperationSetting('save_actions', []);

        // Save Actions
        $this->crud->addSaveAction([
            'name' => 'save_and_edit_content',
            'redirect' => fn($crud, $request, $itemId) => backpack_url('pages/' . $itemId . '/edit#page-content'),
            'button_text' => 'Save and Edit Content',
            'order' => 0,
        ]);

        $this->crud->setOperationSetting('showCancelButton', false);

        // Validation
        $this->crud->setValidation(PageUpdateRequest::class);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');
        $this->authorize('update', Page::class);

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;
        // Set is dynamic
        $is_dynamic = $this->data['entry']->view()->first()->name === 'dynamic';

        $this->data['is_dynamic'] = $is_dynamic;

        // If is dynamic query all of the sections
        if ($is_dynamic) {
            $this->data['all_sections'] = PageSection::where('is_dynamic', true)
                ->get()
                ->toArray();
        }

        // Sections
        $this->data['sections'] = $this->crud->entry
            ->sections()
            ->when($is_dynamic, function (Builder $query) {
                return $query->orderBy('pivot_order', 'ASC');
            })
            ->when(!$is_dynamic, function (Builder $query) {
                return $query->orderBy('order', 'ASC');
            })
            ->get()
            ->toArray();

        $this->data['has_sections'] = count($this->data['sections']) > 0;
        $this->data['show_tooltip'] = $is_dynamic && count($this->data['sections']) <= 0;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    /**
     * Update
     *
     * @return array|bool|RedirectResponse|Response
     * @throws AuthorizationException
     */
    public function update()
    {
        $this->authorize('update', Page::class);

        // Set empty ids array, used for removing unused sections
        $ids = [];
        $request = $this->crud->getRequest();

        try {
            // execute the FormRequest authorization and validation, if one is required
            $this->crud->validateRequest();
        } catch (ValidationException $e) {
            if (Arr::has($e->validator->failed(), 'sections') ) {
                return redirect()
                    ->to(backpack_url('pages/' . $request->get('id') . '/edit#page-layout'))
                    ->withErrors($e->validator->getMessageBag())
                    ->withInput();
            }

            return redirect()
                ->to(backpack_url('pages/' . $request->get('id') . '/edit'))
                ->withErrors($e->validator->getMessageBag())
                ->withInput();
        }

        // update the row in the db
        $update = $this->crud->update($request->get($this->crud->model->getKeyName()),
            $this->crud->getStrippedSaveRequest());

        // Get the request sections
        $sections = $request->get('sections');
        $has_sections = is_array($sections) ? count($sections) > 0 : false;

        if ($has_sections) {
            // Update the sections
            foreach ($sections as $key => $section) {
                if (is_string($section)) {
                    $section = json_decode($section, true);
                }

                if (!isset($section['uuid'])) {
                    $operation = PageSectionsPivot::create([
                        'page_id' => $request->get('id'),
                        'section_id' => $section['id'],
                        'order' => isset($section['order']) ? $section['order'] : $key,
                    ]);
                } else {
                    $operation = PageSectionsPivot::where('uuid', $section['uuid'])->first();
                    $operation->update([
                        'data' => $section['data'],
                        'order' => isset($section['order']) ? $section['order'] : $key,
                    ]);
                }

                $ids[] = $operation instanceof PageSectionsPivot ? $operation->id : $operation;
            }
        }

        // Delete unused sections
        PageSectionsPivot::where('page_id', $request->get('id'))
            ->whereNotIn('id', $ids)
            ->delete();

        $this->data['entry'] = $this->crud->entry = $update;

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($update->getKey());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return string
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');
        $this->authorize('delete', Page::class);

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        return $this->crud->delete($id);
    }

    /**
     * Setup Reorder Operation
     */
    protected function setupReorderOperation()
    {
        // define which model attribute will be shown on draggable elements
        $this->crud->set('reorder.label', 'title');
        // define how deep the admin is allowed to nest the items
        // for infinite levels, set it to 0
        $this->crud->set('reorder.max_level', 2);
    }

    /**
     * Restore
     *
     * @param int $id
     * @return mixed
     * @throws AuthorizationException
     */
    public function restore(int $id)
    {
        $this->authorize('delete', Page::class);
        return $this->crud->getModel()
            ->withTrashed()
            ->findOrFail($id)
            ->restore();
    }

    /**
     * Force Delete
     *
     * @param int $id
     * @return mixed
     * @throws AuthorizationException
     */
    public function forceDelete(int $id)
    {
        $this->authorize('delete', Page::class);
        return $this->crud->getModel()
            ->withTrashed()
            ->findOrFail($id)
            ->forceDelete();
    }

    public function reorder ()
    {
        $this->crud->hasAccessOrFail('reorder');

        if (! $this->crud->isReorderEnabled()) {
            abort(403, 'Reorder is disabled.');
        }

        $this->crud->addClause('where', 'hide_on_menu', false);

        // get all results for that entity
        $this->data['entries'] = $this->crud->getEntries();
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.reorder').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getReorderView(), $this->data);
    }
}
