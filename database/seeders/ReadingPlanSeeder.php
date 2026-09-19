<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $user1 = User::where('email', 'yamada@example.com')
            ->first();
        $user2 = User::where('email', 'suzuki@example.com')
            ->first();

        $book1 = Book::where('isbn', '9784101010014')->firstOrFail();
        $book2 = Book::where('isbn', '9784422100524')->firstOrFail();
        $book3 = Book::where('isbn', '9784873115658')->firstOrFail();
        $book4 = Book::where('isbn', '9784863940246')->firstOrFail();
        $book5 = Book::where('isbn', '9784101010021')->firstOrFail();
        $book6 = Book::where('isbn', '9784309226712')->firstOrFail();

        ReadingPlan::create([
            'user_id' => $user1->id,
            'book_id' => $book1->id,
            'target_date' => Carbon::today()->addDays(3),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => $user1->id,
            'book_id' => $book2->id,
            'target_date' => Carbon::today(),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => $user1->id,
            'book_id' => $book3->id,
            'target_date' => Carbon::today()->subDays(3),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => $user1->id,
            'book_id' => $book4->id,
            'target_date' => Carbon::today()->addDays(7),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => $user1->id,
            'book_id' => $book5->id,
            'target_date' => Carbon::today()->subDays(10),
            'completed_at' => Carbon::today()->subDays(5),
            'status' => 'completed',
        ]);

        ReadingPlan::create([
            'user_id' => $user2->id,
            'book_id' => $book6->id,
            'target_date' => Carbon::today()->addDays(5),
            'status' => 'in_progress',
        ]);
    }
}
