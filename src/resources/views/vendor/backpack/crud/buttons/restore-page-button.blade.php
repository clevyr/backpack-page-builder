<a href="javascript:void(0)"
   onclick="restoreEntry(this)"
   data-route="/{{ $crud->route . '/' . $entry->getKey() }}/restore"
   class="btn btn-sm btn-link"
   data-button-type="restore">
    <i class="la la-trash"></i>
    Restore Page
</a>

{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
    if (typeof restoreEntry != 'function') {
        $("[data-button-type=delete]").unbind('click');

        function restoreEntry(button) {
            // ask for confirmation before deleting an item
            // e.preventDefault();
            var button = $(button);
            var route = button.attr('data-route');
            var row = $("#crudTable a[data-route='"+route+"']").closest('tr');

            swal({
                title: "Restoring",
                text: "Are you sure you want to restore this page?",
                icon: "warning",
                buttons: {
                    cancel: {
                        text: "{!! trans('backpack::crud.cancel') !!}",
                        value: null,
                        visible: true,
                        className: "bg-secondary",
                        closeModal: true,
                    },
                    delete: {
                        text: "Restore",
                        value: true,
                        visible: true,
                        className: "bg-info",
                    }
                },
            }).then((value) => {
                if (value) {
                    $.ajax({
                        url: route,
                        type: 'post',
                        success: function(result) {
                            if (result == 1) {
                                // Show a success notification bubble
                                new Noty({
                                    type: "success",
                                    text: "{!! '<strong>Page has been restored</strong>' !!}"
                                }).show();

                                // Hide the modal, if any
                                $('.modal').modal('hide');

                                // Remove the details row, if it is open
                                if (row.hasClass("shown")) {
                                    row.next().remove();
                                }

                                // Remove the row from the datatable
                                row.remove();
                            } else {
                                // if the result is an array, it means
                                // we have notification bubbles to show
                                if (result instanceof Object) {
                                    // trigger one or more bubble notifications
                                    Object.entries(result).forEach(function(entry, index) {
                                        var type = entry[0];
                                        entry[1].forEach(function(message, i) {
                                            new Noty({
                                                type: type,
                                                text: message
                                            }).show();
                                        });
                                    });
                                } else {// Show an error alert
                                    swal({
                                        title: "Not Restored",
                                        text: "Failed to restore the page",
                                        icon: "error",
                                        timer: 4000,
                                        buttons: false,
                                    });
                                }
                            }
                        },
                        error: function(result) {
                            var text = "Failed to restore that page";

                            if (result.status === 403) {
                                text = "You are not authorized to delete that page.";
                            }

                            // Show an alert with the result
                            swal({
                                title: "Not Restored",
                                text: text,
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                            });
                        }
                    });
                }
            });

        }
    }

    // make it so that the function above is run after each DataTable draw event
    // crud.addFunctionToDataTablesDrawEventQueue('deleteEntry');
</script>
@if (!request()->ajax()) @endpush @endif
