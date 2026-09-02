<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    //認証済みリダイレクト
    //認証済みユーザーがログインページにアクセスした場合、ホームにリダイレクトされること。
    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/login');
        $response->assertRedirect('/books');
    }

    //認証済みリダイレクト
    //ゲストはアクセス可能であること。
    public function test_guest_can_access_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }
}
