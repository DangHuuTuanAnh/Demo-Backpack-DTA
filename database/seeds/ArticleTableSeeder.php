<?php

use Illuminate\Database\Seeder;
use App\Models\Article;

class ArticleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        for ($i=0; $i < 50; $i++) {
            Article::create([
                'title'=>$faker->sentence(3),
                'content'=>$faker->paragraph(6),
                'slug'=>join(',',$faker->words(4))
            ]);
        }
    }
}
