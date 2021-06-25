<?php

use League\CommonMark\CommonMarkConverter;

function markdown($markdown)
{
    return app(CommonMarkConverter::class)->convertToHtml($markdown);
}