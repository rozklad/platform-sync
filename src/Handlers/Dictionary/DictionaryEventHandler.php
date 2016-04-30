<?php namespace Sanatorium\Sync\Handlers\Dictionary;

use Illuminate\Events\Dispatcher;
use Sanatorium\Sync\Models\Dictionary;
use Cartalyst\Support\Handlers\EventHandler as BaseEventHandler;

class DictionaryEventHandler extends BaseEventHandler implements DictionaryEventHandlerInterface {

	/**
	 * {@inheritDoc}
	 */
	public function subscribe(Dispatcher $dispatcher)
	{
		$dispatcher->listen('sanatorium.sync.dictionary.creating', __CLASS__.'@creating');
		$dispatcher->listen('sanatorium.sync.dictionary.created', __CLASS__.'@created');

		$dispatcher->listen('sanatorium.sync.dictionary.updating', __CLASS__.'@updating');
		$dispatcher->listen('sanatorium.sync.dictionary.updated', __CLASS__.'@updated');

		$dispatcher->listen('sanatorium.sync.dictionary.deleted', __CLASS__.'@deleted');
	}

	/**
	 * {@inheritDoc}
	 */
	public function creating(array $data)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function created(Dictionary $dictionary)
	{
		$this->flushCache($dictionary);
	}

	/**
	 * {@inheritDoc}
	 */
	public function updating(Dictionary $dictionary, array $data)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function updated(Dictionary $dictionary)
	{
		$this->flushCache($dictionary);
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleted(Dictionary $dictionary)
	{
		$this->flushCache($dictionary);
	}

	/**
	 * Flush the cache.
	 *
	 * @param  \Sanatorium\Sync\Models\Dictionary  $dictionary
	 * @return void
	 */
	protected function flushCache(Dictionary $dictionary)
	{
		$this->app['cache']->forget('sanatorium.sync.dictionary.all');

		$this->app['cache']->forget('sanatorium.sync.dictionary.'.$dictionary->id);
	}

}
