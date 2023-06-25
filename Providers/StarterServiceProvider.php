<?php

namespace Modules\Starter\Providers;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;
use Modules\Starter\Console\InstallCommand;

class StarterServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Starter';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'starter';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadAllHelpers();
        $this->registerAdminAssetPublishing();

        $this->commands([
            InstallCommand::class
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
            module_path($this->moduleName, 'Config/messages.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/messages.php'), $this->moduleNameLower . '.messages'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }

    private function loadAllHelpers()
    {
        $modules = json_decode(file_get_contents(base_path('modules_statuses.json')), true);

        foreach ($modules as $module => $status) {
            if (!$status) {
                continue;
            }

            $helper_path = module_path($module, 'Helpers');
            // load all the php files in the Helpers folder
            foreach (glob($helper_path . '/*.php') as $filename) {
                require_once $filename;
            }
        }
    }

    private function registerAdminAssetPublishing()
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                module_path($this->moduleName, 'stubs/Console/Kernel.stub') => app_path('Console/Kernel.php'),

                module_path($this->moduleName, 'stubs/Http/Controllers/BaseManagerController.stub') => app_path('Http/Controllers/BaseManagerController.php'),
                module_path($this->moduleName, 'stubs/Http/Controllers/Manager/DepartmentController.stub') => app_path('Http/Controllers/Manager/DepartmentController.php'),
                module_path($this->moduleName, 'stubs/Http/Controllers/Manager/UserController.stub') => app_path('Http/Controllers/Manager/UserController.php'),
                module_path($this->moduleName, 'stubs/Http/Controllers/Manager/IndexController.stub') => app_path('Http/Controllers/Manager/IndexController.php'),
                module_path($this->moduleName, 'stubs/Http/Controllers/Manager/ToolController.stub') => app_path('Http/Controllers/Manager/ToolController.php'),
                module_path($this->moduleName, 'stubs/Http/Kernel.stub') => app_path('Http/Kernel.php'),

                module_path($this->moduleName, 'stubs/Models/Department.stub') => app_path('Models/Department.php'),
                module_path($this->moduleName, 'stubs/Models/User.stub') => app_path('Models/User.php'),
                module_path($this->moduleName, 'stubs/Models/SnsUser.stub') => app_path('Models/SnsUser.php'),

                module_path($this->moduleName, 'stubs/Provider/AppServiceProvider.stub') => app_path('Providers/AppServiceProvider.php'),
                module_path($this->moduleName, 'stubs/Provider/AuthServiceProvider.stub') => app_path('Providers/AuthServiceProvider.php'),

                module_path($this->moduleName, 'stubs/Services/UserService.stub') => app_path('Services/UserService.php'),

                module_path($this->moduleName, 'stubs/routes/api.stub') => base_path('routes/api.php'),
                module_path($this->moduleName, 'stubs/routes/web.stub') => base_path('routes/web.php'),

                module_path($this->moduleName, 'stubs/seeders/SetupSeeder.stub') => database_path('seeders/SetupSeeder.php'),
                module_path($this->moduleName, 'stubs/seeders/DatabaseSeeder.stub') => database_path('seeders/DatabaseSeeder.php'),

                module_path($this->moduleName, 'stubs/config/conf.stub') => config_path('conf.php'),
                module_path($this->moduleName, 'stubs/config/module.stub') => config_path('module.php'),
                module_path($this->moduleName, 'stubs/config/menus.stub') => config_path('default/menus.php'),

                module_path($this->moduleName, 'stubs/assets/images') => public_path('images'),
                module_path($this->moduleName, 'stubs/.htaccess.stub') => public_path('.htaccess'),

                module_path($this->moduleName, 'stubs/.editorconfig.stub') => base_path('.editorconfig'),
                module_path($this->moduleName, 'stubs/.prettierrc.js.stub') => base_path('.prettierrc.js'),
                module_path($this->moduleName, 'stubs/.eslintrc.js.stub') => base_path('.eslintrc.js'),
                module_path($this->moduleName, 'stubs/.jsconfig.json.stub') => base_path('jsconfig.json'),
                module_path($this->moduleName, 'stubs/package.json.stub') => base_path('package.json'),
                module_path($this->moduleName, 'stubs/tailwind.config.js.stub') => base_path('tailwind.config.js'),
                module_path($this->moduleName, 'stubs/postcss.config.js.stub') => base_path('postcss.config.js'),
                module_path($this->moduleName, 'stubs/vite.config.js.stub') => base_path('vite.config.js'),

                module_path($this->moduleName, 'stubs/assets/js') => resource_path('js'),
                module_path($this->moduleName, 'stubs/assets/manager.blade.stub') => resource_path('views/manager.blade.php'),
                module_path($this->moduleName, 'stubs/assets/web') => resource_path('views/web'),
            ], 'starter-assets');
        }
    }
}
