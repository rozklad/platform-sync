<?php namespace Sanatorium\Sync\Formatters;

use Sanatorium\Shop\Repositories\Product\ProductRepositoryInterface;

class ProductFormatter extends Formatter {

	public function __construct(ProductRepositoryInterface $products)
	{
		$this->products = $products;
	}

}