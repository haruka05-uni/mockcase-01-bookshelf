<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\models\Book;

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
