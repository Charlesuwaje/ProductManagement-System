<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{

    public function __construct(public readonly ProductService $productService) {}

    public function index(Request $request)
    {

        return $this->productService->getAllProducts($request->all());
    }

    public function store(ProductRequest $request)
    {
        return $this->productService->createProduct($request->validated());
    }

    public function show($id)
    {
        return $this->productService->getProductById($id);
    }

    public function update(ProductRequest $request, $id)
    {
        return $this->productService->updateProduct($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->productService->deleteProduct($id);
    }
}
