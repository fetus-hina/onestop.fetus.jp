<?php

declare(strict_types=1);

namespace app\models;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Yii;
use app\validators\MyNumberValidator;
use jp3cki\gimei\Gimei;
use jp3cki\mynumber\MyNumber;
use yii\base\Model;

use function array_filter;
use function array_merge;
use function ceil;
use function floor;
use function mb_convert_kana;
use function mb_strlen;
use function mt_rand;
use function preg_match;
use function random_int;
use function sprintf;
use function time;
use function vsprintf;

/**
 * @property-read ?Prefecturer $prefecturer
 */
final class Pdf2016Form extends Model
{
    public const SEX_MALE = '1';
    public const SEX_FEMALE = '2';

    // 投函年月日
    public string|int|null $post_year = null;
    public string|int|null $post_month = null;
    public string|int|null $post_day = null;

    // 自治体名（"長"なし）
    public string|null $local_gov = null;

    // 寄付年月日
    public string|int|null $kifu_year = null;
    public string|int|null $kifu_month = null;
    public string|int|null $kifu_day = null;

    // 寄付金額
    public string|int|null $kifu_amount = null;

    // 寄付者情報
    public string|null $zipcode = null;
    public string|int|null $pref_id = null;
    public string|null $city = null;
    public string|null $address1 = null;
    public string|null $address2 = null;
    public string|null $phone = null;
    public string|null $name = null;
    public string|null $name_kana = null;
    public string|null $sign = '0';
    public string|null $sex = null;
    public string|int|null $birth_year = null;
    public string|int|null $birth_month = null;
    public string|int|null $birth_day = null;
    public string|null $individual_number = null; // マイナンバー
    // 特例にかかわるチェックボックス
    public string|int|null $checkbox1 = null;
    public string|int|null $checkbox2 = null;
    public string|int|null $use_western_year = '0'; // 西暦の利用

    /**
     * @return void
     */
    public function init()
    {
        parent::init();

        // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
        $date = (new DateTimeImmutable(sprintf('@%d', $_SERVER['REQUEST_TIME'])))
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

    /**
     * @inheritdoc
     */
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
            'use_western_year',
            'zipcode',
        ];
        $trimAttrs = array_filter($allAttrs, fn (string $v): bool => $v !== 'checkbox1' &&
                $v !== 'checkbox2' &&
                $v !== 'sign' &&
                $v !== 'use_western_year');
        $requiredAttrs = array_filter($allAttrs, fn (string $v): bool => $v !== 'address2' &&
                $v !== 'individual_number' &&
                $v !== 'sign' &&
                $v !== 'use_western_year');
        return [
            [$trimAttrs, 'trim'],
            [$requiredAttrs, 'required'],
            [['post_year', 'kifu_year', 'birth_year'], 'integer', 'min' => 1],
            [['post_month', 'kifu_month', 'birth_month'], 'integer', 'min' => 1, 'max' => 12],
            [['post_day', 'kifu_day', 'birth_day'], 'integer', 'min' => 1, 'max' => 31],
            [['kifu_amount'], 'integer', 'min' => 1],
            [['local_gov', 'city', 'address1', 'address2', 'name', 'name_kana'], 'string'],
            [['name_kana'], 'filter', 'filter' => fn ($v) => mb_convert_kana($v, 'asKCV', 'UTF-8')],
            [['zipcode'], 'match', 'pattern' => '/^\d{7}$/'],
            [['pref_id'], 'exist',
                'skipOnError' => true,
                'targetClass' => Prefecturer::class,
                'targetAttribute' => ['pref_id' => 'id'],
            ],
            [['phone'], 'match', 'pattern' => '/^0[0-9\-]+$/'],
            [['sex'], 'in',
                'range' => [
                    static::SEX_MALE,
                    static::SEX_FEMALE,
                ],
            ],
            [['checkbox1', 'checkbox2'], 'in',
                'range' => ['1'],
                'message' => 'この項目は必ずチェックが必要です。',
            ],
            [['sign', 'use_western_year'], 'boolean'],
            [['individual_number'], 'match', 'pattern' => '/^\d{12}$/'],
            [['individual_number'], MyNumberValidator::class],
        ];
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
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
            'use_western_year' => '和暦を出力せず、西暦の使用を強制する',
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

        if (!$pref = $this->prefecturer) {
            throw new Exception();
        }

        $pdf = Yii::createObject(Pdf::class)
            ->setUseWesternYear($this->use_western_year === '1')
            ->setEnvelope($post, (string)$this->local_gov)
            ->setAddress(
                (string)$this->zipcode,
                $pref,
                (string)$this->city,
                (string)$this->address1,
                (string)$this->address2,
            )
            ->setPhone((string)$this->phone)
            ->setName(
                (string)$this->name,
                (string)$this->name_kana,
                $this->sign === '1',
            )
            ->setIndividualNumber((string)$this->individual_number)
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

        $this->attributes = array_merge(
            [
                'post_year' => (int)$today->format('Y'),
                'post_month' => (int)$today->format('n'),
                'post_day' => (int)$today->format('j'),
                'kifu_year' => (int)$yesterday->format('Y'),
                'kifu_month' => (int)$yesterday->format('n'),
                'kifu_day' => (int)$yesterday->format('j'),
                'kifu_amount' => mt_rand(5, 50) * 1000,
                'checkbox1' => '1',
                'checkbox2' => '1',
            ],
            $this->generateFakeAddress(),
            $this->generateFakeLocalGov(),
            $this->generateFakePerson(),
            $this->generateFakePhone(),
        );

        return $this;
    }

    /** @return array<string, int|string> */
    private function generateFakeAddress(): array
    {
        $gimei = Gimei::generateAddress();

        return [
            'zipcode' => sprintf('%07d', random_int(0, 9999999)),
            'pref_id' => random_int(1, 47),
            'city' => $gimei->getCity()->getKanji(),
            'address1' => mb_strlen($gimei->getTown()->getKanji(), 'UTF-8') > 4
                ? vsprintf('%s%d-%d', [
                    $gimei->getTown()->getKanji(),
                    random_int(10, 9999),
                    random_int(1, 9),
                ])
                : vsprintf('%s%d-%d-%d', [
                    $gimei->getTown()->getKanji(),
                    random_int(1, 9),
                    random_int(1, 99),
                    random_int(1, 99),
                ]),
            'address2' => '',
        ];
    }

    /** @return array<string, string> */
    private function generateFakeLocalGov(): array
    {
        for ($i = 0; $i < 10000; ++$i) {
            // Gimei の生成する自治体名は色々混じっているので、
            // 処理が確実で簡単になる「なんとか市」だけのアドレスを使用する
            // 廿日市市とか四日市市とかは出ない
            $city = Gimei::generateAddress()->getCity()->getKanji();
            if (preg_match('/^[^市]+市$/u', $city)) {
                return [
                    'local_gov' => $city,
                ];
            }
        }

        // fallback
        return [
            'local_gov' => '寝屋川市',
        ];
    }

    /** @return array<string, int|string> */
    private function generateFakePerson(): array
    {
        $gimei = Gimei::generateName();
        $birthday = (new DateTimeImmutable('now', new DateTimeZone(Yii::$app->timeZone)))
            ->setTimestamp(mt_rand(
                (int)floor(time() - 55 * 365.2425 * 86400),
                (int)ceil(time() - 23 * 365.2425 * 86400),
            ));

        return [
            'birth_year' => (int)$birthday->format('Y'),
            'birth_month' => (int)$birthday->format('n'),
            'birth_day' => (int)$birthday->format('j'),
            'name' => vsprintf('%s　%s', [
                $gimei->getLastName()->getKanji(),
                $gimei->getFirstName()->getKanji(),
            ]),
            'name_kana' => vsprintf('%s　%s', [
                $gimei->getLastName()->getKatakana(),
                $gimei->getFirstName()->getKatakana(),
            ]),
            'sex' => $gimei->isMale() ? '1' : '2',
            'individual_number' => MyNumber::generate(),
        ];
    }

    /** @return array<string, string> */
    private function generateFakePhone(): array
    {
        return [
            'phone' => vsprintf('%s-%04d-%04d', [
                ['070', '080', '090'][random_int(0, 2)],
                random_int(0, 9999),
                random_int(0, 9999),
            ]),
        ];
    }
}
