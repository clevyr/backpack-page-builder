<div class="col-12">
    <div class="accordion" id="revisions-page">
        <div class="card">
            <header class="card-header" id="revisions-page-header">
                <h3 class="mb-0">
                    <button class="btn btn-link"
                            type="button"
                            data-toggle="collapse"
                            data-target="#revision-page-collapse"
                            aria-expanded="true"
                            aria-controls="revision-page-collapse">
                        Page Revisions
                    </button>
                </h3>
            </header>

            <div id="revision-page-collapse"
                 class="collapse"
                 aria-labelledby="revisions-page-header"
                 data-parent="#revisions-page">
                <div class="card-body">
                    <div id="timeline">
                        @foreach($revisions['page'] as $key => $history)
                            <div class="card timeline-item-wrap">
                                <div class="card-header">
                                    <strong class="time">
                                        <i class="la la-clock"></i>
                                        {{ date('h:ia', strtotime($history->created_at)) }}
                                    </strong> -

                                    {{ $history->userResponsible()
                                        ? $history->userResponsible()->name
                                        : trans('revise-operation::revise.guest_user') }}

                                    {{ trans('revise-operation::revise.changed_the') }}

                                    {{ $history->fieldName() }}

                                    <div class="card-header-actions">
                                        <form class="card-header-action"
                                              method="post"
                                              action="{{ url(\Request::url().'/'.$history->id.'/restore') }}">

                                            {!! csrf_field() !!}

                                            <button type="submit"
                                                    class="btn btn-outline-danger btn-sm restore-btn"
                                                    data-entry-id="{{ $entry->id }}"
                                                    data-revision-id="{{ $history->id }}"
                                                    onclick="onRestoreClick(event)">

                                                <i class="la la-undo"></i>

                                                {{ trans('revise-operation::revise.undo') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">{{ mb_ucfirst(trans('revise-operation::revise.from')) }}:</div>
                                        <div class="col-md-6">{{ mb_ucfirst(trans('revise-operation::revise.to')) }}:</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="alert alert-danger" style="overflow: hidden;">
                                                {{ $history->oldValue() }}
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="alert alert-success" style="overflow: hidden;">
                                                {{ $history->newValue() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion" id="revisions-sections">
        <div class="card">
            <header class="card-header" id="revisions-sections-header">
                <h3 class="mb-0">
                    <button class="btn btn-link"
                            type="button"
                            data-toggle="collapse"
                            data-target="#revision-sections-collapse"
                            aria-expanded="true"
                            aria-controls="revision-sections-collapse">
                        Section Revisions
                    </button>
                </h3>
            </header>

            <div id="revision-sections-collapse"
                 class="collapse"
                 aria-labelledby="revisions-sections-header"
                 data-parent="#revisions-sections">
                <div class="card-body">
                    <div id="timeline">
                        @foreach($revisions['sections'] as $key => $section)
                            @foreach($section->revisionHistory as $history)
                                <div class="card timeline-item-wrap">
                                    <div class="card-header">
                                        <strong class="time">
                                            <i class="la la-clock"></i>
                                            {{ date('h:ia', strtotime($history->created_at)) }}
                                        </strong> -

                                        {{ $history->userResponsible()
                                            ? $history->userResponsible()->name
                                            : trans('revise-operation::revise.guest_user') }}

                                        {{ trans('revise-operation::revise.changed_the') }}

                                        {{ $history->fieldName() }}

                                        <div class="card-header-actions">
                                            <form class="card-header-action"
                                                  method="post"
                                                  action="{{ url(\Request::url().'/'.$history->id.'/restore') }}">

                                                {!! csrf_field() !!}

                                                <button type="submit"
                                                        class="btn btn-outline-danger btn-sm restore-btn"
                                                        data-entry-id="{{ $entry->id }}"
                                                        data-revision-id="{{ $history->id }}"
                                                        onclick="onRestoreClick(event)">

                                                    <i class="la la-undo"></i>

                                                    {{ trans('revise-operation::revise.undo') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">{{ mb_ucfirst(trans('revise-operation::revise.from')) }}:</div>
                                            <div class="col-md-6">{{ mb_ucfirst(trans('revise-operation::revise.to')) }}:</div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="alert alert-danger" style="overflow: hidden;">
                                                    {{ $history->oldValue() }}
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="alert alert-success" style="overflow: hidden;">
                                                    {{ $history->newValue() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('after_scripts')
    <script type="text/javascript">
        $.ajaxPrefilter(function(options, originalOptions, xhr) {
            var token = $('meta[name="csrf_token"]').attr('content');

            if (token) {
                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
            }
        });

        function onRestoreClick(e) {
            e.preventDefault();
            var entryId = $(e.target).attr('data-entry-id');
            var revisionId = $(e.target).attr('data-revision-id');
            $.ajax('{{ \Request::url().'/' }}' +  revisionId + '/restore', {
                method: 'POST',
                data: {
                    revision_id: revisionId
                },
                success: function(revisionTimeline) {
                    // Replace the revision list with the updated revision list
                    $('#timeline').replaceWith(revisionTimeline);

                    // Animate the new revision in (by sliding)
                    $('.timeline-item-wrap').first().addClass('fadein');

                    // Show a green notification bubble
                    new Noty({
                        type: "success",
                        text: "{{ trans('revise-operation::revise.revision_restored') }}"
                    }).show();
                },
                error: function(data) {
                    // Show a red notification bubble
                    new Noty({
                        type: "error",
                        text: data.responseJSON.message
                    }).show();
                }
            });
        }
    </script>
@endsection

@section('after_styles')
    {{-- Animations for new revisions after ajax calls --}}
    <style>
        .timeline-item-wrap.fadein {
            -webkit-animation: restore-fade-in 3s;
            animation: restore-fade-in 3s;
        }
        @-webkit-keyframes restore-fade-in {
            from {opacity: 0}
            to {opacity: 1}
        }
        @keyframes restore-fade-in {
            from {opacity: 0}
            to {opacity: 1}
        }
    </style>
@endsection
