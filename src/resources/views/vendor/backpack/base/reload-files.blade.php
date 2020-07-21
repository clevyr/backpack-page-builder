@hasrole('Super Admin')
    @includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_end')
    <button id="reload-button" type="button" disabled readonly class="btn btn-primary reload-files">
        <i class="la la-sync"></i>
    </button>

    <div id="reload-overlay" style="display: none;">
        <i class="la la-sync la-spin la-3x"></i>

        <h3 id="reload-overlay-text">Syncing View Files</h3>
    </div>

    @push('after_styles')
        <style>
            #reload-overlay {
                position: absolute;
                top: 0;
                left: 0;

                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;

                width: 100%;
                height: 100%;

                background-color: rgba(0, 0, 0, 0.3);
            }

            #reload-overlay .la {
                margin-bottom: 0.5em;
                color: #FFFFFF;
            }

            #reload-overlay-text {
                color: #FFFFFF;
            }

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
                var reload_overlay = $('#reload-overlay');
                var reload_overlay_text = $('#reload-overlay-text');

                setLoadingState(false);

                sync_button.click(function (e) {
                    if (!loading) {
                        setLoadingState(true);
                        e.preventDefault();

                        $.ajax({
                            url: '/admin/pages/sync',
                            method: 'GET',
                            error(response) {
                                reload_overlay.fadeOut();

                                new Noty({
                                    type: "error",
                                    text: 'There was an issue syncing',
                                }).show();

                                setLoadingState(false);
                            },
                            success(response) {
                                setLoadingState(false);

                                var success = response.success;

                                if (success) {
                                    new Noty({
                                        type: "success",
                                        text: 'Layouts and sections updated!',
                                    }).show();

                                    location.reload();
                                } else {
                                    reload_overlay.fadeOut();

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

                    if (state) {
                        reload_overlay_text.text('Syncing Views');
                        reload_overlay.fadeIn();
                    } else {
                        reload_overlay_text.text('Reloading Page');
                    }
                }
            });
        </script>
    @endpush
@endhasrole
