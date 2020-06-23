@php
    $horizontalTabs = $crud->getTabsType()=='horizontal' ? true : false;

    if ($errors->any() && array_key_exists(array_keys($errors->messages())[0], $crud->getCurrentFields()) &&
        array_key_exists('tab', $crud->getCurrentFields()[array_keys($errors->messages())[0]])) {
        $tabWithError = ($crud->getCurrentFields()[array_keys($errors->messages())[0]]['tab']);
    }
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
                <li role="presentation" class="nav-item">
                    <a href="#tab_{{ Str::slug($tab) }}"
                        aria-controls="tab_{{ Str::slug($tab) }}"
                        role="tab"
                        tab_name="{{ Str::slug($tab) }}"
                        data-toggle="tab"
                        class="nav-link {{ isset($tabWithError) ? ($tab == $tabWithError ? 'active' : '') : ($k == 0 ? 'active' : '') }}"
                        >{{ $tab }}</a>
                </li>
            @endforeach
                <li role="presentation" class="nav-item">
                    <a href="#tab_page-editor"
                       aria-controls="tab_page-editor"
                       role="tab"
                       tab_name="tab_page-editor"
                       data-toggle="tab"
                       class="nav-link"
                    >Page Editor</a>
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

            <div role="tabpanel" class="tab-pane" id="tab_page-editor">
                <div class="row">
                    @if ($is_dynamic)
                        @include('pagebuilder::crud.form.page_editor_dynamic')
                    @else
                        @include('pagebuilder::crud.form.page_editor')
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

