<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $reviews = [
            [
                'user_id' => $users[0]->id,
                'book_id' => $books[0]->id,
                'rating' => 5,
                'comment' => '猫の視点から人間社会を描いていて、ユーモアがあって面白かったです。',
            ],
            [
                'user_id' => $users[1]->id,
                'book_id' => $books[0]->id,
                'rating' => 4,
                'comment' => '独特な語り口が印象的で、最後まで楽しく読めました。',
            ],
            [
                'user_id' => $users[2]->id,
                'book_id' => $books[0]->id,
                'rating' => 4,
                'comment' => '昔の作品ですが、今読んでも人間観察の鋭さが面白いです。',
            ],

            [
                'user_id' => $users[0]->id,
                'book_id' => $books[1]->id,
                'rating' => 5,
                'comment' => '人との接し方について具体的に書かれていて、とても参考になりました。',
            ],
            [
                'user_id' => $users[3]->id,
                'book_id' => $books[1]->id,
                'rating' => 4,
                'comment' => '仕事だけでなく日常の人間関係にも活かせる内容だと思いました。',
            ],
            [
                'user_id' => $users[4]->id,
                'book_id' => $books[1]->id,
                'rating' => 5,
                'comment' => 'すぐに実践できる考え方が多く、何度も読み返したい一冊です。',
            ],

            [
                'user_id' => $users[1]->id,
                'book_id' => $books[2]->id,
                'rating' => 5,
                'comment' => '読みやすいコードを書くための考え方が具体的で分かりやすかったです。',
            ],
            [
                'user_id' => $users[2]->id,
                'book_id' => $books[2]->id,
                'rating' => 4,
                'comment' => 'コードを書くときに意識したいポイントが多く、実践的な内容でした。',
            ],
            [
                'user_id' => $users[4]->id,
                'book_id' => $books[2]->id,
                'rating' => 5,
                'comment' => 'プログラミングを学び始めた人にも役立つ内容だと思います。',
            ],

            [
                'user_id' => $users[0]->id,
                'book_id' => $books[3]->id,
                'rating' => 4,
                'comment' => '日々の行動を見直すきっかけになる内容でした。',
            ],
            [
                'user_id' => $users[2]->id,
                'book_id' => $books[3]->id,
                'rating' => 5,
                'comment' => '人生や仕事に活かせる考え方が体系的にまとめられていて良かったです。',
            ],
            [
                'user_id' => $users[3]->id,
                'book_id' => $books[3]->id,
                'rating' => 4,
                'comment' => 'すぐに理解できる内容ばかりではないですが、何度も読む価値があると思います。',
            ],

            [
                'user_id' => $users[1]->id,
                'book_id' => $books[4]->id,
                'rating' => 4,
                'comment' => '主人公の真っすぐな性格が印象的で、テンポよく読めました。',
            ],
            [
                'user_id' => $users[3]->id,
                'book_id' => $books[4]->id,
                'rating' => 5,
                'comment' => '登場人物のやり取りが面白く、最後まで飽きずに楽しめました。',
            ],
            [
                'user_id' => $users[4]->id,
                'book_id' => $books[4]->id,
                'rating' => 4,
                'comment' => '短くまとまっていて読みやすく、夏目漱石らしい作品だと感じました。',
            ],

            [
                'user_id' => $users[0]->id,
                'book_id' => $books[5]->id,
                'rating' => 5,
                'comment' => '人類の歴史を大きな視点で捉えていて、とても興味深かったです。',
            ],
            [
                'user_id' => $users[2]->id,
                'book_id' => $books[5]->id,
                'rating' => 4,
                'comment' => '歴史と科学の両方の視点から読めるところが面白かったです。',
            ],
            [
                'user_id' => $users[4]->id,
                'book_id' => $books[5]->id,
                'rating' => 5,
                'comment' => '普段とは違う角度から人類の歩みを考えられる一冊でした。',
            ],

            [
                'user_id' => $users[1]->id,
                'book_id' => $books[6]->id,
                'rating' => 5,
                'comment' => '保守しやすいコードを書くための考え方が具体的に学べました。',
            ],
            [
                'user_id' => $users[2]->id,
                'book_id' => $books[6]->id,
                'rating' => 4,
                'comment' => '実際の開発で意識したいポイントが多く、勉強になりました。',
            ],
            [
                'user_id' => $users[3]->id,
                'book_id' => $books[6]->id,
                'rating' => 5,
                'comment' => 'コードの品質について改めて考えるきっかけになりました。',
            ],

            [
                'user_id' => $users[0]->id,
                'book_id' => $books[7]->id,
                'rating' => 4,
                'comment' => '対話形式なので読みやすく、アドラー心理学に興味を持てました。',
            ],
            [
                'user_id' => $users[3]->id,
                'book_id' => $books[7]->id,
                'rating' => 5,
                'comment' => '他人の評価を気にしすぎない考え方が印象に残りました。',
            ],
            [
                'user_id' => $users[4]->id,
                'book_id' => $books[7]->id,
                'rating' => 4,
                'comment' => '自分の考え方を見直すきっかけになる内容でした。',
            ],

            [
                'user_id' => $users[1]->id,
                'book_id' => $books[8]->id,
                'rating' => 5,
                'comment' => '芸人同士の関係や葛藤がリアルに描かれていて引き込まれました。',
            ],
            [
                'user_id' => $users[2]->id,
                'book_id' => $books[8]->id,
                'rating' => 4,
                'comment' => '夢を追うことの難しさや切なさが伝わってくる作品でした。',
            ],
            [
                'user_id' => $users[4]->id,
                'book_id' => $books[8]->id,
                'rating' => 4,
                'comment' => '独特の空気感があり、最後まで印象に残る小説でした。',
            ],

            [
                'user_id' => $users[0]->id,
                'book_id' => $books[9]->id,
                'rating' => 5,
                'comment' => '数字やデータをもとに世界を見る大切さがよく分かりました。',
            ],
            [
                'user_id' => $users[3]->id,
                'book_id' => $books[9]->id,
                'rating' => 5,
                'comment' => '思い込みに気づかされる内容が多く、とても勉強になりました。',
            ],
            [
                'user_id' => $users[4]->id,
                'book_id' => $books[9]->id,
                'rating' => 4,
                'comment' => '難しいテーマですが、具体例が多く読みやすかったです。',
            ],

            [
                'user_id' => $users[1]->id,
                'book_id' => $books[10]->id,
                'rating' => 4,
                'comment' => 'コンテナが世界の物流を大きく変えたことを知れて興味深かったです。',
            ],
            [
                'user_id' => $users[2]->id,
                'book_id' => $books[10]->id,
                'rating' => 5,
                'comment' => '物流と経済の歴史を知ることができ、想像以上に面白い内容でした。',
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}
