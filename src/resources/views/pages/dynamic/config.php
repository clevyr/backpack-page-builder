<?php

return [
    'one-col' => [
        'title' => [
            'type' => 'text',
            'name' => 'title',
            'label' => 'Title'
        ],
        'col-1' => [
            'type' => 'tinymce',
            'label' => 'Column One',
            'name' => 'col-1',
        ],
    ],
    'two-col' => [
        'title' => [
            'type' => 'text',
            'name' => 'title',
            'label' => 'Title'
        ],
        'col-1' => [
            'type' => 'tinymce',
            'label' => 'Column One',
            'name' => 'col-1',
        ],
        'col-2' => [
            'type' => 'tinymce',
            'label' => 'Column Two',
            'name' => 'col-2',
            'options' => [
                'block_formats' => 'Paragraph=p; Header 1=h1; Header 2=h2; Header 3=h3',
            ],
        ],
    ],
    'three-col' => [
        'title' => [
            'type' => 'text',
            'name' => 'title',
            'label' => 'Title'
        ],
        'col-1' => [
            'type' => 'tinymce',
            'label' => 'Column One',
            'name' => 'col-1',
        ],
        'col-2' => [
            'type' => 'tinymce',
            'label' => 'Column Two',
            'name' => 'col-2',
        ],
        'col-3' => [
            'type' => 'tinymce',
            'label' => 'Column Three',
            'name' => 'col-3',
        ],
    ],
];
