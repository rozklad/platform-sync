<?php namespace Sanatorium\Sync\Providers;

use Cartalyst\Support\ServiceProvider;

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

}
