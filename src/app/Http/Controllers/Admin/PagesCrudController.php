<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderBaseController as CrudController;
use Clevyr\PageBuilder\app\Http\Requests\PageCrud\PageCreateRequest;
use Clevyr\PageBuilder\app\Models\PageSectionsPivot;
use Clevyr\PageBuilder\app\Models\PageView;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class PagesCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    /**
     * @var PageView $page_view
     */
    private PageView $page_view;

    /**
     * PagesCrudController constructor.
     */
    public function __construct()
    {
        $this->page_view = new PageView();

        parent::__construct();
    }

    public function setup()
    {
        $this->crud->setModel(config('backpack.pagebuilder.page_model_class', 'Clevyr\PageBuilder\app\Models\Page'));
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/pages');
        $this->crud->setEntityNameStrings('page', 'pages');

        $this->crud->setCreateView('pagebuilder::crud.pages.create');
        $this->crud->setEditView('pagebuilder::crud.pages.edit');

        parent::setup();
    }

    public function setupListOperation()
    {
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

    public function setupCreateOperation()
    {
        $this->crud->field('name')->type('text');
        $this->crud->field('title')->type('text');
        $this->crud->field('slug')->type('text');
        $this->crud->field('page_view_id')
            ->label('View')
            ->type('select_from_array')
            ->allows_null(false)
            ->options($this->page_view->viewOptions());

        $this->crud->setValidation(PageCreateRequest::class);
    }

    public function setupUpdateOperation()
    {
        $this->crud->field('name')->type('text')->tab('General Information');
        $this->crud->field('title')->type('text')->tab('General Information');
        $this->crud->field('slug')->type('text')->tab('General Information');
        $this->crud->field('page_view_id')
            ->label('View')
            ->type('select_from_array')
            ->options($this->page_view->viewOptions())
            ->allows_null(false)
            ->tab('General Information');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Application|Factory|View
     */
    public function edit($id)
    {
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

        // Sections
        $this->data['sections'] = $this->crud->getModel()
            ->with(['view', 'sections' => function ($query) {
                return $query->orderBy('order', 'ASC');
            }])
            ->first()
            ->sections
            ->toArray();

        // Sections Data
        $this->data['section_data'] = $this->crud
            ->getModel()
            ->first()
            ->sectionData()
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->section_id => $item->data];
            });

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // update the row in the db
        $item = $this->crud->update($request->get($this->crud->model->getKeyName()),
            $this->crud->getStrippedSaveRequest());

        $page_view_id = $this->crud
            ->getModel()
            ->first()
            ->view()
            ->first()
            ->id;

        $sections = $request->get('sections');

        foreach ($sections as $key => $section) {
            PageSectionsPivot::where([
                'page_view_id' => $page_view_id,
                'section_id' => $key
            ])->update(['data' => $section]);
        }

        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }
}
