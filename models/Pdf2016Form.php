<?php
namespace app\models;

use Yii;
use ZendPdf\PdfDocument;
use app\validators\MyNumberValidator;
use yii\base\Model;

class Pdf2016Form extends Model
{
    const SEX_MALE   = '1';
    const SEX_FEMALE = '2';

    const ERA_MEIJI  = 'M';
    const ERA_TAISHO = 'T';
    const ERA_SHOWA  = 'S';
    const ERA_HEISEI = 'H';
    const ERA_REIWA  = 'R';

    // 投函年月日
    public $post_year;
    public $post_month;
    public $post_day;
    // 自治体名（"長"なし）
    public $local_gov;
    // 寄付年月日
    public $kifu_year;
    public $kifu_month;
    public $kifu_day;
    // 寄付金額
    public $kifu_amount;
    // 寄付者情報
    public $zipcode;
    public $pref_id;
    public $city;
    public $address1;
    public $address2;
    public $phone;
    public $name;
    public $name_kana;
    public $sex;
    public $birth_year;
    public $birth_month;
    public $birth_day;
    public $individual_number; // マイナンバー
    // 特例にかかわるチェックボックス
    public $checkbox1;
    public $checkbox2;

    public function init()
    {
        parent::init();

        $date = (new \DateTimeImmutable(sprintf('@%d', $_SERVER['REQUEST_TIME'] ?? time())))
            ->setTimezone(new \DateTimeZone('Asia/Tokyo'));

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
        if ($this->birth_year === null &&
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
            'post_year',
            'post_month',
            'post_day',
            'local_gov',
            'kifu_year',
            'kifu_month',
            'kifu_day',
            'kifu_amount',
            'zipcode',
            'pref_id',
            'city',
            'address1',
            'address2',
            'phone',
            'name',
            'name_kana',
            'sex',
            'birth_year',
            'birth_month',
            'birth_day',
            'individual_number',
            'checkbox1',
            'checkbox2',
        ];
        $requiredAttrs = array_filter($allAttrs, function ($v) {
            return $v !== 'address2' && $v !== 'name';
        });
        return [
            [$allAttrs, 'trim'],
            [$requiredAttrs, 'required'],
            [['post_year', 'kifu_year', 'birth_year'], 'integer', 'min' => 1],
            [['post_month', 'kifu_month', 'birth_month'], 'integer', 'min' => 1, 'max' => 12],
            [['post_day', 'kifu_day', 'birth_day'], 'integer', 'min' => 1, 'max' => 31],
            [['kifu_amount'], 'integer', 'min' => 1],
            [['local_gov', 'city', 'address1', 'address2', 'name', 'name_kana'], 'string'],
            [['name_kana'], 'filter', 'filter' => function ($v) {
                return mb_convert_kana($v, 'asKCV', 'UTF-8');
            }],
            [['zipcode'], 'match', 'pattern' => '/^\d{7}$/'],
            [['pref_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Prefecturer::class,
                'targetAttribute' => ['pref_id' => 'id']],
            [['phone'], 'match', 'pattern' => '/^0[0-9\-]+$/'],
            [['sex'], 'in', 'range' => [
                static::SEX_MALE,
                static::SEX_FEMALE
            ]],
            [['individual_number'], 'match', 'pattern' => '/^\d{12}$/'],
            [['individual_number'], MyNumberValidator::class],
        ];
    }

    public function attributeLabels()
    {
        return [
            'post_year'     => '投函予定日（年）',
            'post_month'    => '投函予定日（月）',
            'post_day'      => '投函予定日（日）',
            'local_gov'     => '寄付先自治体名',
            'kifu_year'     => '寄付日（年）',
            'kifu_month'    => '寄付日（月）',
            'kifu_day'      => '寄付日（日）',
            'kifu_amount'   => '寄付金額',
            'zipcode'       => '郵便番号',
            'pref_id'       => '都道府県',
            'city'          => '市区町村',
            'address1'      => '住所(1)',
            'address2'      => '住所(2)',
            'phone'         => '電話番号',
            'name'          => '名前（漢字）',
            'name_kana'     => '名前（カナ）',
            'sex'           => '性別',
            'birth_year'    => '誕生日（年）',
            'birth_month'   => '誕生日（月）',
            'birth_day'     => '誕生日（日）',
            'individual_number' => 'マイナンバー（個人番号）',
            'checkbox1'     => '地方税法附則第７条第１項（第８項）に規定する申告特例対象寄附者である',
            'checkbox2'     => '地方税法附則第７条第２項（第９項）に規定する要件に該当する者である',
        ];
    }

    public function createPdf()
    {
        $pdf = Yii::createObject(Pdf2016::class);
        $pdf->setEnvelope(
                (int)$this->post_year,
                (int)$this->post_month,
                (int)$this->post_day,
                $this->local_gov
            )
            ->setAddress(
                $this->zipcode,
                $this->prefecturer,
                $this->city,
                $this->address1,
                $this->address2
            )
            ->setPhone($this->phone)
            ->setName($this->name)
            ->setKanaName($this->name_kana)
            ->setIndividualNumber($this->individual_number)
            ->setSex($this->sex)
            ->setBirthday(
                (int)$this->birth_year,
                (int)$this->birth_month,
                (int)$this->birth_day
            )
            ->setKifuData(
                (int)$this->kifu_year,
                (int)$this->kifu_month,
                (int)$this->kifu_day,
                (int)$this->kifu_amount
            )
            ->setCheckbox();
        //$pdf->drawDebugLines();

        header('Content-Type: application/pdf');
        echo $pdf->binary;
        exit;
    }

    public function getPrefecturer() : ?Prefecturer
    {
        return Prefecturer::findOne(['id' => $this->pref_id]);
    }
}
