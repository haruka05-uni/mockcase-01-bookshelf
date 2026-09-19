<?php

namespace Database\Seeders;

use App\models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        foreach ($users as $user) {
            $favoriteBookIds = $books
                ->random(rand(3, 5))
                ->pluck('id');

            $user->favoriteBooks()->syncWithoutDetaching($favoriteBookIds);
        }
    }
}
