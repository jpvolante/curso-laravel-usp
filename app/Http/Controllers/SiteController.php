<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Symfony\Component\Yaml\Yaml;

class SiteController extends Controller
{
    public function index()
    {
        return SELF::gather(resource_path('files/index.md'));
    }

    public function folder($folder)
    {
        return SELF::gather(resource_path('files/' . $folder . '/index.md'));
    }

    public function folderFile($folder, $file)
    {

        return SELF::gather(resource_path('files/' . $folder . '/' . $file . '.md'));
    }

    protected function gather($file)
    {
        $site = Yaml::parse(file_get_contents(resource_path('files/config.yml')));

        if (!file_exists($file)) {
            return view('404', ['site' => $site]);
        }

        $document = YamlFrontMatter::parse(file_get_contents($file));

        return view('index', [
            'front' => $document->matter(),
            'content' => markdown($document->body()),
            'site' => $site,
        ]);
    }
}
