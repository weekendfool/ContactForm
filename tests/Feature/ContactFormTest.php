<?php

namespace Tests\Feature;

use App\Enums\ContactStatus;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// テストケース.md（フォーム入力側 + 管理側、全71項目）に対応するFeatureテスト。
// 各テストメソッド名の先頭の番号は、テストケース.mdの「No」列に対応する。
class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    // 日本語の全角文字をn文字分繰り返した文字列を作る（文字数境界値テスト用）
    private function ja(int $length): string
    {
        return str_repeat('あ', $length);
    }

    // ちょうど指定文字数になる、RFC的に妥当な形式のメールアドレスを作る（文字数境界値テスト用）
    private function emailOfLength(int $length): string
    {
        $prefix = 'a@';
        $suffix = '.com';
        $labelSize = 60;
        $left = $length - strlen($prefix) - strlen($suffix);
        $domain = '';
        while ($left > 0) {
            $len = min($labelSize, $left);
            $domain .= str_repeat('a', $len);
            $left -= $len;
            if ($left > 0) {
                $domain .= '.';
                $left--;
            }
        }

        return $prefix.$domain.$suffix;
    }

    private function validInput(array $overrides = []): array
    {
        return array_merge([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'subject' => 'テスト件名',
            'message' => 'テスト本文です',
        ], $overrides);
    }

    // ============================================================
    // 1.1 入力フォーム（GET /contact）
    // ============================================================

    public function test_1_1_画面表示(): void
    {
        $response = $this->get(route('contact.create'));

        $response->assertStatus(200);
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="subject"', false);
        $response->assertSee('name="message"', false);
    }

    public function test_1_2_初期状態は全て空欄(): void
    {
        $response = $this->get(route('contact.create'));

        $response->assertStatus(200);
        $response->assertDontSee('山田太郎');
    }

    // ============================================================
    // 1.2 入力バリデーション（POST /contact/confirm）
    // ============================================================

    public function test_2_1_全項目正常入力(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput());

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('yamada@example.com');
        $response->assertSee('テスト件名');
        $response->assertSee('テスト本文です');
    }

    public function test_2_2_お名前_未入力(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['name' => '']));

        $response->assertSessionHasErrors(['name']);
    }

    public function test_2_3_お名前_文字数上限ちょうど(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['name' => $this->ja(255)]));

        $response->assertStatus(200);
        $response->assertSessionHasNoErrors();
    }

    public function test_2_4_お名前_文字数超過(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['name' => $this->ja(256)]));

        $response->assertSessionHasErrors(['name']);
    }

    public function test_2_5_メールアドレス_未入力(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['email' => '']));

        $response->assertSessionHasErrors(['email']);
    }

    public function test_2_6_メールアドレス_形式不正(): void
    {
        foreach (['test', 'test@', '@example.com'] as $invalidEmail) {
            $response = $this->post(route('contact.confirm'), $this->validInput(['email' => $invalidEmail]));

            $response->assertSessionHasErrors(['email']);
        }
    }

    public function test_2_7_メールアドレス_記号を含む正しい形式(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['email' => 'user+tag@example.com']));

        $response->assertStatus(200);
        $response->assertSessionHasNoErrors();
    }

    public function test_2_8_メールアドレス_文字数上限ちょうど(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['email' => $this->emailOfLength(255)]));

        $response->assertStatus(200);
        $response->assertSessionHasNoErrors();
    }

    public function test_2_9_メールアドレス_文字数超過(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['email' => $this->emailOfLength(256)]));

        $response->assertSessionHasErrors(['email']);
    }

    public function test_2_10_件名_未入力(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['subject' => '']));

        $response->assertSessionHasErrors(['subject']);
    }

    public function test_2_11_件名_文字数上限ちょうど(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['subject' => $this->ja(255)]));

        $response->assertStatus(200);
        $response->assertSessionHasNoErrors();
    }

    public function test_2_12_件名_文字数超過(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['subject' => $this->ja(256)]));

        $response->assertSessionHasErrors(['subject']);
    }

    public function test_2_13_お問い合わせ内容_未入力(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['message' => '']));

        $response->assertSessionHasErrors(['message']);
    }

    public function test_2_14_お問い合わせ内容_文字数上限ちょうど(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['message' => $this->ja(2000)]));

        $response->assertStatus(200);
        $response->assertSessionHasNoErrors();
    }

    public function test_2_15_お問い合わせ内容_文字数超過(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput(['message' => $this->ja(2001)]));

        $response->assertSessionHasErrors(['message']);
    }

    // エッジケース: 空白のみの入力は現状requiredのみでは弾けず通過してしまう（仕様上の既知の留意点）
    // Laravel標準のTrimStringsミドルウェアが全角スペースも含めてトリムするため、
    // 空白のみの入力はrequiredバリデーションで正しく弾かれる
    public function test_2_16_空白のみ入力は正しく弾かれる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'name' => '　',
            'email' => 'yamada@example.com',
            'subject' => '　',
            'message' => '　',
        ]);

        $response->assertSessionHasErrors(['name', 'subject', 'message']);
    }

    public function test_2_17_複数項目同時エラー(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'name' => '',
            'email' => '',
            'subject' => '',
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_2_18_日本語エラーメッセージ(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'name' => '',
            'email' => '',
            'subject' => '',
            'message' => '',
        ]);

        $response->assertInvalid([
            'name' => 'お名前',
            'email' => 'メールアドレス',
            'subject' => '件名',
            'message' => 'お問い合わせ内容',
        ]);
    }

    // エッジケース: スクリプトタグ入力がエスケープされ実行されないこと（XSS対策の確認）
    public function test_2_19_HTMLスクリプトタグ入力がエスケープされる(): void
    {
        $payload = '<script>alert(1)</script>';

        $confirmResponse = $this->post(route('contact.confirm'), $this->validInput([
            'name' => $payload,
            'subject' => $payload,
        ]));
        $confirmResponse->assertStatus(200);
        $confirmResponse->assertDontSee($payload, false);
        $confirmResponse->assertSee(e($payload), false);

        $contact = Contact::factory()->create(['name' => $payload, 'subject' => $payload]);
        $user = User::factory()->create();

        $indexResponse = $this->actingAs($user)->get(route('admin.contacts.index'));
        $indexResponse->assertDontSee($payload, false);
        $indexResponse->assertSee(e($payload), false);

        $showResponse = $this->actingAs($user)->get(route('admin.contacts.show', $contact));
        $showResponse->assertDontSee($payload, false);
        $showResponse->assertSee(e($payload), false);
    }

    public function test_2_20_改行を含む入力(): void
    {
        $multiline = "1行目\n2行目\n3行目";

        $response = $this->post(route('contact.confirm'), $this->validInput(['message' => $multiline]));

        $response->assertStatus(200);
        $response->assertSee($multiline, false);
    }

    // ============================================================
    // 1.3 確認画面（表示後の操作）
    // ============================================================

    public function test_3_1_入力内容の表示(): void
    {
        $input = $this->validInput();

        $response = $this->post(route('contact.confirm'), $input);

        $response->assertSee($input['name']);
        $response->assertSee($input['email']);
        $response->assertSee($input['subject']);
        $response->assertSee($input['message']);
    }

    // エッジケース: 「修正する」リンクは入力フォームへ戻るのみで、入力値は復元されない
    public function test_3_2_修正するリンクでは入力値が復元されない(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validInput());
        $response->assertSee(route('contact.create'), false);

        $createResponse = $this->get(route('contact.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertDontSee('山田太郎');
    }

    public function test_3_3_確認画面への直接アクセス(): void
    {
        $response = $this->get(route('contact.confirm'));

        $response->assertStatus(405);
    }

    // エッジケース: 二重送信対策が無いため、2回送信すると2件登録されてしまう（現状の既知の留意点）
    public function test_3_4_確認画面の再送信で二重登録される(): void
    {
        $input = $this->validInput();

        $this->post(route('contact.store'), $input);
        $this->post(route('contact.store'), $input);

        $this->assertDatabaseCount('contacts', 2);
    }

    // ============================================================
    // 1.4 送信・完了画面
    // ============================================================

    public function test_4_1_正常送信(): void
    {
        $input = $this->validInput();

        $response = $this->post(route('contact.store'), $input);

        $response->assertRedirect(route('contact.complete'));
        $this->assertDatabaseHas('contacts', [
            'name' => $input['name'],
            'email' => $input['email'],
            'subject' => $input['subject'],
            'message' => $input['message'],
        ]);
    }

    public function test_4_2_完了メッセージ(): void
    {
        $this->post(route('contact.store'), $this->validInput());

        $response = $this->get(route('contact.complete'));

        $response->assertStatus(200);
        $response->assertSee('お問い合わせありがとうございました');
    }

    public function test_4_3_完了画面への直接アクセス(): void
    {
        $response = $this->get(route('contact.complete'));

        $response->assertRedirect(route('contact.create'));
    }

    public function test_4_4_完了画面の再読み込み(): void
    {
        $this->post(route('contact.store'), $this->validInput());

        $this->get(route('contact.complete'))->assertStatus(200);
        $this->get(route('contact.complete'))->assertRedirect(route('contact.create'));
    }

    // CSRFトークン検証は、Laravel標準の仕組みによりユニットテスト実行時は自動的に無効化されるため
    // （PreventRequestForgery::runningUnitTests()）、Featureテストでは419エラーを再現できない。
    // 実際の動作確認はブラウザ操作またはcurl等の実HTTPリクエストで行う必要がある。
    public function test_4_5_CSRFトークン欠如(): void
    {
        $this->markTestSkipped('CSRF検証はLaravelの仕様によりユニットテスト実行中は無効化されるため、Featureテストでは再現できない。');
    }

    public function test_4_6_ステータス初期値(): void
    {
        $this->post(route('contact.store'), $this->validInput());

        $contact = Contact::latest()->first();

        $this->assertSame(ContactStatus::New, $contact->status);
    }

    // ============================================================
    // 2.1 ログイン（/login）
    // ============================================================

    public function test_5_1_ログイン画面表示(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
    }

    public function test_5_2_正常ログイン(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_5_3_メールアドレス誤り(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'notfound@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_5_4_パスワード誤り(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_5_5_未入力送信(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_5_6_ログイン中にloginへアクセス(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect('/');
    }

    public function test_5_7_intended遷移(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $contact = Contact::factory()->create();

        $this->get(route('admin.contacts.show', $contact))
            ->assertRedirect(route('login'));

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.contacts.show', $contact));
    }

    // エッジケース: レート制限が無いため、何度失敗しても正しい認証情報なら最終的にログインできてしまう
    public function test_5_8_ブルートフォース試行にレート制限が無い(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertSessionHasErrors(['email']);
        }

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertAuthenticatedAs($user);
    }

    // ============================================================
    // 2.2 認証・アクセス制御
    // ============================================================

    public function test_6_1_未ログインで一覧アクセス(): void
    {
        $response = $this->get(route('admin.contacts.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_6_2_未ログインで詳細アクセス(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->get(route('admin.contacts.show', $contact));

        $response->assertRedirect(route('login'));
    }

    public function test_6_3_未ログインでステータス更新(): void
    {
        $contact = Contact::factory()->create(['status' => ContactStatus::New]);

        $response = $this->patch(route('admin.contacts.update', $contact), [
            'status' => ContactStatus::InProgress->value,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'status' => 'new']);
    }

    public function test_6_4_ログアウト(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_6_5_ログアウト後のアクセス(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'));
        $response = $this->get(route('admin.contacts.index'));

        $response->assertRedirect(route('login'));
    }

    // エッジケース: ロール区分が無いため、ログインさえできれば誰でも管理機能を操作できる
    public function test_6_6_権限区分は無くログインできれば操作できる(): void
    {
        $anyUser = User::factory()->create();

        $response = $this->actingAs($anyUser)->get(route('admin.contacts.index'));

        $response->assertStatus(200);
    }

    // ============================================================
    // 2.3 一覧表示（/admin/contacts）
    // ============================================================

    public function test_7_1_一覧表示(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.contacts.index'));

        $response->assertStatus(200);
        $response->assertSee($contact->name);
        $response->assertSee($contact->subject);
        $response->assertSee($contact->status->label());
    }

    public function test_7_2_並び順は新しい順(): void
    {
        $older = Contact::factory()->create(['name' => '古い太郎']);
        $older->forceFill(['created_at' => now()->subDays(2)])->save();

        $newer = Contact::factory()->create(['name' => '新しい花子']);
        $newer->forceFill(['created_at' => now()])->save();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.contacts.index'));

        $content = $response->getContent();
        $this->assertTrue(strpos($content, '新しい花子') < strpos($content, '古い太郎'));
    }

    public function test_7_3_ページネーション(): void
    {
        Contact::factory()->count(25)->create();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.contacts.index'));

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->total() === 25 && $contacts->count() === 20;
        });
        $response->assertSee('page=2', false);
    }

    public function test_7_4_データ0件時の表示(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('admin.contacts.index'));

        $response->assertStatus(200);
        $response->assertSee('お問い合わせはまだありません。');
    }

    public function test_7_5_詳細へのリンク(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.contacts.index'));

        $response->assertSee(route('admin.contacts.show', $contact), false);
    }

    public function test_7_6_存在しないページ番号(): void
    {
        Contact::factory()->count(3)->create();

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['page' => 9999]));

        $response->assertStatus(200);
    }

    // ============================================================
    // 2.4 絞り込み機能
    // ============================================================

    public function test_8_1_ステータス絞り込み(): void
    {
        Contact::factory()->create(['name' => '新規太郎', 'status' => ContactStatus::New]);
        Contact::factory()->create(['name' => '対応中花子', 'status' => ContactStatus::InProgress]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['status' => 'in_progress']));

        $response->assertSee('対応中花子');
        $response->assertDontSee('新規太郎');
    }

    public function test_8_2_キーワード絞り込みお名前(): void
    {
        Contact::factory()->create(['name' => '山田太郎']);
        Contact::factory()->create(['name' => '鈴木花子']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['keyword' => '山田']));

        $response->assertSee('山田太郎');
        $response->assertDontSee('鈴木花子');
    }

    public function test_8_3_キーワード絞り込み件名(): void
    {
        Contact::factory()->create(['name' => '田中一郎', 'subject' => '返品について']);
        Contact::factory()->create(['name' => '佐藤次郎', 'subject' => '配送状況について']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['keyword' => '返品']));

        $response->assertSee('田中一郎');
        $response->assertDontSee('佐藤次郎');
    }

    public function test_8_4_AND条件(): void
    {
        Contact::factory()->create(['name' => '山田太郎', 'status' => ContactStatus::New]);
        Contact::factory()->create(['name' => '山田花子', 'status' => ContactStatus::Resolved]);
        Contact::factory()->create(['name' => '鈴木一郎', 'status' => ContactStatus::New]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['status' => 'new', 'keyword' => '山田']));

        $response->assertSee('山田太郎');
        $response->assertDontSee('山田花子');
        $response->assertDontSee('鈴木一郎');
    }

    public function test_8_5_該当なし(): void
    {
        Contact::factory()->create(['name' => '山田太郎']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['keyword' => '存在しない名前']));

        $response->assertSee('条件に一致するお問い合わせはありません。');
    }

    public function test_8_6_クリア(): void
    {
        Contact::factory()->create(['name' => '山田太郎']);
        Contact::factory()->create(['name' => '鈴木花子']);

        $response = $this->actingAs(User::factory()->create())->get(route('admin.contacts.index'));

        $response->assertSee('山田太郎');
        $response->assertSee('鈴木花子');
    }

    public function test_8_7_ページネーションとの併用(): void
    {
        Contact::factory()->count(25)->create(['status' => ContactStatus::New]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['status' => 'new']));

        $response->assertSee('status=new', false);
        $response->assertSee('page=2', false);
    }

    // エッジケース: statusにenumで定義されていない値を渡してもエラーにならず0件になる
    public function test_8_8_不正なステータス値(): void
    {
        Contact::factory()->create(['status' => ContactStatus::New]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['status' => 'unknown']));

        $response->assertStatus(200);
        $response->assertSee('条件に一致するお問い合わせはありません。');
    }

    // エッジケース: LIKE検索の特殊文字「%」を入力すると意図せず広範囲に一致してしまう
    public function test_8_9_キーワードの特殊文字(): void
    {
        Contact::factory()->create(['name' => '山田太郎']);
        Contact::factory()->create(['name' => '鈴木花子']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['keyword' => '%']));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('鈴木花子');
    }

    public function test_8_10_キーワードにSQLインジェクション文字列(): void
    {
        Contact::factory()->create(['name' => '山田太郎']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.contacts.index', ['keyword' => "' OR '1'='1"]));

        $response->assertStatus(200);
        $response->assertSee('条件に一致するお問い合わせはありません。');
    }

    // ============================================================
    // 2.5 詳細表示・ステータス変更（/admin/contacts/{id}）
    // ============================================================

    public function test_9_1_詳細表示(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.contacts.show', $contact));

        $response->assertStatus(200);
        $response->assertSee($contact->name);
        $response->assertSee($contact->email);
        $response->assertSee($contact->subject);
        $response->assertSee($contact->message);
    }

    public function test_9_2_存在しないIDへのアクセス(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/admin/contacts/99999');

        $response->assertStatus(404);
    }

    public function test_9_3_不正な形式のID(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/admin/contacts/abc');

        $response->assertStatus(404);
    }

    public function test_9_4_ステータス変更_新規から対応中(): void
    {
        $contact = Contact::factory()->create(['status' => ContactStatus::New]);

        $response = $this->actingAs(User::factory()->create())
            ->patch(route('admin.contacts.update', $contact), ['status' => ContactStatus::InProgress->value]);

        $response->assertRedirect(route('admin.contacts.show', $contact));
        $response->assertSessionHas('status_message', '対応状況を更新しました');
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'status' => 'in_progress']);
    }

    public function test_9_5_ステータス変更_対応中から解決済み(): void
    {
        $contact = Contact::factory()->create(['status' => ContactStatus::InProgress]);

        $response = $this->actingAs(User::factory()->create())
            ->patch(route('admin.contacts.update', $contact), ['status' => ContactStatus::Resolved->value]);

        $response->assertRedirect(route('admin.contacts.show', $contact));
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'status' => 'resolved']);
    }

    public function test_9_6_同じステータスに更新(): void
    {
        $contact = Contact::factory()->create(['status' => ContactStatus::New]);

        $response = $this->actingAs(User::factory()->create())
            ->patch(route('admin.contacts.update', $contact), ['status' => ContactStatus::New->value]);

        $response->assertRedirect(route('admin.contacts.show', $contact));
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'status' => 'new']);
    }

    public function test_9_7_不正なステータス値を直接送信(): void
    {
        $contact = Contact::factory()->create(['status' => ContactStatus::New]);

        $response = $this->actingAs(User::factory()->create())
            ->patch(route('admin.contacts.update', $contact), ['status' => 'invalid']);

        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'status' => 'new']);
    }

    public function test_9_8_statusパラメータ未送信(): void
    {
        $contact = Contact::factory()->create(['status' => ContactStatus::New]);

        $response = $this->actingAs(User::factory()->create())
            ->patch(route('admin.contacts.update', $contact), []);

        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'status' => 'new']);
    }

    public function test_9_9_更新後の一覧反映(): void
    {
        $contact = Contact::factory()->create(['status' => ContactStatus::New]);
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('admin.contacts.update', $contact), [
            'status' => ContactStatus::Resolved->value,
        ]);

        $response = $this->actingAs($user)->get(route('admin.contacts.index'));

        $response->assertSee($contact->name);
        $response->assertSee(ContactStatus::Resolved->label());
    }
}
