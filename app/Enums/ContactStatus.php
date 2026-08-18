<?php

namespace App\Enums;

// お問い合わせの対応状況
enum ContactStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';

    // 管理画面表示用の日本語ラベル
    public function label(): string
    {
        return match ($this) {
            self::New => '新規',
            self::InProgress => '対応中',
            self::Resolved => '解決済み',
        };
    }
}
