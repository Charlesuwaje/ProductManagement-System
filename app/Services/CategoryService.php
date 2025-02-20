<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryService
{
    public function getAllCategoriesWithPagination()
    {
        return Category::with('products')->paginate(10);
    }

    public function createCategory(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            abort(422, $validator->errors()->first());
        }

        return Category::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function getCategoryByIdWithProducts($id)
    {
        $category = Category::with('products')->findOrFail($id);

        return $category;
    }

    public function updateCategory($id, array $data)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($data, [
            'name' => 'required|string|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            abort(422, $validator->errors()->first());
        }

        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? $category->description,
        ]);

        return $category;
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
    }
}
