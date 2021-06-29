<?php

use League\CommonMark\Environment;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Block\Renderer\FencedCodeRenderer;
use League\CommonMark\Block\Renderer\IndentedCodeRenderer;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;

if (!function_exists('markdown')) {
    function markdown($markdown)
    {
        $environment = Environment::createCommonMarkEnvironment();
        //$environment->addExtension(new GithubFlavoredMarkdownExtension());

        $environment->addExtension(new AutolinkExtension());
        //$environment->addExtension(new DisallowedRawHtmlExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new TaskListExtension());

        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new TableOfContentsExtension());

        # ainda sim precisei colocar o highlight.js. Tem de investigar mais
        $environment->addBlockRenderer(FencedCode::class, new FencedCodeRenderer(config('app-export.highlight')));
        $environment->addBlockRenderer(IndentedCode::class, new IndentedCodeRenderer(config('app-export.highlight')));

        $converter = new CommonMarkConverter(config('app-export.commonmark'), $environment);
        return $converter->convertToHtml($markdown);
    }
}
