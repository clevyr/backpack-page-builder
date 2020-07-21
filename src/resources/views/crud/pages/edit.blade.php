@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => backpack_url('dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.edit') => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
            <small>
                Editing

                <span class="font-weight-bold">
                    {{ $entry->title }}
                </span>
            </small>

        @if ($crud->hasAccess('list'))
          <small><a href="{{ url($crud->route) }}" class="hidden-print font-sm"><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
        @endif

        <small>
            <a href="{{ url($entry->slug) }}" title="Preview Page" class="btn btn-link font-sm ml-auto" target="_blank" rel="noopener noreferrer">
                <span class="la la-external-link-alt"></span>

                Preview Page
            </a>
        </small>
	  </h2>
	</section>
@endsection

@section('content')
<div class="row">
	<div class="{{ $crud->getEditContentClass() }}">
		<!-- Default box -->

		@include('crud::inc.grouped_errors')

		  <form method="post"
		  		action="{{ url($crud->route.'/'.$entry->getKey()) }}"
				@if ($crud->hasUploadFields('update', $entry->getKey()))
				enctype="multipart/form-data"
				@endif
		  		>
		  {!! csrf_field() !!}
		  {!! method_field('PUT') !!}

		  	@if ($crud->model->translationEnabled())
		    <div class="mb-2 text-right">
		    	<!-- Single button -->
				<div class="btn-group">
				  <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				    {{trans('backpack::crud.language')}}: {{ $crud->model->getAvailableLocales()[request()->input('locale')?request()->input('locale'):App::getLocale()] }} &nbsp; <span class="caret"></span>
				  </button>
				  <ul class="dropdown-menu">
				  	@foreach ($crud->model->getAvailableLocales() as $key => $locale)
					  	<a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?locale={{ $key }}">{{ $locale }}</a>
				  	@endforeach
				  </ul>
				</div>
		    </div>
		    @endif

            @include('pagebuilder::crud.form.form_content', ['fields' => $crud->fields(), 'action' => 'edit'])

            @include('crud::inc.form_save_buttons')
		  </form>
	</div>
</div>
@endsection

@section('before_styles')
    <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/revisions.css') }}">
@endsection

@push('after_scripts')
    <script src="{{ asset('packages/backpack/crud/js/revisions.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            var title = $('[name="title"]'),
                slug = $('[name="slug"]'),
                save_actions = $('#saveActions .btn');

            title.keyup(function (e) {
                if (slug.val().toLowerCase() !== '/') {
                    slug.val(title.val()
                        .toLowerCase()
                        .replace(/ /g, '-')
                        .replace(/-\s*$/, ""));
                }
            });

            // Set is dirty
            var is_dirty = false;

            // if input is focused then it is considered dirty
            $('#page-editor-content .form-control').focus(function () {
                is_dirty = true;
            });

            // Do not trigger beforeunload if an action item is clicked
            $.each(save_actions, function (item) {
                $(this).on('click', function () {
                   is_dirty = false;
                });
            });

            // beforeunload event
            $(window).on('beforeunload', function () {
                if (is_dirty) {
                    return 'Are you sure you want to leave this page? Your changes may be lost.';
                }
            });
        });
    </script>
@endpush

