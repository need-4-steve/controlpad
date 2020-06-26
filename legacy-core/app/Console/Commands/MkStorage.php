<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MkStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:mkdir';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make the stupid laravel storage directories';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // I don't know why artisan doesn't have this already
        if (env('APP_STORAGE')) {
            $folders[] = 'logs';
            $folders[] = 'framework/cache';
            $folders[] = 'framework/sessions';
            $folders[] = 'framework/views';
            $folders[] = 'app/public';
            foreach ($folders as $folder) {
                $this->info("* Making: {$folder}");
                if (@mkdir(env('APP_STORAGE').'/'.$folder, 0777, true)) {
                    $this->info("OK {$folder} created!");
                } else {
                    $this->error("ERR ".print_r(error_get_last(), true));
                }
            }
        } else {
            $this->error('* No APP_STORAGE var in this environment');
        }
    }
}
