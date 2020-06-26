<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class PackageList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists current packages with version and date.';

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
        $file = json_decode(file_get_contents('composer.lock'), true);
        $packages = $file['packages'];
        usort($packages, function ($a, $b) {
            if ($a['time'] > $b['time']) {
                return $b;
            }
        });
        $this->line(str_pad('Name', 40).' | '.str_pad('Version', 10).' | Date');
        $oldPackages = 0;
        foreach ($packages as $package) {
            if (Carbon::now()->subMonths(6) > $package['time']) {
                $this->error(str_pad($package['name'], 40).' | '.str_pad($package['version'], 10).' | '.$package['time']);
                $oldPackages ++;
            } else {
                $this->line(str_pad($package['name'], 40).' | '.str_pad($package['version'], 10).' | '.$package['time']);
            }
        }
        if ($oldPackages > 0) {
            $this->info('There are '.$oldPackages.' packages that are older then six months.');
        }
    }
}
