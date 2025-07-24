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
			module_path($this->moduleName, 'Config/message.php') => config_path($this->moduleNameLower . '.php'),
		], 'config');

		$this->mergeConfigFrom(
			module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
		);
		$this->mergeConfigFrom(
			module_path($this->moduleName, 'Config/messages.php'), $this->moduleNameLower . '.messages'
		);
		$this->mergeConfigFrom(
			module_path($this->moduleName, 'Config/message.php'), $this->moduleNameLower . '.message'
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
}
