<?php namespace Sanatorium\Sync\Providers;

use Cartalyst\Support\ServiceProvider;

class DictionaryentriesServiceProvider extends ServiceProvider {

	/**
	 * {@inheritDoc}
	 */
	public function boot()
	{
		// Register the attributes namespace
		$this->app['platform.attributes.manager']->registerNamespace(
			$this->app['Sanatorium\Sync\Models\Dictionaryentries']
		);

		// Subscribe the registered event handler
		$this->app['events']->subscribe('sanatorium.sync.dictionaryentries.handler.event');
	}

	/**
	 * {@inheritDoc}
	 */
	public function register()
	{
		// Register the repository
		$this->bindIf('sanatorium.sync.dictionaryentries', 'Sanatorium\Sync\Repositories\Dictionaryentries\DictionaryentriesRepository');

		// Register the data handler
		$this->bindIf('sanatorium.sync.dictionaryentries.handler.data', 'Sanatorium\Sync\Handlers\Dictionaryentries\DictionaryentriesDataHandler');

		// Register the event handler
		$this->bindIf('sanatorium.sync.dictionaryentries.handler.event', 'Sanatorium\Sync\Handlers\Dictionaryentries\DictionaryentriesEventHandler');

		// Register the validator
		$this->bindIf('sanatorium.sync.dictionaryentries.validator', 'Sanatorium\Sync\Validator\Dictionaryentries\DictionaryentriesValidator');
	}

}
