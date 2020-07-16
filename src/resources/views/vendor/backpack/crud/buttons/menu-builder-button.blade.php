@if ($crud->get('reorder.enabled') && $crud->hasAccess('reorder'))
    <a href="{{ url($crud->route.'/reorder') }}" class="btn btn-outline-primary" title="Menu Builder" data-style="zoom-in">
        <span class="ladda-label"><i class="la la-arrows"></i>
            Menu Builder
        </span>
    </a>
@endif
