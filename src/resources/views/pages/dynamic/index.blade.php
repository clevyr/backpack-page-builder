<section>
    @foreach ($sections as $section)
        @include('pages.dynamic.sections.' . $section->section->name, ['data' => $section->formatted_data])
    @endforeach
</section>
