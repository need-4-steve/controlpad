<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GitResetPull extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:reset-pull {branch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset database, composer install, run legacy-build, pull new branch';

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
        $arguments = $this->arguments();
        echo shell_exec('mysqladmin -u homestead -psecret drop homestead --force');
        echo shell_exec('mysqladmin -u homestead -psecret create homestead --force');
        echo shell_exec('git fetch');
        echo shell_exec('git checkout ' . $arguments['branch']);
        echo shell_exec('git pull');
        echo shell_exec('composer install');
        echo shell_exec('composer clearcache');
        echo shell_exec('php artisan cache:clear');
        echo shell_exec('php artisan view:clear');
        echo shell_exec('php artisan migrate --seed');
        echo shell_exec('npm i');
        echo shell_exec('npm run legacy-build');
    }
}
