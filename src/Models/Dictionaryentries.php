<?php namespace Sanatorium\Sync\Models;

use Cartalyst\Attributes\EntityInterface;
use Illuminate\Database\Eloquent\Model;
use Platform\Attributes\Traits\EntityTrait;
use Cartalyst\Support\Traits\NamespacedEntityTrait;

class Dictionaryentries extends Model implements EntityInterface {

	use EntityTrait, NamespacedEntityTrait;

	/**
	 * {@inheritDoc}
	 */
	protected $table = 'dictionaryentries';

	/**
	 * {@inheritDoc}
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * {@inheritDoc}
	 */
	protected $with = [
		'values.attribute',
	];

	/**
	 * {@inheritDoc}
	 */
	protected static $entityNamespace = 'sanatorium/sync.dictionaryentries';

}
