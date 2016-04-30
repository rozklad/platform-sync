<?php namespace Sanatorium\Sync\Providers;

use Cartalyst\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class SyncServiceProvider extends ServiceProvider {

	/**
	 * {@inheritDoc}
	 */
	public function boot()
	{
		// Subscribe the registered event handler
		$this->app['events']->subscribe('sanatorium.sync.handler.event');

		// Register the manager
        $this->bindIf('sanatorium.sync.formatters', 'Sanatorium\Sync\Repositories\Formatters\FormattersRepository');

		$this->prepareResources();

		$this->registerLaravelExcel();
	}

	/**
	 * {@inheritDoc}
	 */
	public function register()
	{
		// Register the event handler
		$this->bindIf('sanatorium.sync.handler.event', 'Sanatorium\Sync\Handlers\SyncEventHandler');

		// Register the repository
		$this->bindIf('sanatorium.sync.export', 'Sanatorium\Sync\Repositories\Export\ExportRepository');
	}

	/**
	 * Prepare the package resources.
	 *
	 * @return void
	 */
	protected function prepareResources()
	{
		$config = realpath(__DIR__.'/../../config/config.php');

		$this->mergeConfigFrom($config, 'sanatorium-sync');

		$this->publishes([
			$config => config_path('sanatorium-sync.php'),
		], 'config');
	}

	protected function registerLaravelExcel()
	{
		$serviceProvider = 'Maatwebsite\Excel\ExcelServiceProvider';

		if ( class_exists($serviceProvider) )
		{
			if (!$this->app->getProvider($serviceProvider))
			{
				$this->app->register($serviceProvider);
			}
		}

		if ( class_exists('Maatwebsite\Excel\Facades\Excel') )
		{
			AliasLoader::getInstance()->alias('Excel', 'Maatwebsite\Excel\Facades\Excel');
		}

	}

	/**
	 * Function used for integrity checks
	 */
	public static function checkExcel()
	{
		$class = 'Maatwebsite\Excel\Facades\Excel';

		/**
		 * Dependency is not available
		 */
		if ( !class_exists($class) )
			return false;

		return true;
	}

}
