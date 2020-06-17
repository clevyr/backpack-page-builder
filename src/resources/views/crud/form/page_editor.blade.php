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
                           {{ $section['name'] }}
                       </button>
                   </h2>
               </div>

               <div id="{{ $slug }}" class="collapse open" aria-labelledby="{{ $slug }}" data-parent="#accordionExample">
                   <div class="card-body">
                       @include('vendor.backpack.crud.fields.checkbox', ['field' => ['name' => '', 'label' => '', 'type' => '']])
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
