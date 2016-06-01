<?php namespace Sanatorium\Sync\Connectors;

use Product;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductConnector {

	public $dictionary = [
		'name' => 'product_title',
		'description' => 'product_description',
		'product' => 'product_title',
		'productno' => 'code',
		'url' => 'old_url',
	];

	public $default_attribute_type = 'input';

	public function seed($products, $use_dictionary = true, $setup = [])
	{
		$this->attributes = app('Platform\Attributes\Repositories\AttributeRepositoryInterface');

		$categorizing = class_exists('Category');

		// Count of processed entries
		$index = 0;

		foreach( $products as $product )
		{
			$index++;

			if ( $index < 1750 )
				continue;

			$obj = new Product;

			foreach( $product as $key => $value ) 
			{
				$normalized_key = $this->translateKey( self::normalizeKey($key) );

				if ( isset($setup[$key]) ) 
				{
					switch( true ) 
					{
						case $setup[$key] == 'ignore':
							continue;
						break;

						case $setup[$key] == 'create_attribute':
							
							$attribute_exists = $this->attributes->where('slug', $normalized_key)->count() > 0;

							if ( !$attribute_exists ) {
								$this->attributes->create([
									'namespace' 	=> Product::getEntityNamespace(),
				                    'name'      	=> $normalized_key,
				                    'description'	=> $normalized_key,
				                    'type'      	=> $this->default_attribute_type,
				                    'slug'      	=> $normalized_key,
				                    'enabled'   	=> 1,
									]);
							}

							$obj->{$normalized_key} = $value;
						break;

						case strpos($setup[$key], 'attribute.') !== false:
							if ( is_string($value) )
								$obj->{$normalized_key} = (string)$value;
						break;

						case strpos($setup[$key], 'functions.') !== false:
							$function = str_replace('functions.', '', $setup[$key]);
							$value = $this->$function( $obj, $value, $normalized_key );
						break;

						case strpos($setup[$key], 'relations.') !== false:
							$relation = str_replace('relations.', '', $setup[$key]);

							if ( is_array($value) ) {
								$this->{$relation}($obj, $value, $setup[$key]);
							} else {
								$this->{$relation}($obj, [$value], $setup[$key]);
							}

						break;
					}

				}
			}

			$obj->resluggify();
			$obj->save();
		}
	}

	public function mediaArray($obj, $value, $key)
	{
		if ( !$obj->id ) {
			$obj->save();
		}

		$i = 0;

		foreach( $value->IMGURL as $media ) 
		{
			$url = (string)$media;

			$i++;

			// @todo: prepare for multiple files
			//$this->addRemoteMedia($url, $i==1);
		}
	}

	public function imgurl($obj, $value, $key)
	{
		if ( !$obj->id ) {
			$obj->save();
		}

		$url = (string)$value;

		if ( $medium_id = $this->addRemoteMedia($url, 1) )
			$obj->product_cover = $medium_id;
	}

	public function priceVat($obj, $value, $key)
	{
		$obj->price_vat = (string)$value;
	}

	public function price($obj, $value, $key)
	{
		$obj->price = (string)$value;
	}

	public function column($obj, $value, $key)
	{
		$obj->{$key} = (string)$value;
	}

	public function categoryText($obj, $value, $key = null, $delimiter = ' | ')
	{
		$categories = explode($delimiter, $value);

		$connector = new \Sanatorium\Sync\Connectors\CategoryConnector;

		$connector->seed( $categories, $obj );
	}

	public function manufacturers($obj, $manufacturers, $key = null)
	{
		$connector = new \Sanatorium\Sync\Connectors\ManufacturerConnector;

		$connector->seed( $manufacturers, $obj );
	}

	public static function normalizeKey($key)
	{
		return strtolower($key);
	}

	public function translateKey($normalized_key)
	{
		if ( isset($this->dictionary[$normalized_key]) ) {
			return $this->dictionary[$normalized_key];
		}

		return $normalized_key;
	}

	public function addRemoteMedia($file_url = null, $cover = false, $input = [])
	{
		if ( empty($file_url) ) return false;

		$contents = @file_get_contents($file_url);

		if ( empty($contents) ) return false;

		$temp_dir = __DIR__ . '/temp/';

		if ( !file_exists($temp_dir) )
		{
			mkdir($temp_dir, 0777, 1);
			chmod($temp_dir, 0777);
		}

		$temp_path = $temp_dir . basename($file_url);

		file_put_contents($temp_path, $contents);

		$uploaded = new UploadedFile($temp_path, basename($file_url));

		$medium = app('platform.media')->upload($uploaded, ['tags' => []]);

		// Delete temporary file
		unlink($temp_path);

		return $medium->id;
	}

}