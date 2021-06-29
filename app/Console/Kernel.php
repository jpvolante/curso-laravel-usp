<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Yaml\Yaml;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');

        Artisan::command('replaceBase', function () {

            $appUrl = config('app.url');
            $baseUrl = config('app-export.baseurl') ?? Yaml::parse(file_get_contents(resource_path('files/config.yml')))['baseurl'];

            $root = base_path('gh-pages');
            $files = glob("$root/{,*/,*/*/,*/*/*/}*.html", GLOB_BRACE);
            $i = 0;
            foreach ($files as $file) {
                $str = file_get_contents($file);
                $str = str_replace($appUrl, $baseUrl, $str);
                \file_put_contents($file, $str);
                $i++;
            }
            echo "Replaced $appUrl with $baseUrl in $i files." . PHP_EOL;
        });

    }
}
