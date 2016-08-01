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

	// Comment
	public function seed($products, $use_dictionary = true, $setup = [])
	{
		$this->attributes = app('Platform\Attributes\Repositories\AttributeRepositoryInterface');

		$categorizing = class_exists('Category');

		// Count of processed entries
		foreach( $products as $product )
		{
			$this->seedItem($product, $use_dictionary, $setup, $categorizing);
		}
	}

	public function seedItem($product, $use_dictionary = true, $setup = [], $categorizing = null)
	{
		if ( !isset($this->attributes) )
			$this->attributes = app('Platform\Attributes\Repositories\AttributeRepositoryInterface');

		if ( is_null($categorizing) )
			$categorizing = class_exists('Category');

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

		$obj->regenerateThumbnails();

		return $obj;
	}

	public function mediaArray($obj, $value, $key)
	{
		if ( !$obj->id ) {
			$obj->save();
		}

		$i = 0;

		$gallery = [];

		if ( isset($value['IMGURL']) )
			$array = $value['IMGURL'];
		elseif ( is_array($value) )
			$array = $value;
		elseif ( is_string($value) )
			$array = [$value];

		foreach( $array as $media )
		{
			$url = (string)$media;

			$i++;

			$medium_id = $this->addRemoteMedia($url);

			if ( $i==1 )
			{

				$obj->product_cover = $medium_id;

			}

			$gallery[] = $medium_id;
		}

		$obj->product_gallery = $gallery;
	}

	public function imgurl($obj, $value, $key)
	{
		// Never trust user input, if imgurl is array after all...
		if ( is_array($value) ) {
			foreach( $value as $img ) {
				$this->imgurl($obj, $value, $key);
			}
		} else
		{
			if ( !$obj->id )
			{
				$obj->save();
			}

			$url = (string) $value;

			$medium_id = $this->addRemoteMedia($url);

			if ( !$obj->hasCoverImage() )
				$obj->product_cover = $medium_id;
			else
				$obj->product_gallery = [$medium_id];
		}
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

	public static function addRemoteMedia($file_url = null)
	{
		if ( empty($file_url) ) return false;

		$basename = basename($file_url);

		$repo = app('platform.media');

		$original_file_url = $file_url;

		$file_url = self::removeCommonAppendix($file_url);

		// Already uploaded
		if ( $downloaded = $repo->whereName($basename)->first() )
			return $downloaded->id;

        // @todo: temp, delete this
        $local_path = base_path('/clients/zpflorence/images/' . $basename);
        if ( file_exists( $local_path ) && !empty($local_path) ) {
            $contents = file_get_contents($local_path);
        } else
        {
            $contents = file_get_contents($file_url);
            if ( empty($contents) ) return false;
        }

		$temp_dir = __DIR__ . '/temp/';

		if ( !file_exists($temp_dir) )
		{
			mkdir($temp_dir, 0777, 1);
			chmod($temp_dir, 0777);
		}

		$temp_path = $temp_dir . $basename;

		file_put_contents($temp_path, $contents);

		$uploaded = new UploadedFile($temp_path, basename($file_url));

		$medium = $repo->upload($uploaded, ['tags' => []]);

		// Delete temporary file
		unlink($temp_path);

		return $medium->id;
	}


	/**
	 * As seen in "http://www.gamlery.cz/deploy/img/products/2456/2456.jpg?modified=1465976639"
	 * We want to prevent this, but we dont want to prevent http://example.tld/pictures?id=71455 to be removed
	 * @param null $file_url
	 * @return string
	 */
	public static function removeCommonAppendix($file_url = null)
	{
		switch( true ) {

			case (strpos($file_url, '.jpg?') !== false):
				$var = explode('.jpg?', $file_url);
				return $var[0] . '.jpg';

				break;

			case (strpos($file_url, '.png?') !== false):
				$var = explode('.png?', $file_url);
				return $var[0] . '.png';

				break;

			case (strpos($file_url, '.gif?') !== false):
				$var = explode('.gif?', $file_url);
				return $var[0] . '.gif';

				break;

			case (strpos($file_url, '.bmp?') !== false):
				$var = explode('.bmp?', $file_url);
				return $var[0] . '.bmp';

				break;

            default:
                return $file_url;

                break;

		}
	}

}