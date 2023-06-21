<?php

namespace Modules\Starter\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starter:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Starter module';

    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Starter';


    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->info('Installing the Starter module...');
        $this->info('Set up config...');
        $this->installConfig();


        $this->info('Set up Inertia...');
        $this->installInertia();

        $this->info('Set up Telescope...');
        $this->installTelescope();

        $this->info('Publishing Assets...');
        $this->callSilent('vendor:publish', ['--tag' => 'starter-assets', '--force']);
    }


    protected function installConfig()
    {
        //$namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        $file_content = file_get_contents(config_path('app.php'));


        $file_content = str_replace("'timezone' => 'UTC'", "'timezone' => 'PRC'", $file_content);
        $file_content = str_replace("'locale' => 'en'", "'locale' => 'zh_CN'", $file_content);

        file_put_contents(config_path('app.php'), $file_content);

        if (!file_exists(base_path('lang'))) {
            mkdir(base_path('lang'));
        }
        $this->callSilent('lang:add', ['locales' => 'zh_CN']);
        $this->info('zh_CN language installed!');
    }


    protected function installInertia()
    {
        $this->callSilent('inertia:middleware');

        // add \App\Http\Middleware\HandleInertiaRequests::class, to $middlewareGroups['web'] in app/Http/Kernel.php
        $file_content = file_get_contents(app_path('Http/Kernel.php'));
        $file_content = str_replace(
            "'web' => [",
            "'web' => [
            \App\Http\Middleware\HandleInertiaRequests::class,",
            $file_content
        );

        file_put_contents(app_path('Http/Kernel.php'), $file_content);
    }

    protected function installTelescope()
    {
        $this->callSilent('telescope:install');

        $file_content = file_get_contents(app_path('Providers/TelescopeServiceProvider.php'));
        $file_content = str_replace('return in_array($user->email, [', 'return in_array($user->id, [ 1 ', $file_content);
        file_put_contents(app_path('Providers/TelescopeServiceProvider.php'), $file_content);

    }


    private function getEol($file_content): int|string
    {

        $line_ending_count = [
            "\r\n" => substr_count($file_content, "\r\n"),
            "\r" => substr_count($file_content, "\r"),
            "\n" => substr_count($file_content, "\n"),
        ];
        return array_keys($line_ending_count, max($line_ending_count))[0];
    }
}