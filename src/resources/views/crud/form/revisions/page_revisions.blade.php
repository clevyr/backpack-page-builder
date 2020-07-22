<div class="col-12">
    <div id="page-timeline">
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
                        <button type="button"
                                class="btn btn-outline-danger btn-sm restore-btn restore-page"
                                data-entry-id="{{ $entry->id }}"
                                data-revision-id="{{ $history->id }}">

                            <i class="la la-undo"></i>

                            {{ trans('revise-operation::revise.undo') }}
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">{{ mb_ucfirst(trans('revise-operation::revise.from')) }}:</div>
                        <div class="col-md-6">{{ mb_ucfirst(trans('revise-operation::revise.to')) }}:</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info" style="overflow: hidden;">
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

@push('after_scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            var restorePage = $('.restore-page');

            $.ajaxPrefilter(function(options, originalOptions, xhr) {
                var token = $('meta[name="csrf_token"]').attr('content');

                if (token) {
                    return xhr.setRequestHeader('X-XSRF-TOKEN', token);
                }
            });

            $.each(restorePage, function () {
               $(this).on('click', function (e) {
                   e.preventDefault();
                   var entryId = $(e.target).attr('data-entry-id');
                   var revisionId = $(e.target).attr('data-revision-id');

                   $.ajax(`/{{ config('backpack.base.route_prefix') }}/pages/${entryId}/revise/${revisionId}/restore`, {
                       method: 'POST',
                       data: {
                           revision_id: revisionId,
                           entry_id: entryId,
                       },
                       success: function(revisionTimeline) {
                           // Show a green notification bubble
                           new Noty({
                               type: "success",
                               text: "{{ trans('revise-operation::revise.revision_restored') }}"
                           }).show();

                           location.reload();
                       },
                       error: function(data) {
                           // Show a red notification bubble
                           new Noty({
                               type: "error",
                               text: data.responseJSON.message
                           }).show();
                       }
                   });
               });
            });
        });
    </script>
@endpush
