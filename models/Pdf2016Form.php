<?php

declare(strict_types=1);

namespace app\models;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Yii;
use app\validators\MyNumberValidator;
use jp3cki\mynumber\MyNumber;
use yii\base\Model;

/**
 * @property-read ?Prefecturer $prefecturer
 */
final class Pdf2016Form extends Model
{
    public const SEX_MALE   = '1';
    public const SEX_FEMALE = '2';

    // 投函年月日
    /** @var string|int */
    public $post_year;
    /** @var string|int */
    public $post_month;
    /** @var string|int */
    public $post_day;

    // 自治体名（"長"なし）
    /** @var string */
    public $local_gov;

    // 寄付年月日
    /** @var string|int */
    public $kifu_year;
    /** @var string|int */
    public $kifu_month;
    /** @var string|int */
    public $kifu_day;

    // 寄付金額
    /** @var string|int */
    public $kifu_amount;

    // 寄付者情報
    /** @var string */
    public $zipcode;
    /** @var string|int */
    public $pref_id;
    /** @var string */
    public $city;
    /** @var string */
    public $address1;
    /** @var string */
    public $address2;
    /** @var string */
    public $phone;
    /** @var string */
    public $name;
    /** @var string */
    public $name_kana;
    /** @var string */
    public $sign = '0';
    /** @var string */
    public $sex;
    /** @var string|int */
    public $birth_year;
    /** @var string|int */
    public $birth_month;
    /** @var string|int */
    public $birth_day;
    /** @var string */
    public $individual_number; // マイナンバー
    // 特例にかかわるチェックボックス
    /** @var string */
    public $checkbox1;
    /** @var string */
    public $checkbox2;

    /** @return void */
    public function init()
    {
        parent::init();

        $date = (new DateTimeImmutable(sprintf('@%d', $_SERVER['REQUEST_TIME'] ?? time())))
            ->setTimezone(new DateTimeZone('Asia/Tokyo'));

        if ($this->post_year === null && $this->post_month === null && $this->post_day === null) {
            $this->post_year = (int)$date->format('Y');
            $this->post_month = (int)$date->format('n');
            $this->post_day = (int)$date->format('j');
        }
        if ($this->kifu_year === null && $this->kifu_month === null && $this->kifu_day === null) {
            $this->kifu_year = (int)$date->format('Y');
            $this->kifu_month = (int)$date->format('n');
            $this->kifu_day = (int)$date->format('j');
        }
        if (
            $this->birth_year === null &&
            $this->birth_month === null &&
            $this->birth_day === null
        ) {
            $this->birth_year = 1980;
            $this->birth_month = 1;
            $this->birth_day = 1;
        }
    }

    public function rules()
    {
        $allAttrs = [
            'address1',
            'address2',
            'birth_day',
            'birth_month',
            'birth_year',
            'checkbox1',
            'checkbox2',
            'city',
            'individual_number',
            'kifu_amount',
            'kifu_day',
            'kifu_month',
            'kifu_year',
            'local_gov',
            'name',
            'name_kana',
            'phone',
            'post_day',
            'post_month',
            'post_year',
            'pref_id',
            'sex',
            'sign',
            'zipcode',
        ];
        $trimAttrs = array_filter($allAttrs, function (string $v): bool {
            return $v !== 'checkbox1' &&
                $v !== 'checkbox2' &&
                $v !== 'sign';
        });
        $requiredAttrs = array_filter($allAttrs, function (string $v): bool {
            return $v !== 'address2' &&
                $v !== 'individual_number' &&
                $v !== 'sign';
        });
        return [
            [$trimAttrs, 'trim'],
            [$requiredAttrs, 'required'],
            [['post_year', 'kifu_year', 'birth_year'], 'integer', 'min' => 1],
            [['post_month', 'kifu_month', 'birth_month'], 'integer', 'min' => 1, 'max' => 12],
            [['post_day', 'kifu_day', 'birth_day'], 'integer', 'min' => 1, 'max' => 31],
            [['kifu_amount'], 'integer', 'min' => 1],
            [['local_gov', 'city', 'address1', 'address2', 'name', 'name_kana'], 'string'],
            [['name_kana'], 'filter', 'filter' => fn($v) => mb_convert_kana($v, 'asKCV', 'UTF-8')],
            [['zipcode'], 'match', 'pattern' => '/^\d{7}$/'],
            [['pref_id'], 'exist',
                'skipOnError' => true,
                'targetClass' => Prefecturer::class,
                'targetAttribute' => ['pref_id' => 'id']],
            [['phone'], 'match', 'pattern' => '/^0[0-9\-]+$/'],
            [['sex'], 'in',
                'range' => [
                    static::SEX_MALE,
                    static::SEX_FEMALE
                ],
            ],
            [['checkbox1', 'checkbox2'], 'in',
                'range' => ['1'],
                'message' => 'この項目は必ずチェックが必要です。',
            ],
            [['sign'], 'boolean'],
            [['individual_number'], 'match', 'pattern' => '/^\d{12}$/'],
            [['individual_number'], MyNumberValidator::class],
        ];
    }

    /** @codeCoverageIgnore */
    public function attributeLabels()
    {
        return [
            'address1' => '住所(1)',
            'address2' => '住所(2)',
            'birth_day' => '誕生日（日）',
            'birth_month' => '誕生日（月）',
            'birth_year' => '誕生日（年）',
            'checkbox1' => '地方税法附則第７条第１項（第８項）に規定する申告特例対象寄附者である',
            'checkbox2' => '地方税法附則第７条第２項（第９項）に規定する要件に該当する者である',
            'city' => '市区町村',
            'individual_number' => 'マイナンバー（個人番号）',
            'kifu_amount' => '寄付金額',
            'kifu_day' => '寄付日（日）',
            'kifu_month' => '寄付日（月）',
            'kifu_year' => '寄付日（年）',
            'local_gov' => '寄付先自治体名',
            'name' => '名前（漢字）',
            'name_kana' => '名前（カナ）',
            'phone' => '電話番号',
            'post_day' => '投函予定日（日）',
            'post_month' => '投函予定日（月）',
            'post_year' => '投函予定日（年）',
            'pref_id' => '都道府県',
            'sex' => '性別',
            'sign' => '名前を自署するため、空欄で出力する',
            'zipcode' => '郵便番号',
        ];
    }

    public function createPdf(): string
    {
        $post = (new DateTimeImmutable('now', new DateTimeZone('Asia/Tokyo')))
            ->setDate((int)$this->post_year, (int)$this->post_month, (int)$this->post_day)
            ->setTime(0, 0, 0);
        $birthday = (new DateTimeImmutable('now', new DateTimeZone('Asia/Tokyo')))
            ->setDate((int)$this->birth_year, (int)$this->birth_month, (int)$this->birth_day)
            ->setTime(0, 0, 0);
        $kifu = (new DateTimeImmutable('now', new DateTimeZone('Asia/Tokyo')))
            ->setDate((int)$this->kifu_year, (int)$this->kifu_month, (int)$this->kifu_day)
            ->setTime(0, 0, 0);

        $pdf = Yii::createObject(Pdf::class)
            ->setEnvelope($post, $this->local_gov)
            ->setAddress(
                $this->zipcode,
                $this->prefecturer,
                $this->city,
                $this->address1,
                $this->address2
            )
            ->setPhone($this->phone)
            ->setName($this->name, $this->name_kana, $this->sign === '1')
            ->setIndividualNumber($this->individual_number)
            ->setSex($this->sex === static::SEX_MALE)
            ->setBirthday($birthday)
            ->setKifuData($kifu, (int)$this->kifu_amount);

        return $pdf->binary;
    }

    public function getPrefecturer(): ?Prefecturer
    {
        return Prefecturer::findOne(['id' => $this->pref_id]);
    }

    public function faker(): self
    {
        $today = new DateTimeImmutable('now', new DateTimeZone(Yii::$app->timeZone));
        $yesterday = $today->sub(new DateInterval('P1D'));
        $birthday = (new DateTimeImmutable('now', new DateTimeZone(Yii::$app->timeZone)))
            ->setTimestamp(mt_rand(
                (int)floor(time() - 55 * 365.2425 * 86400),
                (int)ceil(time() - 23 * 365.2425 * 86400)
            ));

        $this->attributes = [
            'post_year' => (int)$today->format('Y'),
            'post_month' => (int)$today->format('n'),
            'post_day' => (int)$today->format('j'),
            'local_gov' => '寝屋川市',
            'kifu_year' => (int)$yesterday->format('Y'),
            'kifu_month' => (int)$yesterday->format('n'),
            'kifu_day' => (int)$yesterday->format('j'),
            'kifu_amount' => mt_rand(5, 50) * 1000,
            'zipcode' => '1000001',
            'pref_id' => 13,
            'city' => '千代田区',
            'address1' => '千代田1-1',
            'address2' => 'パレス皇居404',
            'phone' => '090-1234-5678',
            'name' => '相沢　陽菜',
            'name_kana' => 'アイザワ　ヒナ',
            'sex' => '2',
            'birth_year' => (int)$birthday->format('Y'),
            'birth_month' => (int)$birthday->format('n'),
            'birth_day' => (int)$birthday->format('j'),
            'individual_number' => MyNumber::generate(),
            'checkbox1' => '1',
            'checkbox2' => '1',
        ];

        return $this;
    }
}
