<?php namespace Sanatorium\Sync\Controllers\Admin;

use Platform\Access\Controllers\AdminController;
use File;

class SyncController extends AdminController {

	public $functions = [
		'column',
		'categoryText',
		'mediaArray',
		'price',
		'priceVat',
		'imgurl',
	];

	public function index()
	{
		$services = app('sanatorium.sync.formatters')->getServices();

		$formatters = [];

		foreach ( $services as $key => $item ) {

			$formatters[$key] = [
				'url' 			=> route('sanatorium.sync.export.formatter', ['type' => $key]),
				'icon' 			=> 'fa fa-file-code-o',
				'created' 		=> date('j.n.Y H:i:s', $item->getFilemtime()),
				'title'			=> $item->title,
				'description'   => $item->description,
				'refresh_url'   => route('sanatorium.sync.export.refresh', ['type' => $key]),
			];
		}

		return view('sanatorium/sync::index', compact('formatters'));
	}

	public function refresh($type)
	{
		if ( $type == 'all' )
			return $this->refreshAll();

		$formatters = app('sanatorium.sync.formatters')->getServices();

		$type = strtolower($type);

		if ( ! isset($formatters[$type]) )
			return 'Unknown provider ' . $type . '';

		$formatter = $formatters[$type];

		$formatter->refresh();

		return redirect()->back();
	}

	public function refreshAll()
	{
		$formatters = app('sanatorium.sync.formatters')->getServices();

		$results = [];

		foreach( $formatters as $key => $formatter ) {
			$results[$key] = $formatter->refresh();
		}

		return $results;
	}

	public function upload()
	{

		$file = request()->file('import');

		// Check if file was uploaded
		if ( !is_object($file) ) {

			if ( request()->ajax() ) {
				
				return response('Failed', 500);
			
			} else {
			
				$this->alerts->error( trans('sanatorium/sync::common.messages.errors.no_file') );

				return redirect()->back();
			
			}
		}

		$this->attributes = app('Platform\Attributes\Repositories\AttributeRepositoryInterface');

		$data = $this->getFileData($file);

		if ( is_object($data->SHOPITEM) ) {
			$structure = get_object_vars($data->SHOPITEM[0]);
		} else {
			$structure = $data;
		}

		$attributes = $this->attributes->where('namespace', 'sanatorium/shop.product')->get();

		$functions = $this->functions;

		$relations = [
			'manufacturers'
		];

		if ( request()->ajax() ) {
			return [
				'structure' => self::dynaTree($structure),
				'attributes' => $attributes,
				'functions' => $functions,
				'relations' => $relations
			];
		}

		return view('sanatorium/sync::upload', compact('structure', 'functions', 'attributes'));
	}

	public static function dynatree($input = [])
	{
		$results = [];

		foreach( $input as $key => $value ) {
			$item = [
				'title' => $key
			];

			if ( is_object($value) ) {
				$item['children'] = self::dynatree(get_object_vars($value));
			}

			$results[] = $item;
		}

		return $results;
	}

	public function getFileData($file = null)
	{
		$path = $file->getPathname();

		$contents = file_get_contents($path);

		$mime = File::mimeType($path);

		$functions = $this->functions;

		switch( $mime ) {
			case 'application/xml':
				$data = simplexml_load_string($contents, null, LIBXML_NOCDATA);
			break;

			default:
				throw new \Exception('Invalid extension');
			break;
		}

		return $data;
	}

	public function setup()
	{

		$file = request()->file('import');

		// Check if file was uploaded
		if ( !is_object($file) ) {

			if ( request()->ajax() ) {
				
				return response('Failed', 500);
			
			} else {
			
				$this->alerts->error( trans('sanatorium/sync::common.messages.errors.no_file') );

				return redirect()->back();
			
			}
		}

		$data = $this->getFileData($file);

		$connector = new \Sanatorium\Sync\Connectors\ProductConnector;

		$connector->seed( $data, request()->has('dictionary'), request()->get('types') );

		if ( request()->ajax() ) {

			return response('Succes');

		} else {

			$this->alerts->success( trans('sanatorium/sync::common.messages.success.imported') );

			return redirect()->back();

		}
	}
}