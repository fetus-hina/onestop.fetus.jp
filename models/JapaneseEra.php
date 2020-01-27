<?php

namespace app\models;

use DateTimeImmutable;
use DateTimeZone;
use Yii;

class JapaneseEra
{
    public const MEIJI = '明治';
    public const TAISHO = '大正';
    public const SHOWA = '昭和';
    public const HEISEI = '平成';
    public const REIWA = '令和';

    public static function getYearList(int $yearStart, int $yearEnd): array
    {
        $yFormat = function (int $year, array $era): array {
            $dYear = ($year - (int)$era['start']->format('Y')) + 1;
            $dYearJ = $dYear === 1 ? '元' : (string)$dYear;
            // H31
            $formatted1 = sprintf('%s%d', $era['initial'], $dYear);
            // 平成31年
            $formatted2 = sprintf('%s%s年', $era['name'], $dYearJ);

            return [
                'era' => $era['name'],
                'year1' => $dYear,
                'year2' => $dYearJ,
                'fmt1' => $formatted1,
                'fmt2' => $formatted2,
            ];
        };

        $result = [];
        for ($y = $yearStart; $y <= $yearEnd; ++$y) {
            for ($m = 1; $m <= 12; ++$m) {
                $era = static::getEra($y, $m, 1);
                $_ = $yFormat($y, $era);
                $result[$_['fmt1']] = $_;
            }

            $era = static::getEra($y, 12, 31);
            $_ = $yFormat($y, $era);
            $result[$_['fmt1']] = $_;
        }
        return $result;
    }

    public static function getEra(int $year, int $month, int $day): ?array
    {
        $date = static::makeDate($year, $month, $day);
        foreach (static::getEraData() as $era) {
            if ($date >= $era['start']) {
                return $era;
            }
        }
        return null;
    }

    private static function getEraData(): array
    {
        return [
            [
                'name' => static::REIWA,
                'initial' => 'R',
                'start' => static::makeDate(2019, 5, 1),
            ],
            [
                'name' => static::HEISEI,
                'initial' => 'H',
                'start' => static::makeDate(1989, 1, 8),
            ],
            [
                'name' => static::SHOWA,
                'initial' => 'S',
                'start' => static::makeDate(1926, 12, 25),
            ],
            [
                'name' => static::TAISHO,
                'initial' => 'T',
                'start' => static::makeDate(1912, 7, 30),
            ],
            [
                'name' => static::MEIJI,
                'initial' => 'M',
                'start' => static::makeDate(1868, 1, 1),
            ],
        ];
    }

    private static function makeDate(int $year, int $month, int $day): DateTimeImmutable
    {
        return (new DateTimeImmutable())
            ->setTimeZone(new DateTimeZone('Asia/Tokyo'))
            ->setDate($year, $month, $day)
            ->setTime(0, 0, 0);
    }
}
