<div id="page-editor-layout" class="col-8 page-editor-content">
    @if ($has_sections)
        <p class="font-italic">Click and drag the arrows to order the sections</p>

        @foreach($sections as $key => $section)
            @php
                $slug = 'section_' . $key . '_' . $section['name'];
                $uuid = $section['pivot']['uuid'];
                $order = $section['pivot']['order'];
                $accordion_id = "accordion_{$uuid}"
            @endphp
            <div class="accordion"
                 id="{{ $accordion_id }}_layout">
                <div class="card">
                    <div class="card-header p-0 d-flex align-items-center" id="{{ $accordion_id }}_layout_header">
                        <i class="la la-arrows pl-2 pr-2 sort-handle"></i>

                        <p class="text-left px-3 py-2 m-0">
                            {{ $section['human_name'] }}
                        </p>

                        <button type="button" class="btn btn-link ml-auto remove-section"
                                data-tooltip="tooltip"
                                data-placement="top"
                                title="Remove section"
                                data-section-id="{{ $accordion_id }}">
                            <span class="la la-times"></span>
                        </button>

                        <input type="hidden" name="sections[{{ $key }}][uuid]" value="{{ $uuid }}" class="section_uuid" />
                        <input type="hidden" name="sections[{{ $key }}][order]" value="{{ $order }}" class="section_order" />
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <input type="hidden" name="sections" value="" />
    @endif
</div>

<div class="col-4 page-editor-sidebar">
    <h5>Sections</h5>

    <div class="list-group">
        @foreach ($all_sections as $key => $section)
            <button type="button" class="list-group-item list-group-item-action add-section"
                    data-section='@json($section)'>
                {{ $section['human_name'] }}
            </button>
        @endforeach
    </div>
</div>

@push('after_scripts')
{{-- Weird hack to make sure the tooltip function doesnt get overridden --}}
{{-- https://stackoverflow.com/questions/19970082/cannot-call-methods-on-tooltip-prior-to-initialization --}}
<script type="text/javascript">
    var _tooltip = jQuery.fn.tooltip;
</script>

@basset('https://cdn.jsdelivr.net/npm/jquery-ui@1.13.2/dist/jquery-ui.min.js')

<script type="text/javascript">
    jQuery.fn.tooltip = _tooltip;

    $(document).ready(function () {
        var layout = $('#page-editor-layout');
        var content = $('#page-editor-content');
        var has_sections = @json($has_sections);

        var tab_content_item = $('#has-sections-tab');
        var new_sections_count = 0;

        // Instantiate tooltip
        if (has_sections) {
            tab_content_item.data('title', 'You must create or update your page layout before adding content.');
            tab_content_item.tooltip('disable');
        }

        // Instantiate Sortable
        var sortable = layout.sortable({
            handle: '.sort-handle',
            items: '.accordion',
            stop: function () {
                resetNameKeys();

                $.each(sortable.sortable('toArray'), function (key, value) {
                    var order = $('#' + value + ' .section_order');

                    if (order) {
                        order.val(key);
                    }
                });

                setTooltip();
            },
        });

        // Add section listener
        $(document).on('click', '.add-section', function (e) {
            e.preventDefault();

            // Get sections data
            var sectionData = $(this).data('section');

            // Create section
            $(layout).append(createSection(sectionData));

            resetNameKeys();

            new_sections_count++;

            setTooltip();

            layout.sortable('refresh');
        })

        // Remove section listener
        $(document).on('click', '.remove-section', function (e) {
            e.preventDefault();

            var section = $(this).data('section-id');

            if (section) {
                if ($('#' + section + '_layout')) {
                    $('#' + section + '_layout').remove();
                }

                if ($('#' + section + '_content')) {
                    $('#' + section + '_content').remove();
                }
            } else {
                var accordion = $(this).closest('.accordion');
                accordion.remove();
            }

            if (new_sections_count > 0 ) {
                new_sections_count--;
            }

            resetNameKeys();

            setTooltip();

            $(this).tooltip('hide');

            layout.sortable('refresh');
        });

        /**
         * Set Tool Tip
         */
        function setTooltip() {
            // If sections are already present disable the content tab and
            // show the tooltip
            if (has_sections && new_sections_count > 0) {
                tab_content_item.tooltip('enable');
                tab_content_item.tooltip('show');

                disableContentTab(true);
            } else {
                tab_content_item.tooltip('hide');
                tab_content_item.tooltip('disable');

                disableContentTab(false);
            }
        }

        function resetNameKeys()
        {
            $.each(layout.children('.accordion'), function (key) {
               var item = $(this);

               const current_uuid = $(this).attr('id').split('_')[1];
               const content_field = content.find('#accordion_' + current_uuid + '_content');

               var uuid = item.find('.section_uuid');
               var order = item.find('.section_order');
               var new_section = item.find('.section_new');

               if (uuid.length > 0) {
                   uuid.attr('name', 'sections['+key+'][uuid]');
               }

               if (order.length > 0) {
                   order.attr('name', 'sections['+key+'][order]');
               }

               if (new_section.length > 0) {
                   new_section.attr('name', 'sections['+key+']');
               }

               if (content_field) {
                   var c_uuid = content_field.find('.section_uuid');
                   var c_data = content_field.find('.form-control');

                   if (c_uuid.length > 0) {
                       c_uuid.attr('name', 'sections['+key+'][uuid]');
                   }

                   if (c_data.length > 0) {
                       $.each(c_data, function () {
                           var current_name = $(this).attr('name').split('[');
                           current_name = current_name[current_name.length - 1];
                           current_name = current_name.slice(0, current_name.length - 1);

                           $(this).attr('name', 'sections['+ key +'][data]['+ current_name +']');
                       })
                   }
               }
            });
        }

        /**
         * Disable Content Tab
         *
         * @param state
         */
        function disableContentTab(state)
        {
            var tab_content_link = $('#has-sections-tab .nav-link');
            var tab_content_icon = $('#has-sections-tab .has-sections-icon');

            if (!state) {
                tab_content_link.data('disabled', false).removeClass('disabled');
                tab_content_icon.removeClass('d-inline').addClass('d-none');
            } else {
                tab_content_link.data('disabled', true).addClass('disabled');
                tab_content_icon.removeClass('d-none').addClass('d-inline');
            }
        }

        /**
         * Create Section
         *
         * @param section
         * @returns {string}
         */
        function createSection(section) {
            var value = {
                id: section.id,
                uuid: section.uuid ? section.uuid : null,
                fields: section.fields,
            };

            var rand = Math.floor(Math.random() * 26) + Date.now();

            return '<div id=' + "accordion_" + rand + "_layout" + ' class="accordion">' +
                '<div class="card">' +
                ' <div class="card-header p-0 d-flex align-items-center">' +
                '   <i class="la la-arrows pl-2 pr-2 sort-handle"></i>' +
                '   <p class="text-left px-3 py-2 m-0">' +
                '       ' + section.human_name +
                '   </p>' +
                ' <button type="button" class="btn btn-link ml-auto remove-section"' +
                '         data-tooltip="tooltip"' +
                '         data-placement="top"' +
                '         title="Remove section">' +
                '     <span class="la la-times"></span>' +
                ' </button>' +
                ' <textarea style="display: none;" name="sections[]" class="section_new">' + JSON.stringify(value) + '</textarea>' +
                ' </div>' +
                '</div>';
        }
    });
</script>
@endpush

@push('after_styles')
<style type="text/css">
    .page-editor-sidebar {
        max-height: 700px;
        overflow-y: auto;
        overflow-x: hidden;

        border-left: 1px solid #EEEEEE;
    }
</style>
@endpush
