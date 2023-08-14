@php
  $horizontalTabs = $crud->getTabsType()=='horizontal' ? true : false;
  $tabWithError = (function() use ($crud) {
      if(! session()->get('errors')) {
          return false;
      }
      foreach(session()->get('errors')->getBags() as $bag => $errorMessages) {
          foreach($errorMessages->getMessages() as $fieldName => $messages) {
              if(array_key_exists($fieldName, $crud->getCurrentFields()) && array_key_exists('tab', $crud->getCurrentFields()[$fieldName])) {
                  return $crud->getCurrentFields()[$fieldName]['tab'];
              }
          }
      }
      return false;
  })();
@endphp

@push('crud_fields_styles')
    <style>
        .nav-tabs-custom {
            box-shadow: none;
        }
        .nav-tabs-custom > .nav-tabs.nav-stacked > li {
            margin-right: 0;
        }

        .tab-pane .form-group h1:first-child,
        .tab-pane .form-group h2:first-child,
        .tab-pane .form-group h3:first-child {
            margin-top: 0;
        }
    </style>
@endpush

@if ($crud->getFieldsWithoutATab()->filter(function ($value, $key) { return $value['type'] != 'hidden'; })->count())
  <div class="card">
    <div class="card-body row">
      @include('crud::inc.show_fields', ['fields' => $crud->getFieldsWithoutATab()])
    </div>
  </div>
@else
  @include('crud::inc.show_fields', ['fields' => $crud->getFieldsWithoutATab()])
@endif

<div class="tab-container {{ $horizontalTabs ? '' : 'container'}} mb-2">

    <div class="nav-tabs-custom {{ $horizontalTabs ? '' : 'row'}}" id="form_tabs">
        <ul class="nav {{ $horizontalTabs ? 'nav-tabs' : 'flex-column nav-pills'}} {{ $horizontalTabs ? '' : 'col-md-3' }}" role="tablist">
            @foreach ($crud->getTabs() as $k => $tab)
            @php
              $tabSlug = Str::slug($tab);
              if(empty($tabSlug)) {
                  $tabSlug = $k;
              }
            @endphp
            <li role="presentation" class="nav-item">
              <a href="#tab_{{ $tabSlug }}"
                 aria-controls="tab_{{ $tabSlug }}"
                 role="tab"
                 data-toggle="tab" {{-- tab indicator for Bootstrap v4 --}}
                 tab_name="{{ $tabSlug }}" {{-- tab name for Bootstrap v4 --}}
                 data-name="{{ $tabSlug }}" {{-- tab name for Bootstrap v5 --}}
                 data-bs-toggle="tab" {{-- tab name for Bootstrap v5 --}}
                 class="nav-link text-decoration-none {{ isset($tabWithError) && $tabWithError ? ($tab == $tabWithError ? 'active' : '') : ($k == 0 ? 'active' : '') }}"
              >{{ $tab }}</a>
            </li>
            @endforeach

            @if ($is_dynamic)
                <li role="presentation" class="nav-item">
                    <a href="#tab_page-layout"
                       aria-controls="tab_page-layout"
                       role="tab"
                       tab_name="tab_page-layout"
                       data-toggle="tab"
                       class="nav-link {{ isset($tabWithError) ? ($tab == $tabWithError ? 'active' : '') : ($k == 1 ? 'active' : '') }}"
                    >Page Layout</a>
                </li>
            @endif

            <li id="has-sections-tab"
                role="presentation" class="nav-item"
                {{ $show_tooltip ? 'data-tooltip=tooltip' : '' }}
                title="{{ $show_tooltip ? 'You must create your page layout before adding content.' : '' }}">
                <a href="#tab_page-content"
                   aria-controls="tab_page-content"
                   role="tab"
                   tab_name="tab_page-content"
                   data-toggle="tab"
                   {{ !$has_sections ? 'disabled' : '' }}
                   class="nav-link {{ !$has_sections ? 'disabled' : '' }} {{ isset($tabWithError) ? ($tab == $tabWithError ? 'active' : '') : ($k == 2 ? 'active' : '') }}"
                >
                    <span class="la la-exclamation-circle has-sections-icon {{ $has_sections ? 'd-none' : 'd-inline' }}"></span>
                    Page Content
                </a>
            </li>
        </ul>

        @php
            $col = '';

            if ($is_dynamic) {
                $col = 'col-md-12';
            } else {
                $col = $horizontalTabs ? '' : 'col-md-9';
            }
        @endphp

        <div class="tab-content p-0 {{ $col }}">
            @foreach ($crud->getTabs() as $k => $tab)
                <div role="tabpanel" class="tab-pane {{ isset($tabWithError) ? ($tab == $tabWithError ? ' active' : '') : ($k == 0 ? ' active' : '') }}" id="tab_{{ Str::slug($tab) }}">
                    <div class="row">
                        @include('crud::inc.show_fields', ['fields' => $crud->getTabFields($tab)])
                    </div>
                </div>
            @endforeach

            @if ($is_dynamic)
                <div role="tabpanel" class="tab-pane" id="tab_page-layout">
                    <div class="row">
                        @if ($is_dynamic)
                            @include('pagebuilder::crud.form.page_editor_dynamic_layout')
                        @else
                            @include('pagebuilder::crud.form.page_editor')
                        @endif
                    </div>
                </div>
            @endif

            <div role="tabpanel" class="tab-pane" id="tab_page-content">
                <div class="row">
                    @include('pagebuilder::crud.form.page_editor_dynamic_content')
                </div>
            </div>
        </div>
    </div>
</div>

@push('after_scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $('#has-sections-tab').tooltip();

        var has_sections = @json($has_sections);

        if (!has_sections && window.location.hash === '#page-content') {
            $('#tab_page-layout').tab('show');
        }
    });
</script>
@endpush

