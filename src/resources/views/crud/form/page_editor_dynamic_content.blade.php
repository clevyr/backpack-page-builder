<div id="page-editor-content" class="col-12 page-editor-content">
    @if (count($sections) > 0)
        @foreach($sections as $sKey => $section)
            @php $slug = 'section_' . $sKey . '_' . $section['name']; @endphp
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
                                    {{-- Merge field data to create name key array--}}
                                    @php
                                        $value = '';
                                        if (is_array($section_data[$section['pivot']['id']])) {
                                            $value = isset($section_data[$section['pivot']['id']][$key])
                                            ?  $section_data[$section['pivot']['id']][$key]
                                            : '';
                                        }

                                        $field = array_merge($data, [
                                            'name' => "sections[{$sKey}][data][{$key}]",
                                            'value' => $value,
                                        ]);
                                    @endphp
                                    <input type="hidden" name="sections[{{ $sKey }}][uuid]" value="{{ $section['pivot']['uuid'] }}" />
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
