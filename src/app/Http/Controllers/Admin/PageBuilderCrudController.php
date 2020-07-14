<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
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
    public function setupListOperation()
    {
        $this->authorize('view', Page::class);

        $this->crud->addClause('whereHas', 'activeViews');

        $this->crud->addColumn('title');
        $this->crud->addColumn('name');
        $this->crud->addColumn('slug');
        $this->crud->addColumn('page_view_id');
        $this->crud->addColumn([
            'name' => 'created_at',
            'label' => 'Created At',
            'type' => 'datetime',
            'format' => 'D MMM Y'
        ]);
        $this->crud->addColumn([
            'name' => 'updated_at',
            'label' => 'Updated At',
            'type' => 'datetime',
            'format' => 'D MMM Y'
        ]);

        // Buttons
        $this->crud->removeButton('delete');
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
        });
    }

    /**
     * Display all rows in the database for this entity.
     *
     * @return View
     */
    public function index()
    {
        $this->crud->hasAccessOrFail('list');

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
    public function setupCreateOperation()
    {
        $this->authorize('create', Page::class);

        $this->crud->field('name')->type('text');
        $this->crud->field('title')->type('text');
        $this->crud->field('slug')->type('text');

        if (backpack_user()->hasRole('Super Admin')) {
            $this->crud->field('page_view_id')
                ->label('View')
                ->type('select_from_array')
                ->allows_null(false)
                ->options($this->page_view->viewOptions());
        }

        $this->crud->setValidation(PageCreateRequest::class);
    }

    /**
     * Setup Update Operation
     *
     * @throws AuthorizationException
     */
    public function setupUpdateOperation()
    {
        $this->authorize('update', Page::class);

        $this->crud->field('name')->type('text')->tab('Page Settings');
        $this->crud->field('title')->type('text')->tab('Page Settings');
        $this->crud->field('slug')->type('text')->tab('Page Settings');

        if (backpack_user()->hasRole('Super Admin')) {
            $this->crud->field('page_view_id')
                ->label('View')
                ->type('select_from_array')
                ->options($this->page_view->viewOptions())
                ->allows_null(false)
                ->tab('Page Settings');
        }

        // $this->crud->removeAllSaveActions() uses the wrong method and is bugged
        $this->crud->setOperationSetting('save_actions', []);

        $this->crud->addSaveAction([
            'name' => 'save_and_edit_content',
            'redirect' => function($crud, $request, $itemId) {
                return backpack_url('pages/' . $itemId . '/edit#page-content');
            },
            'button_text' => 'Save and Edit Content',
            'order' => 0,
        ]);

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
        $this->authorize('update', Page::class);

        $this->crud->hasAccessOrFail('update');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        $is_dynamic = $this->data['entry']->view()->first()->name === 'dynamic';

        $this->data['is_dynamic'] = $is_dynamic;

        $this->data['all_sections'] = PageSection::where('is_dynamic', true)
            ->get()
            ->toArray();

        // Sections
        $this->data['sections'] = $this->crud->entry
            ->sections()
            ->orderBy('order', 'ASC')
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
     * @return array|bool|RedirectResponse|\Illuminate\Http\Response
     */
    public function update()
    {
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

                if (!$section['uuid']) {
                    $operation = PageSectionsPivot::where('uuid', $section['uuid'])->create([
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
     */
    public function destroy($id)
    {
        if ($this->crud->getEntry($id)->view()->firstOrFail()->name !== 'dynamic') {
            abort(403);
        }

        $this->crud->hasAccessOrFail('delete');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        return $this->crud->delete($id);
    }
}
