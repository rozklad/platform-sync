<?php namespace Sanatorium\Sync\Connectors;

use Manufacturer;

class ManufacturerConnector {

	public $default_attribute_type = 'input';

	public function seed($manufacturers = [], $obj = null)
	{
		$this->attributes = app('Platform\Attributes\Repositories\AttributeRepositoryInterface');

		$parent = null;

		foreach ( $manufacturers as $manufacturer_name ) 
		{

			$manufacturer_name = trim($manufacturer_name);
			$manufacturer_slug = str_slug($manufacturer_name);

			$manufacturer = Manufacturer::where('slug', $manufacturer_slug)->first();

			if ( !$manufacturer ) {
				$manufacturer = new Manufacturer;

				$normalized_key = 'manufacturer_title';

				$attribute_exists = $this->attributes->where('slug', $normalized_key)->count() > 0;

				if ( !$attribute_exists ) {
					$this->attributes->create([
						'namespace' 	=> Manufacturer::getEntityNamespace(),
						'name'      	=> $normalized_key,
						'description'	=> $normalized_key,
						'type'      	=> $this->default_attribute_type,
						'slug'      	=> $normalized_key,
						'enabled'   	=> 1,
						]);
				}

				$manufacturer->{$normalized_key} = $manufacturer_name;

				$manufacturer->slug = $manufacturer_slug;
				$manufacturer->save();

                // Create new slug based on the data
				$manufacturer->save();
                
			}

			// Attach manufacturer
			if ( $obj ) {
				if ( !$obj->id ) {
					$obj->save();
				}
				$obj->manufacturers()->attach($manufacturer->id);
			}
		}
	}

}