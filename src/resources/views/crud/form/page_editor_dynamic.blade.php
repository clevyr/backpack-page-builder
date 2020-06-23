<div id="page-editor-content" class="col-8 page-editor-content">
    @if (count($sections) > 0)
        @foreach($sections as $key => $section)
            @php $slug = 'section_' . $key . '_' . $section['name'] @endphp
            <div class="accordion" id="accordion_{{ $section['name'] }}">
                <div class="card">
                    <div class="card-header p-0" id="section_{{ $section['name'] }}_header">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left px-3 py-2"
                                    type="button"
                                    data-toggle="collapse"
                                    data-target="#{{ $slug }}"
                                    aria-expanded="true"
                                    aria-controls="collapseOne">
                                {{ $section['human_name'] }}
                            </button>
                        </h2>
                    </div>

                    <div id="{{ $slug }}" class="collapse open" aria-labelledby="{{ $slug }}" data-parent="#accordion_{{ $section['name'] }}">
                        <div class="card-body">
                            <div class="form-group">
                                @foreach($section['fields'] as $key => $data)
                                    {{-- Merge field data to create name key array --}}
                                    @php
                                        $value = '';
                                        if (is_array($section_data[$section['id']])) {
                                            $value = isset($section_data[$section['id']][$key])
                                            ?  $section_data[$section['id']][$key]
                                            : '';
                                        }

                                        $field = array_merge($data, [
                                            'name' => "sections[{$section['id']}][{$key}]",
                                            'value' => $value,
                                        ]);
                                    @endphp
                                    @include('crud::fields.' . $data['type'], [
                                         'field' => $field,
                                         'crud' => $crud,
                                     ])
                                @endforeach
                            </div>
                        </div>
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
                    data-section="{{ $section }}">
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

            $.ajax({
                url: '/admin/pages/getField',
                method: 'POST',
                data: {
                    section: sectionData,
                },
                success: function (response) {
                    var fields = response.fields;
                    var uniq_id = response.uniq_id;
                    var slug = 'section_' + uniq_id + '_' + sectionData.name;

                    $(content).append(createSection(sectionData, slug));

                    var fieldsContent = $('#' + slug + '_fields .form-group');

                    $.each(fields, function (key) {
                        fieldsContent.append(fields[key]);
                    });
                },
                error: function (response) {
                    console.log(response);
                },
            });
        })
    });

    // Add a new section
    function createSection(section, slug) {
        return '<div class="accordion" id="accordion_'+ slug +'">' +
            '<div class="card">' +
            ' <div class="card-header p-0" id="'+ slug +'_header">' +
            '     <h2 class="mb-0">' +
            '         <button class="btn btn-link btn-block text-left px-3 py-2"' +
            '                 type="button"' +
            '                 data-toggle="collapse"' +
            '                 data-target="#'+ slug +'_fields"' +
            '                 aria-expanded="true"' +
            '                 aria-controls="collapseOne">' +
            '             ' + section.human_name +
            '         </button>' +
            '     </h2>' +
            ' </div>' +
            '</div>' +
            '<div id="'+ slug +'_fields" class="collapse open" aria-labelledby="'+ slug +'" data-parent="#accordion_'+ slug +'">\n' +
            '    <div class="card-body">' +
            '        <div class="form-group">' +
            '        </div>' +
            '    </div>' +
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
