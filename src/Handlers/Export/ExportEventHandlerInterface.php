<?php namespace Sanatorium\Sync\Handlers\Export;

use Sanatorium\Sync\Models\Export;
use Cartalyst\Support\Handlers\EventHandlerInterface as BaseEventHandlerInterface;

interface ExportEventHandlerInterface extends BaseEventHandlerInterface {

	/**
	 * When a export is being created.
	 *
	 * @param  array  $data
	 * @return mixed
	 */
	public function creating(array $data);

	/**
	 * When a export is created.
	 *
	 * @param  \Sanatorium\Sync\Models\Export  $export
	 * @return mixed
	 */
	public function created(Export $export);

	/**
	 * When a export is being updated.
	 *
	 * @param  \Sanatorium\Sync\Models\Export  $export
	 * @param  array  $data
	 * @return mixed
	 */
	public function updating(Export $export, array $data);

	/**
	 * When a export is updated.
	 *
	 * @param  \Sanatorium\Sync\Models\Export  $export
	 * @return mixed
	 */
	public function updated(Export $export);

	/**
	 * When a export is deleted.
	 *
	 * @param  \Sanatorium\Sync\Models\Export  $export
	 * @return mixed
	 */
	public function deleted(Export $export);

}
