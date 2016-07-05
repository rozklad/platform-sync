<?php namespace Sanatorium\Sync\Plugins\Pricelist\Formatters;

use Sanatorium\Sync\Formatters\ProductFormatter;
use URL;

class PricelistFormatter extends ProductFormatter {

	protected $dictionary = [
		[
			'func'	=> 'escape',
			'label' => 'PRODUCTNAME',
			'value' => 'product_title',
		],
		[
			'func'  => 'price_vat',
			'label' => 'PRICE_VAT',
		],
		[
			'func'  => 'price',
			'label' => 'PRICE',
		],
		[
			'label' => 'STOCK',
			'value' => 'stock',
		],
	];

	public static $wrapper = 'SHOP';
	public static $item = 'SHOPITEM';
	
	public $filename = 'pricelist_export.xml';

	public $title = 'Ceník';
	public $description = 'Export produktů do ceníku';

}
