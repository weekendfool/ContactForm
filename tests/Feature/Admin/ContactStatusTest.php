<?php

namespace Tests\Feature\Admin;

use App\Enums\ContactStatus;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_一覧に登録済みのお問い合わせが表示される(): void
    {
        Contact::factory()->count(3)->create();

        $response = $this->get(route('admin.contacts.index'));

        $response->assertStatus(200);
    }

    public function test_詳細画面が表示される(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->get(route('admin.contacts.show', $contact));

        $response->assertStatus(200);
        $response->assertSee($contact->subject);
    }

    public function test_ステータスを変更できる(): void
    {
        $contact = Contact::factory()->create(['status' => ContactStatus::New]);

        $response = $this->patch(route('admin.contacts.update', $contact), [
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

        $response = $this->patch(route('admin.contacts.update', $contact), [
            'status' => 'unknown_status',
        ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'status' => 'new',
        ]);
    }
}
