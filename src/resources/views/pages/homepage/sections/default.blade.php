<h1>
    {{ $sections->get('default')->formatted_data['title'] }}

    <br />

    <small>
        {{ $sections->get('default')->formatted_data['sub-title'] }}
    </small>
</h1>

{!! $sections->get('default')->formatted_data['content'] !!}
