<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use Carbon\Carbon;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $user1 = User::where('email', 'yamada@example.com')
            ->first();
        $user2 = User::where('email', 'suzuki@example.com')
            ->first();

        $book1 = Book::create([
            'user_id' => $user1->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $book2 = Book::create([
            'user_id' => $user1->id,
            'title' => '人を動かす',
            'author' => 'D・カーネギー',
            'isbn' => '9784422100524',
            'published_date' => '1936-10-01',
            'description' => '良好な人間関係を築き、人の心を動かすための原則を紹介する自己啓発書。',
        ]);


        $book3 = Book::create([
            'user_id' => $user1->id,
            'title' => 'リーダブルコード',
            'author' => 'Dustin Boswell',
            'isbn' => '9784873115658',
            'published_date' => '2012-06-23',
            'description' => '読みやすく理解しやすいコードを書くための考え方やテクニックを紹介する技術書。',
        ]);

        $book4 = Book::create([
            'user_id' => $user1->id,
            'title' => '7つの習慣',
            'author' => 'スティーブン・R・コヴィー',
            'isbn' => '9784863940246',
            'published_date' => '2013-08-30',
            'description' => '人生や仕事をより良くするための考え方と習慣を体系的に紹介する自己啓発書。',
        ]);

        $book5 = Book::create([
            'user_id' => $user1->id,
            'title' => '坊っちゃん',
            'author' => '夏目漱石',
            'isbn' => '9784101010021',
            'published_date' => '1906-04-01',
            'description' => '正義感の強い主人公が赴任先の学校で巻き起こす騒動を描いた夏目漱石の小説。',
        ]);

        $book6 = Book::create([
            'user_id' => $user2->id,
            'title' => 'サピエンス全史',
            'author' => 'ユヴァル・ノア・ハラリ',
            'isbn' => '9784309226712',
            'published_date' => '2016-09-08',
            'description' => '人類の誕生から現代までの歴史を、科学や社会の視点から読み解く歴史書。',
        ]);

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
