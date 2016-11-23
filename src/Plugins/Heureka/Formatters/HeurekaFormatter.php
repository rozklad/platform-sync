<?php namespace Sanatorium\Sync\Plugins\Heureka\Formatters;

use Sanatorium\Sync\Formatters\ProductFormatter;
use URL;

class HeurekaFormatter extends ProductFormatter {

	protected $dictionary = [
		[
			'func'	=> 'escape',
			'label' => 'PRODUCTNAME',
			'value' => 'product_title',
		],
		[
			'func'	=> 'escape',
			'label' => 'DESCRIPTION',
			'value' => 'product_description',
		],
		[
			'func' 	=> 'categorytext',
			'label' => 'CATEGORYTEXT',
		],
		[
			'func'	=> 'deliverydate',
			'label' => 'DELIVERY_DATE',
		],
		[
			'label' => 'EAN',
			'value' => 'ean',
		],
		[
			'label' => 'ITEM_ID',
			'value' => 'id',
		],
		[
			'func'	=> 'cover_image',
			'label' => 'IMGURL',
		],
		[
			'label' => 'IMGURL_ALTERNATIVE',
			'skip'	=> true
		],
		[
			'func'	=> 'manufacturer',
			'label' => 'MANUFACTURER',
		],
		[
			'func'  => 'price_vat',
			'label' => 'PRICE_VAT',
		],
		[
			'func'	=> 'url',
			'label' => 'URL'
		]
	];

	public static $wrapper = 'SHOP';
	public static $item = 'SHOPITEM';
	
	public $filename = 'heureka_export.xml';

	public $title = 'Heureka';
	public $description = 'Heureka export produktů';

	public static function deliverydate($object, $value = null)
	{
		return $object->getAvailability('heureka')->alias ?: 0;
	}
}
