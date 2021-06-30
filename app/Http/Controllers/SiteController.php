<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Symfony\Component\Yaml\Yaml;

class SiteController extends Controller
{
    public function index()
    {
        return SELF::gather(config('app-export.source') . '/index.md');
    }

    public function folder($folder)
    {
        // vamos procurar permalinks nas pasta raiz
        $permalinks = SELF::permalinks();
        if (in_array("/$folder", array_keys($permalinks))) {
            return SELF::gather($permalinks["/$folder"]);
        }

        // ou index da pasta, se houver
        return SELF::gather(resource_path('files/' . $folder . '/index.md'));
    }

    public function folderFile($folder, $file)
    {
        return SELF::gather(resource_path('files/' . $folder . '/' . $file . '.md'));
    }

    protected function permalinks()
    {
        $permalinks = [];
        foreach (glob(config('app-export.source') . '/*.md') as $file) {
            $front = YamlFrontMatter::parse(file_get_contents($file))->matter();
            if (!empty($front['permalink'])) {
                $permalinks[$front['permalink']] = $file;
            }
        }
        return $permalinks;
    }

    protected function gather($file)
    {
        $site = Yaml::parse(file_get_contents(config('app-export.source') . '/_config.yml'));
        if (isset($front['permalink'])) {
            $site['base'] = str_replace('//', '/', config('app.url') . $front['permalink']);
        } else {
            $site['base'] = config('app.url');
        }

        if (!file_exists($file)) {
            return view('404', ['site' => $site]);
        }

        $document = YamlFrontMatter::parse(file_get_contents($file));
        $front = $document->matter();

        // escolhendo layout
        $layout = isset($front['layout']) ? $front['layout'] : 'default';

        return view("layouts.$layout", [
            'site' => $site,
            'front' => $front,
            'content' => markdown($document->body()),
        ]);
    }
}
