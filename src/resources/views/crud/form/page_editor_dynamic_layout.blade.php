<div id="page-editor-content" class="col-8 page-editor-content">
    @if (count($sections) > 0)
        @foreach($sections as $key => $section)
            @php $slug = 'section_' . $key . '_' . $section['name'] @endphp
            <div class="accordion" id="accordion_{{ $section['name'] }}">
                <div class="card">
                    <div class="card-header p-0" id="section_{{ $section['name'] }}_header">
                        <p class="text-left px-3 py-2 m-0">
                            {{ $section['human_name'] }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
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
<script type="text/javascript">
    $(document).ready(function () {
        var content = $('#page-editor-content');

        $('.add-section').click(function (e) {
            e.preventDefault();

            var sectionData = $(this).data('section');

            var slug = 'section_' + '_' + sectionData.name;

            $(content).append(createSection(sectionData, slug));
        })
    });

    // Add a new section
    function createSection(section, slug) {
        var value = {
            id: section.id,
            uuid: section.uuid ? section.uuid : null,
            fields: section.fields,
        };

        return '<div class="accordion">' +
            '<div class="card">' +
            ' <div class="card-header p-0">' +
            '   <p class="text-left px-3 py-2 m-0">' +
            '       ' + section.human_name +
            '   </p>' +
            '<textarea style="display: none;" name="sections[]">' + JSON.stringify(value) + '</textarea>' +
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
