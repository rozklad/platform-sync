<?php namespace Sanatorium\Sync\Connectors;

use Category;

class CategoryConnector {

	public $default_attribute_type = 'input';

	public function seed($categories = [], $obj = null)
	{
		$this->attributes = app('Platform\Attributes\Repositories\AttributeRepositoryInterface');

		$parent = null;

		foreach ( $categories as $category_name ) 
		{

			$category_name = trim($category_name);
			$category_slug = str_slug($category_name);

			$category = Category::where('slug', $category_slug)->first();

			if ( !$category ) {
				$category = new Category;

				$normalized_key = 'category_title';

				$attribute_exists = $this->attributes->where('slug', $normalized_key)->count() > 0;

				if ( !$attribute_exists ) {
					$this->attributes->create([
						'namespace' 	=> Category::getEntityNamespace(),
						'name'      	=> $normalized_key,
						'description'	=> $normalized_key,
						'type'      	=> $this->default_attribute_type,
						'slug'      	=> $normalized_key,
						'enabled'   	=> 1,
						]);
				}

				$category->{$normalized_key} = $category_name;

				if ( $parent ) {
					$category->parent = $parent->id;
				}

				$category->slug = $category_slug;
				$category->save();

                // Create new slug based on the data
				$category->save();
                
			}

			$parent = $category;

			// Attach category
			if ( $obj ) {
				if ( !$obj->id ) {
					$obj->save();
				}
				$obj->categories()->attach($category->id);
			}
		}
	}

}