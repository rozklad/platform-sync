<?php namespace Sanatorium\Sync\Handlers\Dictionaryentries;

use Sanatorium\Sync\Models\Dictionaryentries;
use Cartalyst\Support\Handlers\EventHandlerInterface as BaseEventHandlerInterface;

interface DictionaryentriesEventHandlerInterface extends BaseEventHandlerInterface {

	/**
	 * When a dictionaryentries is being created.
	 *
	 * @param  array  $data
	 * @return mixed
	 */
	public function creating(array $data);

	/**
	 * When a dictionaryentries is created.
	 *
	 * @param  \Sanatorium\Sync\Models\Dictionaryentries  $dictionaryentries
	 * @return mixed
	 */
	public function created(Dictionaryentries $dictionaryentries);

	/**
	 * When a dictionaryentries is being updated.
	 *
	 * @param  \Sanatorium\Sync\Models\Dictionaryentries  $dictionaryentries
	 * @param  array  $data
	 * @return mixed
	 */
	public function updating(Dictionaryentries $dictionaryentries, array $data);

	/**
	 * When a dictionaryentries is updated.
	 *
	 * @param  \Sanatorium\Sync\Models\Dictionaryentries  $dictionaryentries
	 * @return mixed
	 */
	public function updated(Dictionaryentries $dictionaryentries);

	/**
	 * When a dictionaryentries is deleted.
	 *
	 * @param  \Sanatorium\Sync\Models\Dictionaryentries  $dictionaryentries
	 * @return mixed
	 */
	public function deleted(Dictionaryentries $dictionaryentries);

}
