<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function getAllProducts($filters)
    {
        $query = Product::query();

        if (!empty($filters['name'])) {
            $query->where('name', 'LIKE', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['price_min']) && !empty($filters['price_max'])) {
            $query->whereBetween('price', [$filters['price_min'], $filters['price_max']]);
        }

        return $query->paginate(10);
    }

    public function createProduct($data)
    {
        return Product::create($data);
    }

    public function getProductById($id)
    {
        return Product::findOrFail($id);
    }

    public function updateProduct($id, $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }

    public function deleteProduct($id)
    {
        Product::findOrFail($id)->delete();
    }
}
