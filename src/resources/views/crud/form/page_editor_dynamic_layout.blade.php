<div id="page-editor-content" class="col-8 page-editor-content">
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

                        <input type="hidden" name="sections[{{ $key }}][uuid]" value="{{ $uuid }}" />
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

<script src="/packages/jquery-ui-dist/jquery-ui.min.js"></script>

<script type="text/javascript">
    jQuery.fn.tooltip = _tooltip;

    $(document).ready(function () {
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
        var sortable = content.sortable({
            handle: '.sort-handle',
            items: '.accordion',
            stop: function () {
                $.each(sortable.sortable('toArray'), function (key, value) {
                    var order = $('#' + value + ' .section_order');
                    order.val(key);
                });

                disableContentTab(true);
            },
        });

        // Add section listener
        $(document).on('click', '.add-section', function (e) {
            e.preventDefault();

            // Get sections data
            var sectionData = $(this).data('section');

            // Create slug
            var slug = 'section_' + '_' + sectionData.name;

            // Create section
            $(content).append(createSection(sectionData, slug));

            new_sections_count++;

            setTooltip(has_sections, tab_content_item, new_sections_count);

            content.sortable('refresh');
        })

        // Remove section listener
        $(document).on('click', '.remove-section', function (e) {
            e.preventDefault();

            var section = $(this).data('section-id');

            if (section) {
                $('#' + section + '_layout').remove();
                $('#' + section + '_content').remove();
            } else {
                var accordion = $(this).closest('.accordion');
                accordion.remove();
            }

            if (new_sections_count > 0 ) {
                new_sections_count--;
            }

            setTooltip(has_sections, tab_content_item, new_sections_count);

            $(this).tooltip('hide');

            content.sortable('refresh');
        });
    });

    /**
     * Set Tool Tip
     *
     * @param has_sections
     * @param tab_content_item
     * @param new_sections_count
     */
    function setTooltip(has_sections, tab_content_item, new_sections_count) {
        // If sections are already present disable the content tab and
        // show the tooltip
        if (has_sections && new_sections_count > 0) {
            tab_content_item.tooltip('enable');
            tab_content_item.tooltip('show');

            disableContentTab(false);
        } else {
            tab_content_item.tooltip('hide');
            tab_content_item.tooltip('disable');

            disableContentTab(true);
        }
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
            ' <textarea style="display: none;" name="sections[]">' + JSON.stringify(value) + '</textarea>' +
            ' </div>' +
            '</div>';
    }
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
