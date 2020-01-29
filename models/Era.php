<?php

declare(strict_types=1);

namespace app\models;

use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class Era extends ActiveRecord
{
    public static function find(): ActiveQuery
    {
        return new class (static::class) extends ActiveQuery {
            public function init()
            {
                parent::init();
                $this->andWhere(['<>', '{{era}}.[[enabled]]', 0]);
                $this->orderBy(['{{era}}.[[start_date]]' => SORT_DESC]);
            }

            public function andByDate(DateTimeImmutable $date): ActiveQuery
            {
                $this->andWhere(['<=', '{{era}}.[[start_date]]', $date->getTimestamp()]);
                return $this;
            }
        };
    }

    public static function calcYear(DateTimeImmutable $date): ?array
    {
        if (!$era = static::findByDate($date)) {
            return null;
        }

        return [
            $era,
            $date->format('Y') - $era->startYear + 1,
        ];
    }

    public static function findByDate(DateTimeImmutable $date): ?self
    {
        return static::find()
            ->andByDate($date)
            ->limit(1)
            ->one();
    }

    public static function tableName()
    {
        return 'era';
    }

    public function rules()
    {
        return [
            [['start_date', 'name', 'short_romaji'], 'required'],
            [['start_date'], 'integer'],
            [['name'], 'string'],
            [['short_romaji'], 'string', 'min' => 1, 'max' => 1],
            [['short_romaji'], 'match', 'pattern' => '/^[A-Z]$/'],
            [['enabled'], 'integer', 'min' => 0, 'max' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
        ];
    }

    public function getStartYear(): int
    {
        $t = (new DateTimeImmutable('now', new DateTimeZone('Asia/Tokyo')))
            ->setTimestamp((int)$this->start_date);
        return (int)$t->format('Y');
    }
}
