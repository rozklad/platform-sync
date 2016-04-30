<?php namespace Sanatorium\Sync\Providers;

use Cartalyst\Support\ServiceProvider;

class DictionaryServiceProvider extends ServiceProvider {

	/**
	 * {@inheritDoc}
	 */
	public function boot()
	{
		// Register the attributes namespace
		$this->app['platform.attributes.manager']->registerNamespace(
			$this->app['Sanatorium\Sync\Models\Dictionary']
		);

		// Subscribe the registered event handler
		$this->app['events']->subscribe('sanatorium.sync.dictionary.handler.event');
	}

	/**
	 * {@inheritDoc}
	 */
	public function register()
	{
		// Register the repository
		$this->bindIf('sanatorium.sync.dictionary', 'Sanatorium\Sync\Repositories\Dictionary\DictionaryRepository');

		// Register the data handler
		$this->bindIf('sanatorium.sync.dictionary.handler.data', 'Sanatorium\Sync\Handlers\Dictionary\DictionaryDataHandler');

		// Register the event handler
		$this->bindIf('sanatorium.sync.dictionary.handler.event', 'Sanatorium\Sync\Handlers\Dictionary\DictionaryEventHandler');

		// Register the validator
		$this->bindIf('sanatorium.sync.dictionary.validator', 'Sanatorium\Sync\Validator\Dictionary\DictionaryValidator');
	}

}
