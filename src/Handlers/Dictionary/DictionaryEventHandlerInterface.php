<?php namespace Sanatorium\Sync\Handlers\Dictionary;

use Sanatorium\Sync\Models\Dictionary;
use Cartalyst\Support\Handlers\EventHandlerInterface as BaseEventHandlerInterface;

interface DictionaryEventHandlerInterface extends BaseEventHandlerInterface {

	/**
	 * When a dictionary is being created.
	 *
	 * @param  array  $data
	 * @return mixed
	 */
	public function creating(array $data);

	/**
	 * When a dictionary is created.
	 *
	 * @param  \Sanatorium\Sync\Models\Dictionary  $dictionary
	 * @return mixed
	 */
	public function created(Dictionary $dictionary);

	/**
	 * When a dictionary is being updated.
	 *
	 * @param  \Sanatorium\Sync\Models\Dictionary  $dictionary
	 * @param  array  $data
	 * @return mixed
	 */
	public function updating(Dictionary $dictionary, array $data);

	/**
	 * When a dictionary is updated.
	 *
	 * @param  \Sanatorium\Sync\Models\Dictionary  $dictionary
	 * @return mixed
	 */
	public function updated(Dictionary $dictionary);

	/**
	 * When a dictionary is deleted.
	 *
	 * @param  \Sanatorium\Sync\Models\Dictionary  $dictionary
	 * @return mixed
	 */
	public function deleted(Dictionary $dictionary);

}
