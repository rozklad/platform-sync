<?php namespace Sanatorium\Sync\Handlers\Dictionaryentries;

use Illuminate\Events\Dispatcher;
use Sanatorium\Sync\Models\Dictionaryentries;
use Cartalyst\Support\Handlers\EventHandler as BaseEventHandler;

class DictionaryentriesEventHandler extends BaseEventHandler implements DictionaryentriesEventHandlerInterface {

	/**
	 * {@inheritDoc}
	 */
	public function subscribe(Dispatcher $dispatcher)
	{
		$dispatcher->listen('sanatorium.sync.dictionaryentries.creating', __CLASS__.'@creating');
		$dispatcher->listen('sanatorium.sync.dictionaryentries.created', __CLASS__.'@created');

		$dispatcher->listen('sanatorium.sync.dictionaryentries.updating', __CLASS__.'@updating');
		$dispatcher->listen('sanatorium.sync.dictionaryentries.updated', __CLASS__.'@updated');

		$dispatcher->listen('sanatorium.sync.dictionaryentries.deleted', __CLASS__.'@deleted');
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
	public function created(Dictionaryentries $dictionaryentries)
	{
		$this->flushCache($dictionaryentries);
	}

	/**
	 * {@inheritDoc}
	 */
	public function updating(Dictionaryentries $dictionaryentries, array $data)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function updated(Dictionaryentries $dictionaryentries)
	{
		$this->flushCache($dictionaryentries);
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleted(Dictionaryentries $dictionaryentries)
	{
		$this->flushCache($dictionaryentries);
	}

	/**
	 * Flush the cache.
	 *
	 * @param  \Sanatorium\Sync\Models\Dictionaryentries  $dictionaryentries
	 * @return void
	 */
	protected function flushCache(Dictionaryentries $dictionaryentries)
	{
		$this->app['cache']->forget('sanatorium.sync.dictionaryentries.all');

		$this->app['cache']->forget('sanatorium.sync.dictionaryentries.'.$dictionaryentries->id);
	}

}
