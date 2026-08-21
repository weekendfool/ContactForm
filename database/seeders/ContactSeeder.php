<?php

namespace Database\Seeders;

use App\Enums\ContactStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContactSeeder extends Seeder
{
    // 姓（バリエーション豊かに）
    private const SURNAMES = [
        '佐藤', '鈴木', '高橋', '田中', '伊藤', '渡辺', '山本', '中村', '小林', '加藤',
        '吉田', '山田', '佐々木', '山口', '松本', '井上', '木村', '林', '斎藤', '清水',
        '山崎', '池田', '橋本', '阿部', '石川', '前田', '藤田', '後藤', '岡田', '長谷川',
        '村上', '近藤', '石井', '斉藤', '坂本', '遠藤', '青木', '藤井', '西村', '福田',
    ];

    // 名（男性）
    private const MALE_GIVEN_NAMES = [
        '一郎', '太郎', '健太', '大輔', '翔太', '拓也', '直樹', '亮', '誠', '隆',
        '修', '剛', '淳', '悠斗', '蓮', '大和', '陽翔', '颯太', '信二', '和也',
        '雄大', '智也', '哲也', '賢一', '涼太', '慎太郎', '孝之', '達也', '光', '純一',
        '康平', '幸雄',
    ];

    // 名（女性）
    private const FEMALE_GIVEN_NAMES = [
        '花子', '由美', '恵子', '智子', '美咲', 'さくら', '陽子', '直美', '真央', '愛',
        '楓', '結衣', '美月', '香織', '千尋', '麻衣', '沙織', '亜美', '瞳', '彩',
        '菜々子', '優子', '真由美', '春香', '莉子', '奈々', '志保', '亜由美', '志穂', '美穂',
    ];

    // ショッピングサイトでよくある問い合わせ（件名・本文のペア）
    private const INQUIRIES = [
        [
            'subject' => '商品の返品について',
            'message' => "先日購入した商品ですが、サイズが合わなかったため返品したいです。\n手続き方法を教えてください。",
        ],
        [
            'subject' => 'サイズ交換をお願いしたい',
            'message' => "注文した商品のサイズを間違えてしまいました。\n1つ大きいサイズへの交換は可能でしょうか。",
        ],
        [
            'subject' => '注文のキャンセルについて',
            'message' => "先ほど注文した商品をキャンセルしたいのですが、まだ間に合いますでしょうか。\nご確認をお願いいたします。",
        ],
        [
            'subject' => '商品が届きません',
            'message' => "注文してから1週間以上経ちますが、商品がまだ届いておりません。\nお手数ですが配送状況をご確認いただけますでしょうか。",
        ],
        [
            'subject' => '配送状況の確認をお願いします',
            'message' => "配送予定日を過ぎているようなのですが、現在の状況を教えていただけますか。\nよろしくお願いいたします。",
        ],
        [
            'subject' => '届いた商品に不良がありました',
            'message' => "本日届いた商品を確認したところ、傷と汚れがありました。\n交換もしくは返金の対応をお願いできますでしょうか。",
        ],
        [
            'subject' => 'お支払い方法について',
            'message' => "注文時にクレジットカード以外の支払い方法を選びたいのですが、\nどのような決済方法に対応していますでしょうか。",
        ],
        [
            'subject' => 'クーポンが使用できません',
            'message' => "配布されたクーポンコードを入力しましたが、エラーが出て利用できませんでした。\n原因を確認していただけますか。",
        ],
        [
            'subject' => '会員登録ができません',
            'message' => "会員登録画面で登録ボタンを押してもエラーになってしまいます。\n何度か試しましたが解決しないため、ご確認をお願いします。",
        ],
        [
            'subject' => 'ログインできない',
            'message' => "パスワードを入力してもログインできない状態が続いています。\n再設定の方法を教えていただけますでしょうか。",
        ],
        [
            'subject' => '退会方法について',
            'message' => "会員を退会したいのですが、手続き方法が分かりませんでした。\nお手数ですがご案内をお願いいたします。",
        ],
        [
            'subject' => 'ポイントの有効期限について',
            'message' => "保有しているポイントの有効期限を教えてください。\n近日中に失効してしまうか心配しております。",
        ],
        [
            'subject' => '領収書の発行をお願いします',
            'message' => "先日の注文分の領収書を発行していただきたいです。\n宛名の指定方法も併せて教えてください。",
        ],
        [
            'subject' => '在庫状況について',
            'message' => "気になっている商品が在庫切れの表示になっています。\n再入荷の予定はございますでしょうか。",
        ],
        [
            'subject' => 'サイズ表記について質問があります',
            'message' => "商品ページのサイズ表記が分かりにくく、購入を迷っています。\n実際の寸法を教えていただけますか。",
        ],
        [
            'subject' => '注文内容の変更をお願いしたい',
            'message' => "先ほど注文した商品の配送先住所を変更したいです。\n発送前でしたら変更をお願いできますでしょうか。",
        ],
        [
            'subject' => '送料について教えてください',
            'message' => "購入を検討していますが、送料がいくらかかるのか分かりませんでした。\nまとめて注文した場合の送料も教えてください。",
        ],
        [
            'subject' => 'ギフトラッピングについて',
            'message' => "プレゼント用にギフトラッピングをお願いしたいのですが、\n対応は可能でしょうか。追加料金があれば教えてください。",
        ],
    ];

    /**
     * ショッピングサイトを想定したお問い合わせデータを100件投入する
     */
    public function run(): void
    {
        $rows = [];

        for ($i = 0; $i < 100; $i++) {
            $isMale = $i % 2 === 0;
            $surname = Arr::random(self::SURNAMES);
            $givenName = $isMale
                ? Arr::random(self::MALE_GIVEN_NAMES)
                : Arr::random(self::FEMALE_GIVEN_NAMES);

            $inquiry = Arr::random(self::INQUIRIES);

            // 過去3ヶ月（90日）以内のランダムな日時
            $createdAt = Carbon::now()
                ->subDays(random_int(0, 90))
                ->subSeconds(random_int(0, 86399));

            $rows[] = [
                'name' => "{$surname} {$givenName}",
                'email' => Str::lower(Str::random(12)).'@example.com',
                'subject' => $inquiry['subject'],
                'message' => $inquiry['message'],
                'status' => Arr::random(ContactStatus::cases())->value,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        // 男女の並び順が交互のままにならないようシャッフル
        shuffle($rows);

        DB::table('contacts')->insert($rows);
    }
}
