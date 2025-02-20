<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic gadgets and devices.'],
            ['name' => 'Clothing', 'description' => 'Men, women, and children clothing.'],
            ['name' => 'Books', 'description' => 'All kinds of books and novels.'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
