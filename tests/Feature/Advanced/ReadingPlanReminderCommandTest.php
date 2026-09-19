<?php

namespace Tests\Feature\Advanced;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;
use App\Enums\ReadingPlanStatus;
use Carbon\Carbon;

class ReadingPlanReminderCommandTest extends TestCase
{
    use RefreshDatabase;
    //時刻固定
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-09-18 09:00:00');
    }

    //時刻解除
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // ----- 期限3日前 -----
    public function test_reminder_is_sent_three_days_before_due_date(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $targetDate = today()->addDays(3)->toDateString();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $notification = $user->notifications()->first();

        $this->assertNotNull($notification);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => ReadingPlanReminder::class,
        ]);

        $this->assertSame(
            $readingPlan->id,
            $notification->data['reading_plan_id']
        );

        $this->assertSame(
            '「' . $book->title . '」の読書期限まであと3日です。',
            $notification->data['body']
        );

    }

    // ----- 期限当日 -----
    public function test_reminder_is_sent_on_due_date(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $targetDate = today()->toDateString();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $notification = $user->notifications()->first();

        $this->assertNotNull($notification);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => ReadingPlanReminder::class,
        ]);

        $this->assertSame(
            $readingPlan->id,
            $notification->data['reading_plan_id']
        );

        $this->assertSame(
            '「' . $book->title . '」の読書期限は今日です。',
            $notification->data['body']
        );

    }

    // ----- 期限切れ後3日 -----
    public function test_reminder_is_sent_three_days_after_due_date(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $targetDate = today()->subDays(3)->toDateString();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
            'status' => ReadingPlanStatus::Expired,
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $notification = $user->notifications()->first();

        $this->assertNotNull($notification);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => ReadingPlanReminder::class,
        ]);

        $this->assertSame(
            $readingPlan->id,
            $notification->data['reading_plan_id']
        );

        $this->assertSame(
            '「' . $book->title . '」の読書期限から3日経過しています。',
            $notification->data['body']
        );

    }

    // ----- 対象外には通知されない -----
    public function test_reminder_is_not_sent_for_non_target_plans(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        $twoDaysAgo = today()->subDays(2)->toDateString();
        $fourDaysAgo = today()->subDays(4)->toDateString();
        $twoDaysLater = today()->addDays(2)->toDateString();
        $fourDaysLater = today()->addDays(4)->toDateString();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $twoDaysAgo,
            'status' => ReadingPlanStatus::Expired,
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $fourDaysAgo,
            'status' => ReadingPlanStatus::Expired,
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $twoDaysLater,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $fourDaysLater,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(3)->toDateString(),
            'status' => ReadingPlanStatus::Completed,
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $this->assertDatabaseCount('notifications', 0);
    }

    // ----- 一括 Expired 化 -----
    public function test_only_overdue_in_progress_plans_are_updated_to_expired(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から人間社会を風刺的に描いた夏目漱石の代表的な小説。',
        ]);

        // 昨日が期限
        $dueYesterday = today()->subDay()->toDateString();

        $expiredTargetPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $dueYesterday,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        // 明日が期限
        $duetomorrow = today()->addDay()->toDateString();

        $futureTargetPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $duetomorrow,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        // 昨日が期限の計画は expired に変わる
        $this->assertDatabaseHas('reading_plans', [
            'id' => $expiredTargetPlan->id,
            'status' => ReadingPlanStatus::Expired->value,
        ]);

        // 明日が期限の計画は in_progress のまま
        $this->assertDatabaseHas('reading_plans', [
            'id' => $futureTargetPlan->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

}
