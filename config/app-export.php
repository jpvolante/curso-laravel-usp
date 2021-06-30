<?php

return [
    # no site de destino, qual será o "base" do html
    'baseurl' => env('BASEURL', base_path('gh-pages')),

    'source' =>  env('SOURCE', base_path('resources/files')),

    'destination' => env('DESTINATION', base_path('gh-pages')),

    'commonmark' => [

        # para commonmark, table of contents
        'table_of_contents' => [
            'html_class' => 'toc', //'table-of-contents',
            'position' => 'placeholder',
            'style' => 'bullet',
            'min_heading_level' => 2,
            'max_heading_level' => 6,
            'normalize' => 'relative',
            'placeholder' => '<ul id="toc"></ul>',
        ],

        # commonmark, permalinks
        'heading_permalink' => [
            'html_class' => 'heading-permalink',
            'id_prefix' => 'user-content',
            'insert' => 'before',
            'title' => 'Permalink',
            'symbol' => '', //HeadingPermalinkRenderer::DEFAULT_SYMBOL,
           // 'slug_normalizer' => new SlugNormalizer(),
        ],
    ],

    'highlight' => ['html', 'php', 'javascript', 'bash'],

];
