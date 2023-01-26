<?php

declare(strict_types=1);

namespace app\models;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

use const SORT_DESC;

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
final class Era extends ActiveRecord
{
    public static function find(): ActiveQuery
    {
        return parent::find()
            ->andWhere(['<>', '{{era}}.[[enabled]]', 0])
            ->orderBy(['{{era}}.[[start_date]]' => SORT_DESC]);
    }

    public static function calcYear(DateTimeInterface $date): ?array
    {
        if (!$era = self::findOneByDate($date)) {
            return null;
        }

        return [
            $era,
            (int)$date->format('Y') - $era->startYear + 1,
        ];
    }

    public static function findOneByDate(DateTimeInterface $date): ?self
    {
        return self::find()
            ->andWhere(['<=', '{{era}}.[[start_date]]', $date->getTimestamp()])
            ->limit(1)
            ->one();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'era';
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
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
