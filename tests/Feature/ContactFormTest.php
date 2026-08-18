<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_入力フォームが表示される(): void
    {
        $response = $this->get(route('contact.create'));

        $response->assertStatus(200);
    }

    public function test_妥当な入力で確認画面に入力内容が表示される(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'subject' => 'テスト件名',
            'message' => 'テスト本文です',
        ]);

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('yamada@example.com');
        $response->assertSee('テスト件名');
        $response->assertSee('テスト本文です');
    }

    public function test_不正な入力は確認画面に進めない(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'name' => '',
            'email' => 'invalid-email',
            'subject' => '',
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_確認画面から送信すると保存され完了画面へリダイレクトされる(): void
    {
        $input = [
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'subject' => 'テスト件名',
            'message' => 'テスト本文です',
        ];

        $response = $this->post(route('contact.store'), $input);

        $response->assertRedirect(route('contact.complete'));
        $this->assertDatabaseHas('contacts', [
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'subject' => 'テスト件名',
            'message' => 'テスト本文です',
            'status' => 'new',
        ]);
    }

    public function test_完了画面が表示される(): void
    {
        $this->withSession(['contact_submitted' => true])
            ->get(route('contact.complete'))
            ->assertStatus(200)
            ->assertSee('お問い合わせありがとうございました');
    }

    public function test_未送信で完了画面に直接アクセスすると入力画面へリダイレクトされる(): void
    {
        $response = $this->get(route('contact.complete'));

        $response->assertRedirect(route('contact.create'));
    }
}
