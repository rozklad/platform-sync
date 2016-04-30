<?php namespace Sanatorium\Sync\Controllers\Frontend;

use Platform\Foundation\Controllers\Controller;

class DictionariesController extends Controller {

	/**
	 * Return the main view.
	 *
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		return view('sanatorium/sync::index');
	}

}
