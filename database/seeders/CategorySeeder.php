<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Graphic Design',
                'description' => 'Logo design, branding, illustrations, and visual content creation',
            ],
            [
                'name'        => 'Web Development',
                'description' => 'Website development, web applications, and frontend/backend programming',
            ],
            [
                'name'        => 'Content Writing',
                'description' => 'Article writing, blog posts, copywriting, and content creation',
            ],
            [
                'name'        => 'Digital Marketing',
                'description' => 'Social media marketing, SEO, email marketing, and online advertising',
            ],
            [
                'name'        => 'Video Editing',
                'description' => 'Video production, editing, motion graphics, and animation',
            ],
            [
                'name'        => 'Translation',
                'description' => 'Language translation, localization, and interpretation services',
            ],
            [
                'name'        => 'Data Entry',
                'description' => 'Data processing, spreadsheet management, and administrative tasks',
            ],
            [
                'name'        => 'Tutoring',
                'description' => 'Academic tutoring, exam preparation, and educational support',
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name'        => $category['name'],
                'slug'        => Str::slug($category['name']),
                'description' => $category['description'],
            ]);
        }
    }
}
