<?php

namespace Tests\Feature\Admin;

use App\Enums\ContactStatus;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_一覧に登録済みのお問い合わせが表示される(): void
    {
        Contact::factory()->count(3)->create();

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index'));

        $response->assertStatus(200);
    }

    public function test_詳細画面が表示される(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.show', $contact));

        $response->assertStatus(200);
        $response->assertSee($contact->subject);
    }

    public function test_ステータスを変更できる(): void
    {
        $contact = Contact::factory()->create(['status' => ContactStatus::New]);

        $response = $this->actingAs(User::factory()->create())
            ->patch(route('admin.contacts.update', $contact), [
                'status' => ContactStatus::InProgress->value,
            ]);

        $response->assertRedirect(route('admin.contacts.show', $contact));
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_不正なステータス値は拒否される(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->actingAs(User::factory()->create())
            ->patch(route('admin.contacts.update', $contact), [
                'status' => 'unknown_status',
            ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'status' => 'new',
        ]);
    }

    public function test_未ログインでは一覧にアクセスできずログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('admin.contacts.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_対応状況で絞り込める(): void
    {
        Contact::factory()->create(['name' => '新規太郎', 'status' => ContactStatus::New]);
        Contact::factory()->create(['name' => '対応中花子', 'status' => ContactStatus::InProgress]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['status' => 'in_progress']));

        $response->assertStatus(200);
        $response->assertSee('対応中花子');
        $response->assertDontSee('新規太郎');
    }

    public function test_キーワードで名前を絞り込める(): void
    {
        Contact::factory()->create(['name' => '山田太郎']);
        Contact::factory()->create(['name' => '鈴木花子']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['keyword' => '山田']));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertDontSee('鈴木花子');
    }

    public function test_キーワードで件名を絞り込める(): void
    {
        Contact::factory()->create(['name' => '田中一郎', 'subject' => '返品について']);
        Contact::factory()->create(['name' => '佐藤次郎', 'subject' => '配送状況について']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['keyword' => '返品']));

        $response->assertStatus(200);
        $response->assertSee('田中一郎');
        $response->assertDontSee('佐藤次郎');
    }

    public function test_条件に一致しない場合は該当なしメッセージが表示される(): void
    {
        Contact::factory()->create(['name' => '山田太郎']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['keyword' => '存在しない名前']));

        $response->assertStatus(200);
        $response->assertSee('条件に一致するお問い合わせはありません。');
    }
}
