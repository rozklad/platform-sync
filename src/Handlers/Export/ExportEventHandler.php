<?php namespace Sanatorium\Sync\Handlers\Export;

use Illuminate\Events\Dispatcher;
use Sanatorium\Sync\Models\Export;
use Cartalyst\Support\Handlers\EventHandler as BaseEventHandler;

class ExportEventHandler extends BaseEventHandler implements ExportEventHandlerInterface {

	/**
	 * {@inheritDoc}
	 */
	public function subscribe(Dispatcher $dispatcher)
	{
		$dispatcher->listen('sanatorium.sync.export.creating', __CLASS__.'@creating');
		$dispatcher->listen('sanatorium.sync.export.created', __CLASS__.'@created');

		$dispatcher->listen('sanatorium.sync.export.updating', __CLASS__.'@updating');
		$dispatcher->listen('sanatorium.sync.export.updated', __CLASS__.'@updated');

		$dispatcher->listen('sanatorium.sync.export.deleted', __CLASS__.'@deleted');
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
	public function created(Export $export)
	{
		$this->flushCache($export);
	}

	/**
	 * {@inheritDoc}
	 */
	public function updating(Export $export, array $data)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function updated(Export $export)
	{
		$this->flushCache($export);
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleted(Export $export)
	{
		$this->flushCache($export);
	}

	/**
	 * Flush the cache.
	 *
	 * @param  \Sanatorium\Sync\Models\Export  $export
	 * @return void
	 */
	protected function flushCache(Export $export)
	{
		$this->app['cache']->forget('sanatorium.sync.export.all');

		$this->app['cache']->forget('sanatorium.sync.export.'.$export->id);
	}

}
