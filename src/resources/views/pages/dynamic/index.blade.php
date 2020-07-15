@extends('layouts.app')

@section('content')
    @foreach ($sections as $section)
        @include('pages.dynamic.sections.' . $section->name, ['data' => $section->formatted_data])
    @endforeach
@endsection
