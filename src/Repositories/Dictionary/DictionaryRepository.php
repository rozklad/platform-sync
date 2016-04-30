<?php namespace Sanatorium\Sync\Repositories\Dictionary;

use Cartalyst\Support\Traits;
use Illuminate\Container\Container;
use Symfony\Component\Finder\Finder;

class DictionaryRepository implements DictionaryRepositoryInterface {

	use Traits\ContainerTrait, Traits\EventTrait, Traits\RepositoryTrait, Traits\ValidatorTrait;

	/**
	 * The Data handler.
	 *
	 * @var \Sanatorium\Sync\Handlers\Dictionary\DictionaryDataHandlerInterface
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

		$this->data = $app['sanatorium.sync.dictionary.handler.data'];

		$this->setValidator($app['sanatorium.sync.dictionary.validator']);

		$this->setModel(get_class($app['Sanatorium\Sync\Models\Dictionary']));
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
		return $this->container['cache']->rememberForever('sanatorium.sync.dictionary.all', function()
		{
			return $this->createModel()->get();
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function find($id)
	{
		return $this->container['cache']->rememberForever('sanatorium.sync.dictionary.'.$id, function() use ($id)
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
		// Create a new dictionary
		$dictionary = $this->createModel();

		// Fire the 'sanatorium.sync.dictionary.creating' event
		if ($this->fireEvent('sanatorium.sync.dictionary.creating', [ $input ]) === false)
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
			// Save the dictionary
			$dictionary->fill($data)->save();

			// Fire the 'sanatorium.sync.dictionary.created' event
			$this->fireEvent('sanatorium.sync.dictionary.created', [ $dictionary ]);
		}

		return [ $messages, $dictionary ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function update($id, array $input)
	{
		// Get the dictionary object
		$dictionary = $this->find($id);

		// Fire the 'sanatorium.sync.dictionary.updating' event
		if ($this->fireEvent('sanatorium.sync.dictionary.updating', [ $dictionary, $input ]) === false)
		{
			return false;
		}

		// Prepare the submitted data
		$data = $this->data->prepare($input);

		// Validate the submitted data
		$messages = $this->validForUpdate($dictionary, $data);

		// Check if the validation returned any errors
		if ($messages->isEmpty())
		{
			// Update the dictionary
			$dictionary->fill($data)->save();

			// Fire the 'sanatorium.sync.dictionary.updated' event
			$this->fireEvent('sanatorium.sync.dictionary.updated', [ $dictionary ]);
		}

		return [ $messages, $dictionary ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete($id)
	{
		// Check if the dictionary exists
		if ($dictionary = $this->find($id))
		{
			// Fire the 'sanatorium.sync.dictionary.deleted' event
			$this->fireEvent('sanatorium.sync.dictionary.deleted', [ $dictionary ]);

			// Delete the dictionary entry
			$dictionary->delete();

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
