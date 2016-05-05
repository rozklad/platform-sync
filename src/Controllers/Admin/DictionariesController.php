<?php namespace Sanatorium\Sync\Controllers\Admin;

use Platform\Access\Controllers\AdminController;
use Sanatorium\Sync\Repositories\Dictionary\DictionaryRepositoryInterface;

class DictionariesController extends AdminController {

	/**
	 * {@inheritDoc}
	 */
	protected $csrfWhitelist = [
		'executeAction',
	];

	/**
	 * The Sync repository.
	 *
	 * @var \Sanatorium\Sync\Repositories\Dictionary\DictionaryRepositoryInterface
	 */
	protected $dictionaries;

	/**
	 * Holds all the mass actions we can execute.
	 *
	 * @var array
	 */
	protected $actions = [
		'delete',
		'enable',
		'disable',
	];

	/**
	 * Constructor.
	 *
	 * @param  \Sanatorium\Sync\Repositories\Dictionary\DictionaryRepositoryInterface  $dictionaries
	 * @return void
	 */
	public function __construct(DictionaryRepositoryInterface $dictionaries)
	{
		parent::__construct();

		$this->dictionaries = $dictionaries;
	}

	/**
	 * Display a listing of dictionary.
	 *
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		return view('sanatorium/sync::dictionaries.index');
	}

	/**
	 * Datasource for the dictionary Data Grid.
	 *
	 * @return \Cartalyst\DataGrid\DataGrid
	 */
	public function grid()
	{
		$data = $this->dictionaries->grid();

		$columns = [
			'id',
			'name',
			'slug',
			'created_at',
		];

		$settings = [
			'sort'      => 'created_at',
			'direction' => 'desc',
		];

		$transformer = function($element)
		{
			$element->edit_uri = route('admin.sanatorium.sync.dictionaries.edit', $element->id);

			return $element;
		};

		return datagrid($data, $columns, $settings, $transformer);
	}

	/**
	 * Show the form for creating new dictionary.
	 *
	 * @return \Illuminate\View\View
	 */
	public function create()
	{
		return $this->showForm('create');
	}

	/**
	 * Handle posting of the form for creating new dictionary.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store()
	{
		return $this->processForm('create');
	}

	/**
	 * Show the form for updating dictionary.
	 *
	 * @param  int  $id
	 * @return mixed
	 */
	public function edit($id)
	{
		return $this->showForm('update', $id);
	}

	/**
	 * Handle posting of the form for updating dictionary.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function update($id)
	{
		return $this->processForm('update', $id);
	}

	/**
	 * Remove the specified dictionary.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function delete($id)
	{
		$type = $this->dictionaries->delete($id) ? 'success' : 'error';

		$this->alerts->{$type}(
			trans("sanatorium/sync::dictionaries/message.{$type}.delete")
		);

		return redirect()->route('admin.sanatorium.sync.dictionaries.all');
	}

	/**
	 * Executes the mass action.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function executeAction()
	{
		$action = request()->input('action');

		if (in_array($action, $this->actions))
		{
			foreach (request()->input('rows', []) as $row)
			{
				$this->dictionaries->{$action}($row);
			}

			return response('Success');
		}

		return response('Failed', 500);
	}

	/**
	 * Shows the form.
	 *
	 * @param  string  $mode
	 * @param  int  $id
	 * @return mixed
	 */
	protected function showForm($mode, $id = null)
	{
		// Do we have a dictionary identifier?
		if (isset($id))
		{
			if ( ! $dictionary = $this->dictionaries->find($id))
			{
				$this->alerts->error(trans('sanatorium/sync::dictionaries/message.not_found', compact('id')));

				return redirect()->route('admin.sanatorium.sync.dictionaries.all');
			}
		}
		else
		{
			$dictionary = $this->dictionaries->createModel();
		}

		$attributes = self::optGroupByNamespace(\Sanatorium\Sync\Controllers\Admin\SyncController::userAttributes());

		// Show the page
		return view('sanatorium/sync::dictionaries.form', compact('mode', 'dictionary', 'attributes'));
	}

	public static function optGroupByNamespace($attributes)
	{
		$result = [];

		foreach( $attributes as $attribute ) {

			if ( !isset($result[$attribute['namespace']]) ) {
				$result[$attribute['namespace']] = [];
			}

			$result[$attribute['namespace']][] = $attribute;

		}

		return $result;
	}

	/**
	 * Processes the form.
	 *
	 * @param  string  $mode
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function processForm($mode, $id = null)
	{
		// Store the dictionary
		list($messages, $dictionary) = $this->dictionaries->store($id, request()->except('entries'));

		$entries = request()->get('entries');
		unset($entries['ROW_POSITION']);

		foreach ( $entries as $key => $entry ) {

			$entries[$key] = [
				'slug' => $entry['slug'],
				'options' => json_encode($entry['options'])
			];

		}

		$dictionary->entries()->delete();

		foreach( $entries as $entry )
		{
			$dictionary->entries()->create($entry);
		}

		// Do we have any errors?
		if ($messages->isEmpty())
		{
			$this->alerts->success(trans("sanatorium/sync::dictionaries/message.success.{$mode}"));

			return redirect()->route('admin.sanatorium.sync.dictionaries.all');
		}

		$this->alerts->error($messages, 'form');

		return redirect()->back()->withInput();
	}

}
