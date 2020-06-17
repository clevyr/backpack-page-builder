<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderBaseController as CrudController;
use Clevyr\PageBuilder\app\Http\Requests\PageCrud\PageCreateRequest;
use Clevyr\PageBuilder\app\Models\Page;
use Clevyr\PageBuilder\app\Models\PageSection;
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
     * @var PageView $pageView
     */
    private PageView $pageView;

    private PageSection $pageSection;

    /**
     * PagesCrudController constructor.
     */
    public function __construct()
    {
        $this->pageView = new PageView();
        $this->pageSection = new PageSection();

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
            ->options($this->pageView->viewOptions());

        $this->crud->setValidation(PageCreateRequest::class);
    }

    public function setupUpdateOperation()
    {
        $this->crud->field('name')->type('text')->tab('General Information');
        $this->crud->field('title')->type('text')->tab('General Information');
        $this->crud->field('slug')->type('text')->tab('General Information');
        $this->crud->field('layout')
            ->type('select_from_array')
            ->options($this->pageView->viewOptions())
            ->tab('General Information');

        $this->crud->setValidation(PageCreateRequest::class);
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
        $this->data['sections'] = $this->crud->getModel()->with(['view', 'sections'])->first()->sections->toArray();

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }
}
