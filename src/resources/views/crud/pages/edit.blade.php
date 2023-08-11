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
<form method="post"
      action="{{ url($crud->route.'/'.$entry->getKey()) }}"
      @if ($crud->hasUploadFields('update', $entry->getKey()))
        enctype="multipart/form-data"
      @endif>
    <div class="row">
        <div class="{{ $crud->getEditContentClass() }}">
            <!-- Default box -->

            @include('crud::inc.grouped_errors')
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
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="card" style="margin-top: 40px;">
                <header class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Status</h4>

                        <span class="font-weight-bold text-capitalize">
                            @if ($entry->is_published)
                                Published
                            @else
                                Draft
                            @endif
                        </span>
                    </div>

                    @include('crud::inc.form_save_buttons')
                </header>

                <div class="card-body">
                    <div class="form-group">
                        <input id="published_at_value" type="hidden" class="form-control" name="published_at" value="{{ $entry->published_at ?? '' }}">

                        <label for="published_at_datetime">Publish On</label>

                        <div class="input-group date">
                            <input
                                id="published_at_datetime"
                                type="text"
                                data-bs-datetimepicker="{}"
                                data-init-function="initPublishedAtDateTimePicker"
                                class="form-control"
                            >
                            <div class="input-group-append">
                                <span class="input-group-text"><span class="la la-calendar"></span></span>
                            </div>
                        </div>

                        <p class="help-block">
                            Select the date you want this page to be published, pressing <b>delete</b> will remove it.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('before_styles')
    <link rel="stylesheet" href="{{ asset('packages/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}">
    <link rel="stylesheet" href="{{ asset('packages/pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}">

    <style type="text/css">
        #saveActions.form-group {
            margin-bottom: 0;
        }
    </style>
@endsection

{{-- FIELD JS - will be loaded in the after_scripts section --}}

@push('after_scripts')
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

    <script type="text/javascript" src="{{ asset('packages/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('packages/pc-bootstrap4-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        var dateFormat=function(){var a=/d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,b=/\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,c=/[^-+\dA-Z]/g,d=function(a,b){for(a=String(a),b=b||2;a.length<b;)a="0"+a;return a};return function(e,f,g){var h=dateFormat;if(1!=arguments.length||"[object String]"!=Object.prototype.toString.call(e)||/\d/.test(e)||(f=e,e=void 0),e=e?new Date(e):new Date,isNaN(e))throw SyntaxError("invalid date");f=String(h.masks[f]||f||h.masks.default),"UTC:"==f.slice(0,4)&&(f=f.slice(4),g=!0);var i=g?"getUTC":"get",j=e[i+"Date"](),k=e[i+"Day"](),l=e[i+"Month"](),m=e[i+"FullYear"](),n=e[i+"Hours"](),o=e[i+"Minutes"](),p=e[i+"Seconds"](),q=e[i+"Milliseconds"](),r=g?0:e.getTimezoneOffset(),s={d:j,dd:d(j),ddd:h.i18n.dayNames[k],dddd:h.i18n.dayNames[k+7],m:l+1,mm:d(l+1),mmm:h.i18n.monthNames[l],mmmm:h.i18n.monthNames[l+12],yy:String(m).slice(2),yyyy:m,h:n%12||12,hh:d(n%12||12),H:n,HH:d(n),M:o,MM:d(o),s:p,ss:d(p),l:d(q,3),L:d(q>99?Math.round(q/10):q),t:n<12?"a":"p",tt:n<12?"am":"pm",T:n<12?"A":"P",TT:n<12?"AM":"PM",Z:g?"UTC":(String(e).match(b)||[""]).pop().replace(c,""),o:(r>0?"-":"+")+d(100*Math.floor(Math.abs(r)/60)+Math.abs(r)%60,4),S:["th","st","nd","rd"][j%10>3?0:(j%100-j%10!=10)*j%10]};return f.replace(a,function(a){return a in s?s[a]:a.slice(1,a.length-1)})}}();dateFormat.masks={default:"ddd mmm dd yyyy HH:MM:ss",shortDate:"m/d/yy",mediumDate:"mmm d, yyyy",longDate:"mmmm d, yyyy",fullDate:"dddd, mmmm d, yyyy",shortTime:"h:MM TT",mediumTime:"h:MM:ss TT",longTime:"h:MM:ss TT Z",isoDate:"yyyy-mm-dd",isoTime:"HH:MM:ss",isoDateTime:"yyyy-mm-dd'T'HH:MM:ss",isoUtcDateTime:"UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"},dateFormat.i18n={dayNames:["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],monthNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec","January","February","March","April","May","June","July","August","September","October","November","December"]},Date.prototype.format=function(a,b){return dateFormat(this,a,b)};

        // use Line Awesome icons (Backpack default) instead of Font Awesome (datetimepicker default)
        $.extend(true, $.fn.datetimepicker.defaults, {
            icons: {
                time: 'la la-clock text-bold',
                date: 'la la-calendar',
                up: 'la la-arrow-up',
                down: 'la la-arrow-down',
                previous: 'la la-chevron-left',
                next: 'la la-chevron-right',
                today: 'la la-calendar-check',
                clear: 'la la-trash-alt',
                close: 'la la-times-circle'
            }
        });

        function initPublishedAtDateTimePicker(element) {
            var $fake = element,
                $field = $('#published_at_value'),
                $customConfig = $.extend({
                    format: 'D MMM YYYY @ h:m a',
                    defaultDate: $field.val(),
                    @if(isset($field['allows_null']) && $field['allows_null'])
                        showClear: true,
                    @endif
                }, $fake.data('bs-datetimepicker'));

            $customConfig.locale = $customConfig['language'];
            delete($customConfig['language']);
            var $picker = $fake.datetimepicker($customConfig);

            $picker.on('dp.change', function(e){
                var sqlDate = e.date ? e.date.format('YYYY-MM-DD HH:mm:ss') : null;
                $field.val(sqlDate);
            });
        }
    </script>
@endpush

