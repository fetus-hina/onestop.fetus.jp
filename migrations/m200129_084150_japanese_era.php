<?php

declare(strict_types=1);

use yii\db\Migration;

class m200129_084150_japanese_era extends Migration
{
    public function safeUp()
    {
        $this->createTable('era', [
            'id' => $this->primaryKey(),
            'start_date' => $this->integer()->notNull()->unique(),
            'name' => $this->text()->notNull()->unique(),
            'short_romaji' => $this->char(1)->notNull()->unique(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
        ]);

        $t = function (string $date): int {
            return (new DateTimeImmutable($date, new DateTimeZone('Asia/Tokyo')))
                ->getTimestamp();
        };

        $this->batchInsert('era', ['start_date', 'name', 'short_romaji', 'enabled'], [
            // 1873 年以前は太陰暦のため、それ以前には正しく適用できないが無視する
            [$t('1868-01-01'), '明治', 'M', true],
            [$t('1912-07-30'), '大正', 'T', true],
            [$t('1926-12-25'), '昭和', 'S', true],
            [$t('1989-01-08'), '平成', 'H', true],
            [$t('2019-05-01'), '令和', 'R', true],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('era');
    }
}
