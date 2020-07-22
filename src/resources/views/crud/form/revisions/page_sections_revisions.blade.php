<div class="col-12">
    <div id="page-content-timeline">
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

                        <span class="font-weight-bold">
                            {{ $section->section()->first()->name }}

                            section
                        </span>

                        <div class="card-header-actions">
                            <div class="card-header-action">
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm restore-btn restore-content"
                                        data-entry-id="{{ $section->id }}"
                                        data-revision-id="{{ $history->id }}">

                                    <i class="la la-undo"></i>

                                    {{ trans('revise-operation::revise.undo') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">{{ mb_ucfirst(trans('revise-operation::revise.from')) }}:</div>
                            <div class="col-md-6">{{ mb_ucfirst(trans('revise-operation::revise.to')) }}:</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                @if (is_array((array) json_decode($history->oldValue())))
                                    <div class="alert alert-info" style="overflow-y: auto; max-height: 200px;">
                                        @foreach((array) json_decode($history->oldValue()) as $key => $old)
                                            <p class="lead font-weight-bold">{{ $key }}</p>
                                            <p>{!! $old !!}</p>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                @if (is_array((array) json_decode($history->newValue())))
                                    <div class="alert alert-success" style="overflow-y: auto; max-height: 200px;">
                                        @foreach((array) json_decode($history->newValue()) as $key => $new)
                                            <p class="lead font-weight-bold">{{ $key }}</p>
                                            <p>{!! $new !!}</p>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach
    </div>
</div>

@push('after_scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            var restoreContent = $('.restore-content');

            $.ajaxPrefilter(function(options, originalOptions, xhr) {
                var token = $('meta[name="csrf_token"]').attr('content');

                if (token) {
                    return xhr.setRequestHeader('X-XSRF-TOKEN', token);
                }
            });

            $.each(restoreContent, function () {
                $(this).on('click', function (e) {
                    e.preventDefault();
                    var entryId = $(e.target).attr('data-entry-id');
                    var revisionId = $(e.target).attr('data-revision-id');

                    $.ajax(`/{{ config('backpack.base.route_prefix') }}/section-data/${entryId}/revise/${revisionId}/restore`, {
                        method: 'POST',
                        data: {
                            revision_id: revisionId,
                            entry_id: entryId,
                        },
                        success: function(revisionTimeline) {
                            // Show a green notification bubble
                            new Noty({
                                type: "success",
                                text: "{{ trans('revise-operation::revise.revision_restored') }}, now reloading the page."
                            }).show();

                            location.reload();
                        },
                        error: function(data) {
                            // Show a red notification bubble
                            new Noty({
                                type: "error",
                                text: "Failed to undo that change."
                            }).show();
                        }
                    });
                });
            });
        });
    </script>
@endpush
