<?php

use League\CommonMark\Environment;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Normalizer\SlugNormalizer;
use League\CommonMark\Block\Renderer\FencedCodeRenderer;
use League\CommonMark\Block\Renderer\IndentedCodeRenderer;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;

function markdown($markdown)
{

    $environment = Environment::createCommonMarkEnvironment();
    $environment->addExtension(new GithubFlavoredMarkdownExtension());

    $environment->addExtension(new HeadingPermalinkExtension());
    $environment->addExtension(new TableOfContentsExtension());

    $environment->addBlockRenderer(FencedCode::class, new FencedCodeRenderer(['html', 'php', 'javascript', 'bash']));
    $environment->addBlockRenderer(IndentedCode::class, new IndentedCodeRenderer(['html', 'php', 'javascript', 'bash']));

    $config = [
        'table_of_contents' => [
            'html_class' => 'toc', //'table-of-contents',
            'position' => 'placeholder',
            'style' => 'bullet',
            'min_heading_level' => 2,
            'max_heading_level' => 6,
            'normalize' => 'relative',
            'placeholder' => '<ul id="toc"></ul>',
        ],
        'heading_permalink' => [
            'html_class' => 'heading-permalink',
            'id_prefix' => 'user-content',
            'insert' => 'before',
            'title' => 'Permalink',
            'symbol' => '', //HeadingPermalinkRenderer::DEFAULT_SYMBOL,
            'slug_normalizer' => new SlugNormalizer(),
        ],
    ];

    $converter = new CommonMarkConverter($config, $environment);
    return $converter->convertToHtml($markdown);
}
