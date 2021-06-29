<?php

use League\CommonMark\Block\Renderer\FencedCodeRenderer;
use League\CommonMark\Block\Renderer\IndentedCodeRenderer;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\Normalizer\SlugNormalizer;

if (!function_exists('markdown')) {
    function markdown($markdown)
    {
        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new TableOfContentsExtension());

        # ainda sim precisei colocar o highlight.js. Tem de investigar mais
        $environment->addBlockRenderer(FencedCode::class, new FencedCodeRenderer(config('app-export.highlight')));
        $environment->addBlockRenderer(IndentedCode::class, new IndentedCodeRenderer(config('app-export.highlight')));

        $converter = new CommonMarkConverter(config('app-export.commonmark'), $environment);
        return $converter->convertToHtml($markdown);
    }
}
