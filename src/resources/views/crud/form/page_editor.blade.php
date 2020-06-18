<div class="container container-fluid">
   @foreach($sections as $key => $section)
       @php $slug = 'section_' . $key . '_' . $section['name'] @endphp
       <div class="accordion" id="accordionExample">
           <div class="card">
               <div class="card-header" id="headingOne">
                   <h2 class="mb-0">
                       <button class="btn btn-link"
                               type="button"
                               data-toggle="collapse"
                               data-target="#{{ $slug }}"
                               aria-expanded="true"
                               aria-controls="collapseOne">
                           {{ $section['human_name'] }}
                       </button>
                   </h2>
               </div>

               <div id="{{ $slug }}" class="collapse open" aria-labelledby="{{ $slug }}" data-parent="#accordionExample">
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
</div>


@push('after_styles')
<style type="text/css">
    .page-editor {
        background: red;
    }
</style>
@endpush
