<?php

declare(strict_types=1);

namespace tests\unit\models;

use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;
use UnitTester;
use Yii;
use app\models\Era;

use function count;
use function is_int;
use function mktime;
use function strtotime;
use function substr;

class EraTest extends Unit
{
    protected UnitTester $tester;

    public function testEraDataCount(): void
    {
        $this->assertEquals(
            count($this->getEraData()),
            Era::find()->count(),
        );
    }

    /**
     * @param int|string $startDate
     * @dataProvider getEraData
     */
    public function testEraData($startDate, string $nameFull, string $nameShort): void
    {
        $model = Era::findOne(['name' => $nameFull]);
        $this->assertInstanceOf(Era::class, $model);
        $this->assertEquals($nameFull, $model->name);
        $this->assertEquals($nameShort, $model->short_romaji);
        if (is_int($startDate)) {
            // 明治元年は太陽暦ではないので月日に意味を持たない
            $this->assertGreaterThanOrEqual(mktime(0, 0, 0, 1, 1, $startDate), $model->start_date);
            $this->assertLessThanOrEqual(mktime(0, 0, 0, 12, 31, $startDate), $model->start_date);
        } else {
            $this->assertEquals(strtotime($startDate . 'T00:00:00+09:00'), $model->start_date);
        }

        $this->assertEquals((int)substr((string)$startDate, 0, 4), $model->startYear);
    }

    public function testCalcYear(): void
    {
        // 江戸時代のような古いデータは取れないので null が返る
        $this->assertNull(
            Era::calcYear(new DateTimeImmutable('1603-03-24T00:00:00+09:00', new DateTimeZone('Asia/Tokyo'))),
        );

        $result = Era::calcYear(new DateTimeImmutable('2020-01-23T11:22:33+09:00', new DateTimeZone('Asia/Tokyo')));
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Era::class, $result[0]);
        $this->assertIsInt($result[1]);

        // 令和2年とわかる
        $this->assertEquals('令和', $result[0]->name);
        $this->assertEquals(2, $result[1]);
    }

    public function testValidate(): void
    {
        // Note: 使わないので適当
        $model = Yii::createObject([
            'class' => Era::class,
            'start_date' => strtotime('2100-01-01T00:00:00+09:00'),
            'name' => 'ほげ',
            'short_romaji' => 'X',
            'enabled' => 1,
        ]);
        $this->assertTrue($model->validate());
    }

    public function getEraData(): array
    {
        return [
            '明治' => [1868, '明治', 'M'],
            '大正' => ['1912-07-30', '大正', 'T'],
            '昭和' => ['1926-12-25', '昭和', 'S'],
            '平成' => ['1989-01-08', '平成', 'H'],
            '令和' => ['2019-05-01', '令和', 'R'],
        ];
    }
}
