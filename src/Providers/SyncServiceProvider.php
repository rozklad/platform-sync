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

		// Subscribe the registered event handler
		$this->app['events']->subscribe('sanatorium.sync.dictionary.handler.event');

		// Subscribe the registered event handler
		$this->app['events']->subscribe('sanatorium.sync.dictionaryentries.handler.event');

		$this->prepareResources();

		$this->registerLaravelExcel();

		$this->registerPlugins();
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

		// Register the repository
		$this->bindIf('sanatorium.sync.dictionary', 'Sanatorium\Sync\Repositories\Dictionary\DictionaryRepository');

		// Register the data handler
		$this->bindIf('sanatorium.sync.dictionary.handler.data', 'Sanatorium\Sync\Handlers\Dictionary\DictionaryDataHandler');

		// Register the event handler
		$this->bindIf('sanatorium.sync.dictionary.handler.event', 'Sanatorium\Sync\Handlers\Dictionary\DictionaryEventHandler');

		// Register the validator
		$this->bindIf('sanatorium.sync.dictionary.validator', 'Sanatorium\Sync\Validator\Dictionary\DictionaryValidator');

		// Register the repository
		$this->bindIf('sanatorium.sync.dictionaryentries', 'Sanatorium\Sync\Repositories\Dictionaryentries\DictionaryentriesRepository');

		// Register the data handler
		$this->bindIf('sanatorium.sync.dictionaryentries.handler.data', 'Sanatorium\Sync\Handlers\Dictionaryentries\DictionaryentriesDataHandler');

		// Register the event handler
		$this->bindIf('sanatorium.sync.dictionaryentries.handler.event', 'Sanatorium\Sync\Handlers\Dictionaryentries\DictionaryentriesEventHandler');

		// Register the validator
		$this->bindIf('sanatorium.sync.dictionaryentries.validator', 'Sanatorium\Sync\Validator\Dictionaryentries\DictionaryentriesValidator');
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

	public function registerPlugins()
	{
		try
		{
			// Register the attributes namespace
			$this->app['sanatorium.sync.formatters']->registerService(
				'heureka', 'Sanatorium\Sync\Plugins\Heureka\Formatters\HeurekaFormatter'
			);

			// Register the heureka stock usage
			$this->app['sanatorium.stock.usages']->registerService(
				'heureka', 'Sanatorium\Sync\Plugins\Heureka\Usages\HeurekaUsage'
			);

			// Register the attributes namespace
			$this->app['sanatorium.sync.formatters']->registerService(
				'zbozi', 'Sanatorium\Sync\Plugins\Zbozi\Formatters\ZboziFormatter'
			);

			// Register the zbozi stock usage
			$this->app['sanatorium.stock.usages']->registerService(
				'zbozi', 'Sanatorium\Sync\Plugins\Zbozi\Usages\ZboziUsage'
			);

			// Register the attributes namespace
			$this->app['sanatorium.sync.formatters']->registerService(
				'pricelist', 'Sanatorium\Sync\Plugins\Pricelist\Formatters\PricelistFormatter'
			);
		} catch (\ReflectionException $e)
		{
			// If any of the classes above cannot be registered to their
			// respective manager, system will not fail, but this
			// extension will most probably not work correctly
			Log::error('sync: ' . $e->getMessage() . ', extension will not work properly');
		}
	}

}
