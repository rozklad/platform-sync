<?php namespace Sanatorium\Sync\Handlers\Dictionaryentries;

interface DictionaryentriesDataHandlerInterface {

	/**
	 * Prepares the given data for being stored.
	 *
	 * @param  array  $data
	 * @return mixed
	 */
	public function prepare(array $data);

}
