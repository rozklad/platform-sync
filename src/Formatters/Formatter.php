<?php namespace Sanatorium\Sync\Formatters;

use File;
use Response;
use URL;
use Sanatorium\Shop\Repositories\Product\ProductRepositoryInterface;
use StorageUrl;

class Formatter {

	protected static $chunkSize = 200;

	public $folder = 'exports/';

	public $filename = 'export.xml';

	public $title = 'Export';
	public $description = 'Export produktů';

	public function __construct(ProductRepositoryInterface $products)
	{
		$this->products = $products;
	}

	public function responseXML($contents) 
	{
		
		$response = Response::make($contents);
		
		$response->header('Content-Type', 'text/xml');
		
		return $response;

	}

	public function refresh()
	{
		if ( $this->export($this->products, false) )
			return true;

		return false;
	}

	public function getFilepath()
	{
		return storage_path( $this->folder );
	}

	public function getFilemtime()
	{
		if ( !$this->exists() )
			return time();

		$filepath = $this->getFilepath();

		return File::lastModified( $filepath . $this->filename );
	}

	public function exists()
	{
		$filepath = $this->getFilepath();

		if ( File::exists($filepath . $this->filename) ) {
			return true;
		}

		return false;
	}

	public function export($repository = null, $cache = true)
	{
		$filepath = $this->getFilepath();

		if ( $this->exists() && $cache ) {
			return $this->responseXML( File::get($filepath . $this->filename) );
		}

		$contents = $this->render($repository);

		// Create directory if not exists
		if ( !File::exists($filepath) ) {
			File::makeDirectory($filepath);
		}

		File::put($filepath . $this->filename, $contents);

		return $this->responseXML($contents);
	}

	public function render($repository = null)
	{

		$self = get_called_class();

		$this->xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><'.$self::$wrapper.'></'.$self::$wrapper.'>');
		$this->xmlItemsElem = $this->xml;

		$repository->chunk($self::$chunkSize, function($products) use ($self) {

			foreach ($products as $product) {

				$shopitem = $this->xmlItemsElem->addChild($self::$item, '');

				foreach( $this->dictionary as $node ) {

					if ( isset($node['skip']) )
						continue;

					// Use function to get value
					if ( isset($node['func']) ) {
						$funcname = $node['func'];
						$value = $self::{$funcname}($product, (isset($node['value']) ? $product->{$node['value']} : null) );
					} else if ( isset($node['value']) ) {
						// Use property to get value
						$value = $product->{$node['value']};
					}

					if ( $value || $value == 0 )
						$shopitem->addChild($node['label'], $value);
				}

			}
		});

		return $this->xml->asXML();
	}

	public static function escape($object, $value = null)
	{
		return
			trim(
				preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '',
					str_replace([
						'•',			// Replacables
						'„',
						'“',
					],
					[
						'',				// Replaces
						'',
						'',
					],
					htmlspecialchars(
						strip_tags($value),
						ENT_XML1,
						'UTF-8'
					))
				)
			);
	}

	public static function categorytext($object, $value = null)
	{
		// Categories
		$emptyCategory = 1;
		$categoryText = "";
		foreach ($object->categories as $category) {
			if ($emptyCategory === 1) {
				$categoryText .= $category->slug;
				$emptyCategory = 0;
			} else {
				$categoryText .= " | " . $category->slug;
			}
		}
		return $categoryText;
	}

	public static function price($object, $value = null)
	{
		return $object->price;
	}

	public static function price_vat($object, $value = null)
	{
		return $object->price_vat;
	}

	public static function url($object, $value = null)
	{
		return URL::to($object->url);
	}

	public static function cover_image($object, $value = null)
	{
		return StorageUrl::url($object->coverThumb());
	}

	public static function deliverydate($object, $value = null)
	{
		return 2;
	}

	public static function manufacturer($object, $value)
	{
		if ($manufacturer = $object->manufacturers()->first())
			return $manufacturer->manufacturer_name;

		return null;
	}

}
