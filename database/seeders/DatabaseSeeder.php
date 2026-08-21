<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 管理ページ用の初期アカウント（メール: admin@example.com / パスワード: password）
        // updateOrCreateにより、再実行しても重複作成されない
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理者',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        // お問い合わせのダミーデータ（初回のみ・既にデータがあれば再投入しない）
        if (Contact::count() === 0) {
            $this->call(ContactSeeder::class);
        }
    }
}
