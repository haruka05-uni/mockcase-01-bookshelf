<?php

namespace Tests\Feature\Advanced;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    // ----- 認証ユーザーが計画を作成出来る。 -----
    public function test_authenticated_user_can_create_reading_plan(): void
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

        $this->actingAs($user);

        $targetDate = today()->addWeek()->toDateString();

        $readingPlanData = [
            'book_id' => $book->id,
            'target_date' => $targetDate,
        ];

        $response = $this->post('/reading-plans', $readingPlanData);
        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
        ]);
    }

    // ----- 認証ユーザーが計画を編集出来る。 -----
    public function test_owner_can_update_reading_plan(): void
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

        $this->actingAs($user);

        $targetDate = today()->addWeek()->toDateString();
        $updatedTargetDate = today()->addDays(3)->toDateString();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
        ]);

        $updatedReadingPlanData = [
            'book_id' => $book->id,
            'target_date' => $updatedTargetDate,
        ];

        $response = $this->put('/reading-plans/'.$readingPlan->id, $updatedReadingPlanData);
        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $updatedTargetDate,
        ]);
    }

    // ----- 認証ユーザーが計画と通知を削除出来る。 -----
    public function test_owner_can_delete_reading_plan_and_related_notifications(): void
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

        $this->actingAs($user);

        $targetDate = today()->addWeek()->toDateString();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
        ]);

        $user->notify(
            new ReadingPlanReminder($readingPlan, 'three_days_before')
        );

        $notification = $user->notifications()->first();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
        ]);

        $response = $this->delete('/reading-plans/'.$readingPlan->id);
        $response->assertRedirect('/reading-plans');

        // 計画削除
        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);

        // 通知削除
        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }

    // ----- 他ユーザーによる編集は403 -----
    public function test_non_owner_cannot_update_reading_plan(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $anotherUser = User::create([
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
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

        $this->actingAs($anotherUser);

        $targetDate = today()->addWeek()->toDateString();
        $updatedTargetDate = today()->addDays(3)->toDateString();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
        ]);

        $updatedReadingPlanData = [
            'book_id' => $book->id,
            'target_date' => $updatedTargetDate,
        ];

        $response = $this->put('/reading-plans/'.$readingPlan->id, $updatedReadingPlanData);
        $response->assertStatus(403);
    }

    // ----- 他ユーザーによる削除は403 -----
    public function test_non_owner_cannot_delete_reading_plan(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $anotherUser = User::create([
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
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

        $this->actingAs($anotherUser);

        $targetDate = today()->addWeek()->toDateString();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
        ]);

        $response = $this->delete('/reading-plans/'.$readingPlan->id);
        $response->assertStatus(403);

    }

    // ----- 「読了する」操作 -----
    public function test_owner_can_complete_reading_plan(): void
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

        $this->actingAs($user);

        $targetDate = today()->addWeek()->toDateString();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
        ]);

        $response = $this->post('/reading-plans/'.$readingPlan->id.'/complete');
        $response->assertRedirect('/reading-plans');

        // status が Completed に更新され completed_at がセットされている。
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => 'completed',
            'completed_at' => today()->toDateTimeString(),
        ]);

    }

    // ----- Expired計画の期限変更 -----
    public function test_updating_expired_reading_plan_changes_status_to_in_progress(): void
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

        $this->actingAs($user);

        $targetDate = today()->subWeek()->toDateString();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
            'status' => ReadingPlanStatus::Expired,
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Expired->value,
        ]);

        $updatedTargetDate = today()->addWeek()->toDateString();

        $updatedReadingPlanData = [
            'book_id' => $book->id,
            'target_date' => $updatedTargetDate,
        ];

        $response = $this->put('/reading-plans/'.$readingPlan->id, $updatedReadingPlanData);
        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::InProgress->value,
            'target_date' => $updatedTargetDate,
        ]);

    }

    // ----- Completed計画の編集アクセスは403 -----
    public function test_completed_reading_plan_cannot_be_edited(): void
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

        $this->actingAs($user);

        $targetDate = today()->subWeek()->toDateString();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->get('/reading-plans/'.$readingPlan->id.'/edit');
        $response->assertStatus(403);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Completed->value,
            'target_date' => $targetDate,
        ]);

    }
}
