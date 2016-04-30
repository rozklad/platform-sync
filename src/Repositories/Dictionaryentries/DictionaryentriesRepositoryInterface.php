<?php namespace Sanatorium\Sync\Repositories\Dictionaryentries;

interface DictionaryentriesRepositoryInterface {

	/**
	 * Returns a dataset compatible with data grid.
	 *
	 * @return \Sanatorium\Sync\Models\Dictionaryentries
	 */
	public function grid();

	/**
	 * Returns all the sync entries.
	 *
	 * @return \Sanatorium\Sync\Models\Dictionaryentries
	 */
	public function findAll();

	/**
	 * Returns a sync entry by its primary key.
	 *
	 * @param  int  $id
	 * @return \Sanatorium\Sync\Models\Dictionaryentries
	 */
	public function find($id);

	/**
	 * Determines if the given sync is valid for creation.
	 *
	 * @param  array  $data
	 * @return \Illuminate\Support\MessageBag
	 */
	public function validForCreation(array $data);

	/**
	 * Determines if the given sync is valid for update.
	 *
	 * @param  int  $id
	 * @param  array  $data
	 * @return \Illuminate\Support\MessageBag
	 */
	public function validForUpdate($id, array $data);

	/**
	 * Creates or updates the given sync.
	 *
	 * @param  int  $id
	 * @param  array  $input
	 * @return bool|array
	 */
	public function store($id, array $input);

	/**
	 * Creates a sync entry with the given data.
	 *
	 * @param  array  $data
	 * @return \Sanatorium\Sync\Models\Dictionaryentries
	 */
	public function create(array $data);

	/**
	 * Updates the sync entry with the given data.
	 *
	 * @param  int  $id
	 * @param  array  $data
	 * @return \Sanatorium\Sync\Models\Dictionaryentries
	 */
	public function update($id, array $data);

	/**
	 * Deletes the sync entry.
	 *
	 * @param  int  $id
	 * @return bool
	 */
	public function delete($id);

}
