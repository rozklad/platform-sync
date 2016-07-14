<?php namespace Sanatorium\Sync\Handlers;

use Illuminate\Events\Dispatcher;
use Cartalyst\Support\Handlers\EventHandler as BaseEventHandler;
use Event;

class SyncEventHandler extends BaseEventHandler  {

	/**
	 * {@inheritDoc}
	 */
	public function subscribe(Dispatcher $dispatcher)
	{
		$dispatcher->listen('sanatorium.shop.product.lists.refresh', __CLASS__.'@refresh');

	}

	public function refresh()
	{
		$formatters = app('sanatorium.sync.formatters')->getServices();

		foreach ( $formatters as $formatter ) 
		{
            $formatter = app($formatter);
			$formatter->refresh();
		}
	}

}
