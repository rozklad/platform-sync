<?php namespace Sanatorium\Sync\Repositories\Dictionaryentries;

use Cartalyst\Support\Traits;
use Illuminate\Container\Container;
use Symfony\Component\Finder\Finder;

class DictionaryentriesRepository implements DictionaryentriesRepositoryInterface {

	use Traits\ContainerTrait, Traits\EventTrait, Traits\RepositoryTrait, Traits\ValidatorTrait;

	/**
	 * The Data handler.
	 *
	 * @var \Sanatorium\Sync\Handlers\Dictionaryentries\DictionaryentriesDataHandlerInterface
	 */
	protected $data;

	/**
	 * The Eloquent sync model.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Constructor.
	 *
	 * @param  \Illuminate\Container\Container  $app
	 * @return void
	 */
	public function __construct(Container $app)
	{
		$this->setContainer($app);

		$this->setDispatcher($app['events']);

		$this->data = $app['sanatorium.sync.dictionaryentries.handler.data'];

		$this->setValidator($app['sanatorium.sync.dictionaryentries.validator']);

		$this->setModel(get_class($app['Sanatorium\Sync\Models\Dictionaryentries']));
	}

	/**
	 * {@inheritDoc}
	 */
	public function grid()
	{
		return $this
			->createModel();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findAll()
	{
		return $this->container['cache']->rememberForever('sanatorium.sync.dictionaryentries.all', function()
		{
			return $this->createModel()->get();
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function find($id)
	{
		return $this->container['cache']->rememberForever('sanatorium.sync.dictionaryentries.'.$id, function() use ($id)
		{
			return $this->createModel()->find($id);
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function validForCreation(array $input)
	{
		return $this->validator->on('create')->validate($input);
	}

	/**
	 * {@inheritDoc}
	 */
	public function validForUpdate($id, array $input)
	{
		return $this->validator->on('update')->validate($input);
	}

	/**
	 * {@inheritDoc}
	 */
	public function store($id, array $input)
	{
		return ! $id ? $this->create($input) : $this->update($id, $input);
	}

	/**
	 * {@inheritDoc}
	 */
	public function create(array $input)
	{
		// Create a new dictionaryentries
		$dictionaryentries = $this->createModel();

		// Fire the 'sanatorium.sync.dictionaryentries.creating' event
		if ($this->fireEvent('sanatorium.sync.dictionaryentries.creating', [ $input ]) === false)
		{
			return false;
		}

		// Prepare the submitted data
		$data = $this->data->prepare($input);

		// Validate the submitted data
		$messages = $this->validForCreation($data);

		// Check if the validation returned any errors
		if ($messages->isEmpty())
		{
			// Save the dictionaryentries
			$dictionaryentries->fill($data)->save();

			// Fire the 'sanatorium.sync.dictionaryentries.created' event
			$this->fireEvent('sanatorium.sync.dictionaryentries.created', [ $dictionaryentries ]);
		}

		return [ $messages, $dictionaryentries ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function update($id, array $input)
	{
		// Get the dictionaryentries object
		$dictionaryentries = $this->find($id);

		// Fire the 'sanatorium.sync.dictionaryentries.updating' event
		if ($this->fireEvent('sanatorium.sync.dictionaryentries.updating', [ $dictionaryentries, $input ]) === false)
		{
			return false;
		}

		// Prepare the submitted data
		$data = $this->data->prepare($input);

		// Validate the submitted data
		$messages = $this->validForUpdate($dictionaryentries, $data);

		// Check if the validation returned any errors
		if ($messages->isEmpty())
		{
			// Update the dictionaryentries
			$dictionaryentries->fill($data)->save();

			// Fire the 'sanatorium.sync.dictionaryentries.updated' event
			$this->fireEvent('sanatorium.sync.dictionaryentries.updated', [ $dictionaryentries ]);
		}

		return [ $messages, $dictionaryentries ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete($id)
	{
		// Check if the dictionaryentries exists
		if ($dictionaryentries = $this->find($id))
		{
			// Fire the 'sanatorium.sync.dictionaryentries.deleted' event
			$this->fireEvent('sanatorium.sync.dictionaryentries.deleted', [ $dictionaryentries ]);

			// Delete the dictionaryentries entry
			$dictionaryentries->delete();

			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function enable($id)
	{
		$this->validator->bypass();

		return $this->update($id, [ 'enabled' => true ]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function disable($id)
	{
		$this->validator->bypass();

		return $this->update($id, [ 'enabled' => false ]);
	}

}
