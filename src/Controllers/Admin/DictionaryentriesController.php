<?php namespace Sanatorium\Sync\Controllers\Admin;

use Platform\Access\Controllers\AdminController;
use Sanatorium\Sync\Repositories\Dictionaryentries\DictionaryentriesRepositoryInterface;

class DictionaryentriesController extends AdminController {

	/**
	 * {@inheritDoc}
	 */
	protected $csrfWhitelist = [
		'executeAction',
	];

	/**
	 * The Sync repository.
	 *
	 * @var \Sanatorium\Sync\Repositories\Dictionaryentries\DictionaryentriesRepositoryInterface
	 */
	protected $dictionaryentries;

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
	 * @param  \Sanatorium\Sync\Repositories\Dictionaryentries\DictionaryentriesRepositoryInterface  $dictionaryentries
	 * @return void
	 */
	public function __construct(DictionaryentriesRepositoryInterface $dictionaryentries)
	{
		parent::__construct();

		$this->dictionaryentries = $dictionaryentries;
	}

	/**
	 * Display a listing of dictionaryentries.
	 *
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		return view('sanatorium/sync::dictionaryentries.index');
	}

	/**
	 * Datasource for the dictionaryentries Data Grid.
	 *
	 * @return \Cartalyst\DataGrid\DataGrid
	 */
	public function grid()
	{
		$data = $this->dictionaryentries->grid();

		$columns = [
			'id',
			'dictionary_id',
			'slug',
			'options',
			'created_at',
		];

		$settings = [
			'sort'      => 'created_at',
			'direction' => 'desc',
		];

		$transformer = function($element)
		{
			$element->edit_uri = route('admin.sanatorium.sync.dictionaryentries.edit', $element->id);

			return $element;
		};

		return datagrid($data, $columns, $settings, $transformer);
	}

	/**
	 * Show the form for creating new dictionaryentries.
	 *
	 * @return \Illuminate\View\View
	 */
	public function create()
	{
		return $this->showForm('create');
	}

	/**
	 * Handle posting of the form for creating new dictionaryentries.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store()
	{
		return $this->processForm('create');
	}

	/**
	 * Show the form for updating dictionaryentries.
	 *
	 * @param  int  $id
	 * @return mixed
	 */
	public function edit($id)
	{
		return $this->showForm('update', $id);
	}

	/**
	 * Handle posting of the form for updating dictionaryentries.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function update($id)
	{
		return $this->processForm('update', $id);
	}

	/**
	 * Remove the specified dictionaryentries.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function delete($id)
	{
		$type = $this->dictionaryentries->delete($id) ? 'success' : 'error';

		$this->alerts->{$type}(
			trans("sanatorium/sync::dictionaryentries/message.{$type}.delete")
		);

		return redirect()->route('admin.sanatorium.sync.dictionaryentries.all');
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
				$this->dictionaryentries->{$action}($row);
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
		// Do we have a dictionaryentries identifier?
		if (isset($id))
		{
			if ( ! $dictionaryentries = $this->dictionaryentries->find($id))
			{
				$this->alerts->error(trans('sanatorium/sync::dictionaryentries/message.not_found', compact('id')));

				return redirect()->route('admin.sanatorium.sync.dictionaryentries.all');
			}
		}
		else
		{
			$dictionaryentries = $this->dictionaryentries->createModel();
		}

		// Show the page
		return view('sanatorium/sync::dictionaryentries.form', compact('mode', 'dictionaryentries'));
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
		// Store the dictionaryentries
		list($messages) = $this->dictionaryentries->store($id, request()->all());

		// Do we have any errors?
		if ($messages->isEmpty())
		{
			$this->alerts->success(trans("sanatorium/sync::dictionaryentries/message.success.{$mode}"));

			return redirect()->route('admin.sanatorium.sync.dictionaryentries.all');
		}

		$this->alerts->error($messages, 'form');

		return redirect()->back()->withInput();
	}

}
