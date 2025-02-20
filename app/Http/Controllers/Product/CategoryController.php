<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use App\Services\CategoryService;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{

    public function __construct(public readonly CategoryService $categoryService) {}

    public function index()
    {
        $categories = $this->categoryService->getAllCategoriesWithPagination();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $category = $this->categoryService->createCategory($request->all());

        return response()->json(['message' => 'Category created successfully.', 'category' => $category], 201);
    }

    public function show($id)
    {
        $category = $this->categoryService->getCategoryByIdWithProducts($id);

        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = $this->categoryService->updateCategory($id, $request->all());

        return response()->json(['message' => 'Category updated successfully.', 'category' => $category]);
    }

    public function destroy($id)
    {
        $this->categoryService->deleteCategory($id);

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
