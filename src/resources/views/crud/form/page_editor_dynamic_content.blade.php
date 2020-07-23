<div id="page-editor-content" class="col-12 page-editor-content">
    @if (count($sections) > 0)
        @foreach($sections as $sKey => $section)
            @php
                $uuid = $section['pivot']['uuid'];
                $accordion_id = "accordion_{$uuid}"
            @endphp
            <div class="accordion" id="{{ $accordion_id }}_content">
                <div class="card">
                    <div class="card-header p-0" id="{{ $accordion_id }}_header">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left px-3 py-2 d-flex align-items-center shadow-none"
                                    type="button"
                                    data-toggle="collapse"
                                    data-target="#{{ $accordion_id }}_collapse"
                                    aria-expanded="true"
                                    aria-controls="{{ $accordion_id }}_collapse">
                                {{ $section['human_name'] }}

                                <i class="la la-arrow-down ml-auto"></i>
                            </button>
                        </h2>
                    </div>

                    <div id="{{ $accordion_id }}_collapse" class="collapse open"
                         aria-labelledby="{{ $accordion_id }}_collapse"
                         data-parent="#{{ $accordion_id }}_content">
                        <div class="card-body">
                            <div class="form-group">
                                @foreach($section['fields'] as $key => $data)
                                    {{-- Merge field data to create name key array--}}
                                    @php
                                        $value = '';
                                        // This is bad and should be changed - TODO
                                        if (!is_array($section['pivot']['data'])) {
                                            $section['pivot']['data']
                                            = json_decode($section['pivot']['data'], true);
                                        }

                                        if (is_array($section['pivot']['data'])) {
                                            $value = isset($section['pivot']['data'][$key])
                                            ?  $section['pivot']['data'][$key]
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
