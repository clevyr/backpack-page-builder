@hasrole('Super Admin')
    @includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_end')
    <button id="reload-button" type="button" disabled readonly class="btn btn-primary reload-files">
        <i class="la la-sync"></i>
    </button>

    @push('after_styles')
        <style>
            .reload-files {
                position: fixed;
                bottom: 0;
                right: 0;

                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-align: center;
                -ms-flex-align: center;
                align-items: center;
                -webkit-box-pack: center;
                -ms-flex-pack: center;
                justify-content: center;

                padding: 18px;
                margin: 24px;

                border-radius: 100%;
            }

            .reload-files .la {
                position: relative;

                font-size: 24px;
            }
        </style>
    @endpush

    @push('after_scripts')
        <script type="text/javascript">
            $(document).ready(function () {
                var loading = false;

                var sync_button = $('#reload-button');
                // var sync_icon = $('#reload-button .la');

                setLoadingState(false);

                sync_button.click(function (e) {
                    if (!loading) {
                        setLoadingState(true);
                        e.preventDefault();

                        $.ajax({
                            url: '/admin/pages/sync',
                            method: 'GET',
                            error(response) {
                                setLoadingState(false);

                                new Noty({
                                    type: "error",
                                    text: 'There was an issue syncing',
                                }).show();
                            },
                            success(response) {
                                setLoadingState(false);

                                var success = response.success;

                                if (success) {
                                    new Noty({
                                        type: "success",
                                        text: 'Layouts and sections updated!',
                                    }).show();
                                } else {
                                    new Noty({
                                        type: "error",
                                        text: response.message,
                                    }).show();
                                }
                            }
                        });
                    }
                });

                function setLoadingState(state) {
                    loading = state;

                    sync_button
                        .prop('disabled', state)
                        .prop('readonly', state);
                }
            });
        </script>
    @endpush
@endhasrole
