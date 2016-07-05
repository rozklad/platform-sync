<?php namespace Sanatorium\Sync\Controllers\Frontend;

use Platform\Foundation\Controllers\Controller;
use File;
use Sanatorium\Shop\Repositories\Product\ProductRepositoryInterface;

class ExportController extends Controller {

	/**
	 * Constructor.
	 *
	 * @param  \Sanatorium\Shop\Repositories\Product\ProductRepositoryInterface  $products
	 * @return void
	 */
	public function __construct(ProductRepositoryInterface $products)
	{
		parent::__construct();

		$this->products = $products;
	}

	public function index($type)
	{
		$formatters = app('sanatorium.sync.formatters')->getServices();
		
		$type = strtolower($type);

		if ( ! isset($formatters[$type]) )
			return 'Unknown provider ' . $type . '';

		$formatter = app($formatters[$type]);

		return $formatter->export($this->products);
	}

}
