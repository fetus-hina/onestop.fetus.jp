<?php

declare(strict_types=1);

namespace app\models;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Yii;
use app\models\query\EraQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "era".
 *
 * @property int $id
 * @property int $start_date
 * @property string $name
 * @property string $short_romaji
 * @property bool|int $enabled
 *
 * @property-read int $startYear
 */
class Era extends ActiveRecord
{
    public static function find(): EraQuery
    {
        return Yii::createObject(EraQuery::class, [static::class]);
    }

    public static function calcYear(DateTimeInterface $date): ?array
    {
        if (!$era = static::findByDate($date)) {
            return null;
        }

        return [
            $era,
            (int)$date->format('Y') - $era->startYear + 1,
        ];
    }

    public static function findByDate(DateTimeInterface $date): ?self
    {
        $query = static::find();
        assert($query instanceof EraQuery);

        return $query->andByDate($date)
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

    /** @codeCoverageIgnore */
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
