<?php
namespace app\models;

use Yii;
use ZendPdf\Color\Cmyk;
use ZendPdf\Color\ColorInterface;
use ZendPdf\PdfDocument;
use ZendPdf\Font;
use app\models\Pdf2016Form as Form;
use yii\base\Model;

class Pdf2016 extends Model
{
    const SEX_MALE   = Form::SEX_MALE;
    const SEX_FEMALE = Form::SEX_FEMALE;

    const ERA_MEIJI  = Form::ERA_MEIJI;
    const ERA_TAISHO = Form::ERA_TAISHO;
    const ERA_SHOWA  = Form::ERA_SHOWA;
    const ERA_HEISEI = Form::ERA_HEISEI;
    const ERA_REIWA  = Form::ERA_REIWA;

    const A4_WIDTH_MM  = 210;
    const A4_HEIGHT_MM = 297;

    const INDIVIDUAL_COL_1_LEFT_MM      =  49.8;
    const INDIVIDUAL_COL_1_RIGHT_MM     = 101.6;
    const INDIVIDUAL_COL_2_LEFT_MM      = 116.8;
    const INDIVIDUAL_COL_2_RIGHT_MM     = 168.5;
    const INDIVIDUAL_ADDRESS_TOP_MM     =  36.3;
    const INDIVIDUAL_ADDRESS_BOTTOM_MM  =  60.2;
    const INDIVIDUAL_PHONE_TOP_MM       = self::INDIVIDUAL_ADDRESS_BOTTOM_MM;
    const INDIVIDUAL_PHONE_BOTTOM_MM    =  68.2;
    const INDIVIDUAL_KANA_TOP_MM        = self::INDIVIDUAL_ADDRESS_TOP_MM;
    const INDIVIDUAL_KANA_BOTTOM_MM     =  39.45;
    const INDIVIDUAL_NAME_TOP_MM        = self::INDIVIDUAL_KANA_BOTTOM_MM;
    const INDIVIDUAL_NAME_BOTTOM_MM     =  49.0;
    const INDIVIDUAL_NUMBER_TOP_MM      = self::INDIVIDUAL_NAME_BOTTOM_MM;
    const INDIVIDUAL_NUMBER_BOTTOM_MM   =  55.4;
    const INDIVIDUAL_NUMBER_CELL_WIDTH_MM = (self::INDIVIDUAL_COL_2_RIGHT_MM - self::INDIVIDUAL_COL_2_LEFT_MM) / 12;
    const INDIVIDUAL_SEX_V_MM           =  57.8; // 性別の縦方向中心位置
    const INDIVIDUAL_SEX_H_MALE_MM      = 135.6; // 男の横方向中心位置
    const INDIVIDUAL_SEX_H_FEMALE_MM    = 150.0; // 女の横方向中心位置
    const INDIVIDUAL_SEX_CIRCLE_R       =   1.85; // 性別の円の大きさ
    const INDIVIDUAL_BIRTH_TOP_MM       = self::INDIVIDUAL_PHONE_TOP_MM;
    const INDIVIDUAL_BIRTH_BOTTOM_MM    = self::INDIVIDUAL_PHONE_BOTTOM_MM;
    const INDIVIDUAL_BIRTH_V_1_MM       =  63.0; // 誕生日・明治/大正 の縦方向中心位置 | 明・大
    const INDIVIDUAL_BIRTH_V_2_MM       =  65.7; // 誕生日・昭和/平成 の縦方向中心位置 | 昭・平
    const INDIVIDUAL_BIRTH_H_1_MM       = 124.1; // 誕生日・明治/昭和 の横方向中心位置 ~~~~~~~~~
    const INDIVIDUAL_BIRTH_H_2_MM       = 128.4; // 誕生日・大正/平成 の横方向中心位置
    const INDIVIDUAL_BIRTH_CIRCLE_MM    =   1.5; // 明治・大正・昭和・平成の円の大きさ
    const INDIVIDUAL_BIRTH_CELL_WIDTH_MM    =  9.54;
    const INDIVIDUAL_BIRTH_YEAR_LEFT_MM     = self::INDIVIDUAL_COL_2_LEFT_MM + 17.15;
    const INDIVIDUAL_BIRTH_YEAR_RIGHT_MM    = self::INDIVIDUAL_BIRTH_YEAR_LEFT_MM + self::INDIVIDUAL_BIRTH_CELL_WIDTH_MM;
    const INDIVIDUAL_BIRTH_MONTH_LEFT_MM    = self::INDIVIDUAL_BIRTH_YEAR_RIGHT_MM;
    const INDIVIDUAL_BIRTH_MONTH_RIGHT_MM   = self::INDIVIDUAL_BIRTH_MONTH_LEFT_MM + self::INDIVIDUAL_BIRTH_CELL_WIDTH_MM;
    const INDIVIDUAL_BIRTH_DAY_LEFT_MM      = self::INDIVIDUAL_BIRTH_MONTH_RIGHT_MM;
    const INDIVIDUAL_BIRTH_DAY_RIGHT_MM     = self::INDIVIDUAL_BIRTH_DAY_LEFT_MM + self::INDIVIDUAL_BIRTH_CELL_WIDTH_MM;

    const TITLE_YEAR_CELL_TOP_MM        = 17.3;
    const TITLE_YEAR_CELL_BOTTOM_MM     = 21.3;
    const TITLE_YEAR_CELL_LEFT_MM       = 43.0;
    const TITLE_YEAR_CELL_RIGHT_MM      = 54.0;

    const ENVELOPE_CELL_TOP_MM          = 29.9;
    const ENVELOPE_CELL_BOTTOM_MM       = 35.8;
    const ENVELOPE_CELL_LEFT_MM         = 35.0;
    const ENVELOPE_CELL_RIGHT_MM        = 88.4;
    const ENVELOPE_DATE_TOP_MM          = self::ENVELOPE_CELL_TOP_MM;
    const ENVELOPE_DATE_BOTTOM_MM       = self::ENVELOPE_DATE_TOP_MM + 2.9;
    const ENVELOPE_YEAR_LEFT_MM         = self::ENVELOPE_CELL_LEFT_MM + 6.2;
    const ENVELOPE_YEAR_RIGHT_MM        = self::ENVELOPE_CELL_LEFT_MM + 14.6;
    const ENVELOPE_MONTH_LEFT_MM        = self::ENVELOPE_CELL_LEFT_MM + 17.6;
    const ENVELOPE_MONTH_RIGHT_MM       = self::ENVELOPE_CELL_LEFT_MM + 25.7;
    const ENVELOPE_DAY_LEFT_MM          = self::ENVELOPE_CELL_LEFT_MM + 29.7;
    const ENVELOPE_DAY_RIGHT_MM         = self::ENVELOPE_CELL_LEFT_MM + 37.9;
    const ENVELOPE_NAME_LEFT_MM         = self::ENVELOPE_CELL_LEFT_MM;
    const ENVELOPE_NAME_RIGHT_MM        = self::ENVELOPE_CELL_RIGHT_MM - 11.0;
    const ENVELOPE_NAME_BOTTOM_MM       = self::ENVELOPE_CELL_BOTTOM_MM;
    const ENVELOPE_NAME_TOP_MM          = self::ENVELOPE_NAME_BOTTOM_MM - 2.7;

    const DATA_CELL_LEFT_MM             = self::ENVELOPE_CELL_LEFT_MM - 0.2;
    const DATA_CELL_RIGHT_MM            = self::INDIVIDUAL_COL_2_RIGHT_MM;
    const DATA_CELL_CENTER_MM           = self::INDIVIDUAL_COL_1_RIGHT_MM + 0.1;
    const DATA_KIFU_CELL_TOP_MM         = 138.5;
    const DATA_KIFU_CELL_BOTTOM_MM      = 143.3;
    const DATA_KIFU_YEAR_LEFT_MM        = self::DATA_CELL_LEFT_MM + 18.0;
    const DATA_KIFU_YEAR_RIGHT_MM       = self::DATA_CELL_LEFT_MM + 27.0;
    const DATA_KIFU_MONTH_LEFT_MM       = self::DATA_CELL_LEFT_MM + 30.7;
    const DATA_KIFU_MONTH_RIGHT_MM      = self::DATA_CELL_LEFT_MM + 39.6;
    const DATA_KIFU_DAY_LEFT_MM         = self::DATA_CELL_LEFT_MM + 42.7;
    const DATA_KIFU_DAY_RIGHT_MM        = self::DATA_CELL_LEFT_MM + 52.4;

    const _8PT = 8 * 25.4 / 72;
    const _9PT = 9 * 25.4 / 72;
    const _13PT = 13 * 25.4 / 72;

    const INDIVIDUAL_ADDRESS_FONT_MM    = self::_8PT;
    const INDIVIDUAL_PHONE_FONT_MM      = self::INDIVIDUAL_ADDRESS_FONT_MM;
    const INDIVIDUAL_KANA_FONT_MM       =   2.6;
    const INDIVIDUAL_NAME_FONT_MM       =   5.2;
    const INDIVIDUAL_NUMBER_FONT_MM     =   5.2;
    const INDIVIDUAL_BIRTH_FONT_MM      = self::INDIVIDUAL_ADDRESS_FONT_MM;
    const TITLE_FONT_MM                 = self::TITLE_YEAR_CELL_BOTTOM_MM - self::TITLE_YEAR_CELL_TOP_MM;
    const ENVELOPE_NAME_FONT_MM         = self::ENVELOPE_NAME_BOTTOM_MM - self::ENVELOPE_NAME_TOP_MM;
    const ENVELOPE_DATE_FONT_MM         = self::ENVELOPE_NAME_FONT_MM;
    const DATA_FONT_MM                  = self::_9PT;
    const DATA_CHECK_FONT_MM            = self::_13PT;

    private $pdf;
    private $page;

    public function init()
    {
        parent::init();
        $this->pdf = PdfDocument::load(Yii::getAlias('@app/data/pdfs/2016.pdf'));
        $this->page = $this->pdf->pages[0];

        // $this->drawDebugLines();
    }

    public function getBinary() : string
    {
        return $this->pdf->render();
    }

    public function setEnvelope(
        int $year,
        int $month,
        int $day,
        string $localGovName
    ) : self {
        list($font, $baseline) = $this->Mincho;

        $era = JapaneseEra::getEra($year, $month, $day);
        $eraY = $year - (int)$era['start']->format('Y') + 1;
        $year  = ($eraY === 1) ? '元' : mb_convert_kana((string)$eraY, 'A', 'UTF-8');
        $month = mb_convert_kana((string)$month, 'A', 'UTF-8');
        $day   = mb_convert_kana((string)$day,   'A', 'UTF-8');
        $this->page->setFont($font, static::mm2pt(static::ENVELOPE_DATE_FONT_MM));

        if ($era['name'] !== '平成') {
            // 平成を消す
            $x1 = static::x(static::ENVELOPE_CELL_LEFT_MM + 0.2);
            $x2 = static::x(static::ENVELOPE_YEAR_LEFT_MM - 0.2);
            $y1 = static::y(
                (static::ENVELOPE_DATE_TOP_MM + static::ENVELOPE_DATE_BOTTOM_MM) / 2 - 0.4
            );
            $y2 = static::y(
                (static::ENVELOPE_DATE_TOP_MM + static::ENVELOPE_DATE_BOTTOM_MM) / 2 + 0.4
            );
            $this->page
                ->setLineColor($this->black)
                ->setLineWidth(self::mm2pt(0.35))
                ->drawLine($x1, $y1, $x2, $y1)
                ->drawLine($x1, $y2, $x2, $y2);

            // 元号
            $x = static::x(static::ENVELOPE_CELL_LEFT_MM + 0.25);
            $y = static::y(
                static::ENVELOPE_NAME_TOP_MM + static::ENVELOPE_NAME_FONT_MM * $baseline
            );
            $this->page->drawText($era['name'], $x, $y);
        }

        $y = static::ENVELOPE_DATE_TOP_MM + static::ENVELOPE_DATE_FONT_MM * $baseline;

        // 年
        $x = static::ENVELOPE_YEAR_LEFT_MM +
                (static::ENVELOPE_YEAR_RIGHT_MM - static::ENVELOPE_YEAR_LEFT_MM) / 2 - 
                    mb_strlen($year, 'UTF-8') * static::ENVELOPE_DATE_FONT_MM / 2;
        $this->page->drawText($year, static::x($x), static::y($y));

        // 月
        $x = static::ENVELOPE_MONTH_LEFT_MM +
                (static::ENVELOPE_MONTH_RIGHT_MM - static::ENVELOPE_MONTH_LEFT_MM) / 2 - 
                    mb_strlen($month, 'UTF-8') * static::ENVELOPE_DATE_FONT_MM / 2;
        $this->page->drawText($month, static::x($x), static::y($y));

        // 日
        $x = static::ENVELOPE_DAY_LEFT_MM +
                (static::ENVELOPE_DAY_RIGHT_MM - static::ENVELOPE_DAY_LEFT_MM) / 2 - 
                    mb_strlen($day, 'UTF-8') * static::ENVELOPE_DATE_FONT_MM / 2;
        $this->page->drawText($day, static::x($x), static::y($y));

        // 首長名
        $name = mb_convert_kana(sprintf('%s長', $localGovName), 'asKV', 'UTF-8');
        $this->page->setFont($font, static::mm2pt(static::ENVELOPE_NAME_FONT_MM));
        $y = static::ENVELOPE_NAME_TOP_MM + static::ENVELOPE_NAME_FONT_MM * $baseline;
        $x = static::ENVELOPE_NAME_RIGHT_MM - static::ENVELOPE_NAME_FONT_MM * mb_strlen($name, 'UTF-8');
        $this->page->drawText($name, static::x($x), static::y($y));
        return $this;
    }

    public function setAddress(
        string $zipcode,
        Prefecturer $pref,
        string $city,
        string $address1,
        ?string $address2) : self
    {
        $lines = [
            sprintf('〒%s-%s', substr($zipcode, 0, 3), substr($zipcode, 3, 4)),
            sprintf('%s %s', $pref->name, $city),
            $address1
        ];
        if ($address2 != '') {
            $lines[] = $address2;
        }
        $lines = array_map(
            function ($v) {
                return mb_convert_kana($v, 'ASKV', 'UTF-8');
            },
            $lines
        );
        $maxChars = max(1, max(array_map(
            function ($v) {
                return mb_strlen($v, 'UTF-8');
            },
            $lines
        )));
        $cellWidth = static::INDIVIDUAL_COL_1_RIGHT_MM - static::INDIVIDUAL_COL_1_LEFT_MM - 2; // padding 2x1mm
        $fontSize = max(1, min(
            static::INDIVIDUAL_ADDRESS_FONT_MM,
            $cellWidth / $maxChars
        ));
        list($font, $baseline) = $this->Mincho;
        $this->page->setFont($font, static::mm2pt($fontSize));
        $y = static::INDIVIDUAL_ADDRESS_TOP_MM + 1 + $fontSize * $baseline;
        $x = static::INDIVIDUAL_COL_1_LEFT_MM + 1;
        foreach ($lines as $line) {
            $this->page->drawText($line, static::x($x), static::y($y));
            $y += $fontSize * 1.2;
        }
        return $this;
    }

    public function setPhone(string $phone)
    {
        $phone = mb_convert_kana($phone, 'ASKV', 'UTF-8');
        $fontSize = static::INDIVIDUAL_ADDRESS_FONT_MM;
        list($font, $baseline) = $this->Mincho;
        $this->page->setFont($font, static::mm2pt($fontSize));
        $cellHeight = static::INDIVIDUAL_PHONE_BOTTOM_MM - static::INDIVIDUAL_PHONE_TOP_MM - 2; // padding 2x1mm
        $y = static::INDIVIDUAL_PHONE_TOP_MM + 1 +
                ($cellHeight / 2 - $fontSize / 2) +
                $fontSize * $baseline;
        $x = static::INDIVIDUAL_COL_1_LEFT_MM + 1;
        $this->page->drawText($phone, static::x($x), static::y($y));
        return $this;
    }

    public function setName(string $name) : self
    {
        if ($name != '') {
            list($font, $baseline) = $this->Mincho;
            $this->page->setFont($font, static::mm2pt(static::INDIVIDUAL_NAME_FONT_MM));
            $cellHeight = static::INDIVIDUAL_NAME_BOTTOM_MM - static::INDIVIDUAL_NAME_TOP_MM;
            $y = static::INDIVIDUAL_NAME_TOP_MM +
                    ($cellHeight / 2 - static::INDIVIDUAL_NAME_FONT_MM / 2) +
                    static::INDIVIDUAL_NAME_FONT_MM * $baseline;
            $x = static::INDIVIDUAL_COL_2_LEFT_MM + 1;
            $this->page->drawText($name, static::x($x), static::y($y));
        }
        return $this;
    }

    public function setKanaName(string $kana) : self
    {
        list($font, $baseline) = $this->Mincho;
        $this->page->setFont($font, static::mm2pt(static::INDIVIDUAL_KANA_FONT_MM));
        $cellHeight = static::INDIVIDUAL_KANA_BOTTOM_MM - static::INDIVIDUAL_KANA_TOP_MM;
        $y = static::INDIVIDUAL_KANA_TOP_MM +
                ($cellHeight / 2 - static::INDIVIDUAL_KANA_FONT_MM / 2) +
                static::INDIVIDUAL_KANA_FONT_MM * $baseline;
        $x = static::INDIVIDUAL_COL_2_LEFT_MM + 1;
        $this->page->drawText($kana, static::x($x), static::y($y));
        return $this;
    }

    public function setIndividualNumber(string $number) : self
    {
        $number = $number . str_repeat(' ', 12);
        list($font, $baseline) = $this->OCRB;
        $this->page->setFont($font, static::mm2pt(static::INDIVIDUAL_NUMBER_FONT_MM));
        $cellHeight = static::INDIVIDUAL_NUMBER_BOTTOM_MM - static::INDIVIDUAL_NUMBER_TOP_MM;
        $y = static::INDIVIDUAL_NUMBER_TOP_MM +
                ($cellHeight / 2 - static::INDIVIDUAL_NUMBER_FONT_MM / 2) +
                static::INDIVIDUAL_NUMBER_FONT_MM * $baseline;
        for ($i = 0; $i < 12; ++$i) {
            $character = substr($number, $i, 1);
            if (!preg_match('/^[0-9]$/', $character)) {
                continue;
            }
            $charWidth = self::pt2mm(
                $font->widthForGlyph($font->glyphNumberForCharacter($character)) /
                    $font->getUnitsPerEm() * self::mm2pt(static::INDIVIDUAL_NUMBER_FONT_MM)
            );
            $cellLeft = static::INDIVIDUAL_COL_2_LEFT_MM + static::INDIVIDUAL_NUMBER_CELL_WIDTH_MM * $i;
            $x = $cellLeft + (static::INDIVIDUAL_NUMBER_CELL_WIDTH_MM / 2 - $charWidth / 2);
            $this->page->drawText($character, static::x($x), static::y($y));
        }
        return $this;
    }

    public function setSex($sex) : self
    {
        $this->page
            ->setLineColor($this->black)
            ->setLineWidth(self::mm2pt(0.4))
            ->drawCircle(
                self::x(
                    $sex == static::SEX_MALE
                        ? static::INDIVIDUAL_SEX_H_MALE_MM
                        : static::INDIVIDUAL_SEX_H_FEMALE_MM
                ),
                self::y(static::INDIVIDUAL_SEX_V_MM),
                self::mm2pt(static::INDIVIDUAL_SEX_CIRCLE_R),
                \ZendPdf\Page::SHAPE_DRAW_STROKE
            );
        return $this;
    }
    
    public function setBirthday(int $year, int $month, int $day)
    {
        // 時代の○
        //$this->page
        //    ->setLineColor($this->black)
        //    ->setLineWidth(self::mm2pt(0.4))
        //    ->drawCircle(
        //        self::x(
        //            in_array($era, [static::ERA_MEIJI, static::ERA_SHOWA], true)
        //                ? static::INDIVIDUAL_BIRTH_H_1_MM
        //                : static::INDIVIDUAL_BIRTH_H_2_MM
        //        ),
        //        self::y(
        //            in_array($era, [static::ERA_MEIJI, static::ERA_TAISHO], true)
        //                ? static::INDIVIDUAL_BIRTH_V_1_MM
        //                : static::INDIVIDUAL_BIRTH_V_2_MM
        //        ),
        //        self::mm2pt(static::INDIVIDUAL_BIRTH_CIRCLE_MM),
        //        \ZendPdf\Page::SHAPE_DRAW_STROKE
        //    );

        list($font, $baseline) = $this->Mincho;
        $this->page->setFont($font, static::mm2pt(static::INDIVIDUAL_BIRTH_FONT_MM));

        $cellHeight = static::INDIVIDUAL_BIRTH_BOTTOM_MM - static::INDIVIDUAL_BIRTH_TOP_MM;
        $y = static::INDIVIDUAL_BIRTH_TOP_MM +
                ($cellHeight / 2 - static::INDIVIDUAL_BIRTH_FONT_MM / 2) +
                static::INDIVIDUAL_BIRTH_FONT_MM * $baseline;

        $drawText = function (string $value, float $left, float $right) use ($y, $font) {
            $text = mb_convert_kana((string)$value, 'ASKV', 'UTF-8');
            $textWidth = mb_strlen($text, 'UTF-8') * static::INDIVIDUAL_BIRTH_FONT_MM;

            $cellWidth = $right - $left;
            $x = $left + ($cellWidth / 2 - $textWidth / 2);
            $this->page->drawText($text, self::x($x), self::y($y));
        };

        $drawText(
            $year == 1 ? '元年' : $year,
            static::INDIVIDUAL_BIRTH_YEAR_LEFT_MM,
            static::INDIVIDUAL_BIRTH_YEAR_RIGHT_MM
        );
        $drawText(
            $month,
            static::INDIVIDUAL_BIRTH_MONTH_LEFT_MM,
            static::INDIVIDUAL_BIRTH_MONTH_RIGHT_MM
        );
        $drawText(
            $day,
            static::INDIVIDUAL_BIRTH_DAY_LEFT_MM,
            static::INDIVIDUAL_BIRTH_DAY_RIGHT_MM
        );
        return $this;
    }

    public function setKifuData(
        int $year,
        int $month,
        int $day,
        int $amount) : self
    {
        list($font, $baseline) = $this->Mincho;

        $year  = ($year === 1) ? '元' : mb_convert_kana((string)$year, 'A', 'UTF-8');
        $month = mb_convert_kana((string)$month, 'A', 'UTF-8');
        $day   = mb_convert_kana((string)$day,   'A', 'UTF-8');
        $amount = mb_convert_kana(number_format($amount), 'A', 'UTF-8');

        // タイトル
        $this->page->setFont($font, static::mm2pt(static::TITLE_FONT_MM));
        $y = static::TITLE_YEAR_CELL_TOP_MM + static::TITLE_FONT_MM * $baseline;
        $x = static::TITLE_YEAR_CELL_LEFT_MM +
                (static::TITLE_YEAR_CELL_RIGHT_MM - static::TITLE_YEAR_CELL_LEFT_MM) / 2 -
                    mb_strlen($year, 'UTF-8') * static::TITLE_FONT_MM / 2;
        $this->page->drawText($year, static::x($x), static::y($y));

        $this->page->setFont($font, static::mm2pt(static::DATA_FONT_MM));
        $cellHeight = static::DATA_KIFU_CELL_BOTTOM_MM - static::DATA_KIFU_CELL_TOP_MM;
        $y = static::DATA_KIFU_CELL_TOP_MM +
                ($cellHeight / 2 - static::DATA_FONT_MM / 2) +
                static::DATA_FONT_MM * $baseline;

        // 年月日
        $data = [
            [$year,  static::DATA_KIFU_YEAR_LEFT_MM,  static::DATA_KIFU_YEAR_RIGHT_MM],
            [$month, static::DATA_KIFU_MONTH_LEFT_MM, static::DATA_KIFU_MONTH_RIGHT_MM],
            [$day,   static::DATA_KIFU_DAY_LEFT_MM,   static::DATA_KIFU_DAY_RIGHT_MM],
        ];
        foreach ($data as list ($text, $x1, $x2)) {
            $x = $x1 + ($x2 - $x1) / 2 - (mb_strlen($text, 'UTF-8') * static::DATA_FONT_MM) / 2;
            $this->page->drawText($text, static::x($x), static::y($y));
        }

        // 寄付金額
        $len = mb_strlen($amount, 'UTF-8') + 2; // "￥" + ".-" の 2
        $x = static::DATA_CELL_CENTER_MM +
                (static::DATA_CELL_RIGHT_MM - static::DATA_CELL_CENTER_MM) / 2 -
                $len * static::DATA_FONT_MM / 2;
        $this->page->drawText('￥' . $amount . '.-', static::x($x), static::y($y));
        return $this;
    }

    public function setCheckbox() : self
    {
        list($font, $baseline) = $this->checkFont;

        // U+2713, Check mark
        $text = mb_convert_encoding(chr(0x27) . chr(0x13), 'UTF-8', 'UTF-16BE');

        $this->page->setFont($font, static::mm2pt(static::DATA_CHECK_FONT_MM));
        $x = static::DATA_CELL_RIGHT_MM - 8.3;
        $y_ = [
            143.3 + 26.4,
            143.3 + 69.7,
        ];
        foreach ($y_ as $y) {
            $this->page->drawText($text, static::x($x), static::y($y));
        }
        return $this;
    }

    public function drawDebugLines() : self
    {
        return $this
            ->drawDebugLinesTitleForms()
            ->drawDebugLinesEnvelopeForms()
            ->drawDebugLinesIndividualForms()
            ->drawDebugLinesData1Forms();
    }

    private function drawDebugLinesTitleForms() : self
    {
        $this->page
            ->setLineColor($this->cyan)
            ->setLineWidth(self::mm2pt(0.1));

        $this->page
            ->drawRectangle(
                static::x(static::TITLE_YEAR_CELL_LEFT_MM),
                static::y(static::TITLE_YEAR_CELL_TOP_MM),
                static::x(static::TITLE_YEAR_CELL_RIGHT_MM),
                static::y(static::TITLE_YEAR_CELL_BOTTOM_MM),
                \ZendPdf\Page::SHAPE_DRAW_STROKE
            );

        return $this;
    }

    private function drawDebugLinesEnvelopeForms() : self
    {
        $this->page
            ->setLineColor($this->orange)
            ->setLineWidth(self::mm2pt(0.1));

        $this->page
            // 左側の縦線
            ->drawLine(
                static::x(static::ENVELOPE_CELL_LEFT_MM),
                static::y(static::ENVELOPE_CELL_TOP_MM - 10),
                static::x(static::ENVELOPE_CELL_LEFT_MM),
                static::y(static::ENVELOPE_CELL_BOTTOM_MM + 10)
            )
            // 右側の縦線
            ->drawLine(
                static::x(static::ENVELOPE_CELL_RIGHT_MM),
                static::y(static::ENVELOPE_CELL_TOP_MM - 10),
                static::x(static::ENVELOPE_CELL_RIGHT_MM),
                static::y(static::ENVELOPE_CELL_BOTTOM_MM + 10)
            )
            // 上側の横線
            ->drawLine(
                static::x(static::ENVELOPE_CELL_LEFT_MM - 10),
                static::y(static::ENVELOPE_CELL_TOP_MM),
                static::x(static::ENVELOPE_CELL_RIGHT_MM + 10),
                static::y(static::ENVELOPE_CELL_TOP_MM)
            )
            // 下側の横線
            ->drawLine(
                static::x(static::ENVELOPE_CELL_LEFT_MM - 10),
                static::y(static::ENVELOPE_CELL_BOTTOM_MM),
                static::x(static::ENVELOPE_CELL_RIGHT_MM + 10),
                static::y(static::ENVELOPE_CELL_BOTTOM_MM)
            );

        $this->page
            ->setLineColor($this->cyan)
            ->setLineWidth(self::mm2pt(0.1));

        $this->page
            // 年月日
            ->drawRectangle(
                static::x(static::ENVELOPE_YEAR_LEFT_MM),
                static::y(static::ENVELOPE_DATE_TOP_MM),
                static::x(static::ENVELOPE_YEAR_RIGHT_MM),
                static::y(static::ENVELOPE_DATE_BOTTOM_MM),
                \ZendPdf\Page::SHAPE_DRAW_STROKE
            )
            ->drawRectangle(
                static::x(static::ENVELOPE_MONTH_LEFT_MM),
                static::y(static::ENVELOPE_DATE_TOP_MM),
                static::x(static::ENVELOPE_MONTH_RIGHT_MM),
                static::y(static::ENVELOPE_DATE_BOTTOM_MM),
                \ZendPdf\Page::SHAPE_DRAW_STROKE
            )
            ->drawRectangle(
                static::x(static::ENVELOPE_DAY_LEFT_MM),
                static::y(static::ENVELOPE_DATE_TOP_MM),
                static::x(static::ENVELOPE_DAY_RIGHT_MM),
                static::y(static::ENVELOPE_DATE_BOTTOM_MM),
                \ZendPdf\Page::SHAPE_DRAW_STROKE
            )
            // 宛先首長名
            ->drawRectangle(
                static::x(self::ENVELOPE_NAME_LEFT_MM),
                static::y(self::ENVELOPE_NAME_TOP_MM),
                static::x(self::ENVELOPE_NAME_RIGHT_MM),
                static::y(self::ENVELOPE_NAME_BOTTOM_MM),
                \ZendPdf\Page::SHAPE_DRAW_STROKE
            );

        return $this;
    }
                

    private function drawDebugLinesIndividualForms() : self
    {
        $this->page
            ->setLineColor($this->orange)
            ->setLineWidth(self::mm2pt(0.1));

        $this->page
            // 左カラム左側の縦線
            ->drawLine(
                static::x(static::INDIVIDUAL_COL_1_LEFT_MM),
                static::y(static::INDIVIDUAL_ADDRESS_TOP_MM - 10),
                static::x(static::INDIVIDUAL_COL_1_LEFT_MM),
                static::y(static::INDIVIDUAL_PHONE_BOTTOM_MM + 10)
            )
            // 左カラム右側の縦線
            ->drawLine(
                static::x(static::INDIVIDUAL_COL_1_RIGHT_MM),
                static::y(static::INDIVIDUAL_ADDRESS_TOP_MM - 10),
                static::x(static::INDIVIDUAL_COL_1_RIGHT_MM),
                static::y(static::INDIVIDUAL_PHONE_BOTTOM_MM + 10)
            )
            // 右カラム左側の縦線
            ->drawLine(
                static::x(static::INDIVIDUAL_COL_2_LEFT_MM),
                static::y(static::INDIVIDUAL_ADDRESS_TOP_MM - 10),
                static::x(static::INDIVIDUAL_COL_2_LEFT_MM),
                static::y(static::INDIVIDUAL_PHONE_BOTTOM_MM + 10)
            )
            // 右カラム右側の縦線
            ->drawLine(
                static::x(static::INDIVIDUAL_COL_2_RIGHT_MM),
                static::y(static::INDIVIDUAL_ADDRESS_TOP_MM - 10),
                static::x(static::INDIVIDUAL_COL_2_RIGHT_MM),
                static::y(static::INDIVIDUAL_PHONE_BOTTOM_MM + 10)
            )
            // 住所・フリガナの上の線
            ->drawLine(
                static::x(static::INDIVIDUAL_COL_1_LEFT_MM - 10),
                static::y(static::INDIVIDUAL_ADDRESS_TOP_MM),
                static::x(static::INDIVIDUAL_COL_2_RIGHT_MM + 10),
                static::y(static::INDIVIDUAL_ADDRESS_TOP_MM)
            )
            // 住所・性別の下の線
            ->drawLine(
                static::x(static::INDIVIDUAL_COL_1_LEFT_MM - 10),
                static::y(static::INDIVIDUAL_ADDRESS_BOTTOM_MM),
                static::x(static::INDIVIDUAL_COL_2_RIGHT_MM + 10),
                static::y(static::INDIVIDUAL_ADDRESS_BOTTOM_MM)
            )
            // 電話番号・生年月日の下の線
            ->drawLine(
                static::x(static::INDIVIDUAL_COL_1_LEFT_MM - 10),
                static::y(static::INDIVIDUAL_PHONE_BOTTOM_MM),
                static::x(static::INDIVIDUAL_COL_2_RIGHT_MM + 10),
                static::y(static::INDIVIDUAL_PHONE_BOTTOM_MM)
            )
            // カナ・名前の間の線
            ->drawLine(
                static::x(static::INDIVIDUAL_COL_2_LEFT_MM - 10),
                static::y(static::INDIVIDUAL_KANA_BOTTOM_MM),
                static::x(static::INDIVIDUAL_COL_2_RIGHT_MM + 10),
                static::y(static::INDIVIDUAL_KANA_BOTTOM_MM)
            )
            // 名前・個人番号の間の線
            ->drawLine(
                static::x(static::INDIVIDUAL_COL_2_LEFT_MM - 10),
                static::y(static::INDIVIDUAL_NUMBER_TOP_MM),
                static::x(static::INDIVIDUAL_COL_2_RIGHT_MM + 10),
                static::y(static::INDIVIDUAL_NUMBER_TOP_MM)
            )
            // 個人番号・性別の間の線
            ->drawLine(
                static::x(static::INDIVIDUAL_COL_2_LEFT_MM - 10),
                static::y(static::INDIVIDUAL_NUMBER_BOTTOM_MM),
                static::x(static::INDIVIDUAL_COL_2_RIGHT_MM + 10),
                static::y(static::INDIVIDUAL_NUMBER_BOTTOM_MM)
            );

        // 個人番号セル
        for ($i = 1; $i < 12; ++$i) {
            $x = static::INDIVIDUAL_COL_2_LEFT_MM + static::INDIVIDUAL_NUMBER_CELL_WIDTH_MM * $i;
            $this->page->drawLine(
                static::x($x),
                static::y(static::INDIVIDUAL_NUMBER_TOP_MM - 2),
                static::x($x),
                static::y(static::INDIVIDUAL_NUMBER_BOTTOM_MM + 2)
            );
        }

        // 生年月日
        $this->page
            ->setLineColor($this->cyan)
            ->setLineWidth(self::mm2pt(0.1));

        $this->page
            ->drawLine(
                static::x(static::INDIVIDUAL_BIRTH_YEAR_LEFT_MM),
                static::y(static::INDIVIDUAL_BIRTH_TOP_MM),
                static::x(static::INDIVIDUAL_BIRTH_YEAR_LEFT_MM),
                static::y(static::INDIVIDUAL_BIRTH_BOTTOM_MM)
            )
            ->drawLine(
                static::x(static::INDIVIDUAL_BIRTH_YEAR_RIGHT_MM),
                static::y(static::INDIVIDUAL_BIRTH_TOP_MM),
                static::x(static::INDIVIDUAL_BIRTH_YEAR_RIGHT_MM),
                static::y(static::INDIVIDUAL_BIRTH_BOTTOM_MM)
            )
            ->drawLine(
                static::x(static::INDIVIDUAL_BIRTH_MONTH_LEFT_MM),
                static::y(static::INDIVIDUAL_BIRTH_TOP_MM),
                static::x(static::INDIVIDUAL_BIRTH_MONTH_LEFT_MM),
                static::y(static::INDIVIDUAL_BIRTH_BOTTOM_MM)
            )
            ->drawLine(
                static::x(static::INDIVIDUAL_BIRTH_MONTH_RIGHT_MM),
                static::y(static::INDIVIDUAL_BIRTH_TOP_MM),
                static::x(static::INDIVIDUAL_BIRTH_MONTH_RIGHT_MM),
                static::y(static::INDIVIDUAL_BIRTH_BOTTOM_MM)
            )
            ->drawLine(
                static::x(static::INDIVIDUAL_BIRTH_DAY_LEFT_MM),
                static::y(static::INDIVIDUAL_BIRTH_TOP_MM),
                static::x(static::INDIVIDUAL_BIRTH_DAY_LEFT_MM),
                static::y(static::INDIVIDUAL_BIRTH_BOTTOM_MM)
            )
            ->drawLine(
                static::x(static::INDIVIDUAL_BIRTH_DAY_RIGHT_MM),
                static::y(static::INDIVIDUAL_BIRTH_TOP_MM),
                static::x(static::INDIVIDUAL_BIRTH_DAY_RIGHT_MM),
                static::y(static::INDIVIDUAL_BIRTH_BOTTOM_MM)
            );

        return $this;
    }

    // 当団体に対する寄付に関する事項
    private function drawDebugLinesData1Forms() : self
    {
        $this->page
            ->setLineColor($this->orange)
            ->setLineWidth(self::mm2pt(0.1));

        $this->page
            // 左
            ->drawLine(
                static::x(static::DATA_CELL_LEFT_MM),
                static::y(static::DATA_KIFU_CELL_TOP_MM - 10),
                static::x(static::DATA_CELL_LEFT_MM),
                static::y(static::DATA_KIFU_CELL_BOTTOM_MM + 10)
            )
            // 中央
            ->drawLine(
                static::x(static::DATA_CELL_CENTER_MM),
                static::y(static::DATA_KIFU_CELL_TOP_MM  - 10),
                static::x(static::DATA_CELL_CENTER_MM),
                static::y(static::DATA_KIFU_CELL_BOTTOM_MM + 10)
            )
            // 右
            ->drawLine(
                static::x(static::DATA_CELL_RIGHT_MM),
                static::y(static::DATA_KIFU_CELL_TOP_MM - 10),
                static::x(static::DATA_CELL_RIGHT_MM),
                static::y(static::DATA_KIFU_CELL_BOTTOM_MM + 10)
            )
            // 上
            ->drawLine(
                static::x(static::DATA_CELL_LEFT_MM - 10),
                static::y(static::DATA_KIFU_CELL_TOP_MM),
                static::x(static::DATA_CELL_RIGHT_MM + 10),
                static::y(static::DATA_KIFU_CELL_TOP_MM)
            )
            // 下
            ->drawLine(
                static::x(static::DATA_CELL_LEFT_MM - 10),
                static::y(static::DATA_KIFU_CELL_BOTTOM_MM),
                static::x(static::DATA_CELL_RIGHT_MM + 10),
                static::y(static::DATA_KIFU_CELL_BOTTOM_MM)
            );

        // 年月日
        $this->page
            ->setLineColor($this->cyan)
            ->setLineWidth(self::mm2pt(0.1));
        $x_ = [
            static::DATA_KIFU_YEAR_LEFT_MM,
            static::DATA_KIFU_YEAR_RIGHT_MM,
            static::DATA_KIFU_MONTH_LEFT_MM,
            static::DATA_KIFU_MONTH_RIGHT_MM,
            static::DATA_KIFU_DAY_LEFT_MM,
            static::DATA_KIFU_DAY_RIGHT_MM,
        ];
        foreach ($x_ as $x) {
            $this->page->drawLine(
                static::x($x),
                static::y(static::DATA_KIFU_CELL_TOP_MM),
                static::x($x),
                static::y(static::DATA_KIFU_CELL_BOTTOM_MM)
            );
        }

        return $this;
    }

    public function getBlack() : ColorInterface
    {
        return new Cmyk(0, 0, 0, 1.0);
    }

    public function getOrange() : ColorInterface
    {
        return new Cmyk(0, 0.9, 1.0, 0);
    }

    public function getCyan() : ColorInterface
    {
        return new Cmyk(1.0, 0, 0, 0);
    }

    // list ($font, $baseline)
    public function getOCRB() : array
    {
        static $cache;
        if (!$cache) {
            $cache = [
                Font::fontWithPath(Yii::getAlias('@app/data/fonts/ocrb/OCRB_aizu_1_1.ttf')),
                0.85 /*$fontOCRB->getAscent() / ($fontOCRB->getAscent() - $fontOCRB->getDescent());*/
            ];
        }
        return $cache;
    }

    public function getMincho() : array
    {
        static $cache;
        if (!$cache) {
            $cache = [
                Font::fontWithPath(Yii::getAlias('@app/data/fonts/ipam/ipam.ttf')),
                0.85 /*$fontOCRB->getAscent() / ($fontOCRB->getAscent() - $fontOCRB->getDescent());*/
            ];
        }
        return $cache;
    }

    public function getCheckFont() : array
    {
        static $cache;
        if (!$cache) {
            $cache = [
                Font::fontWithPath(Yii::getAlias('@app/data/fonts/source-sans-pro/SourceSansPro-Regular.ttf')),
                0.85
            ];
        }
        return $cache;
    }

    private static function x(float $mm) : float
    {
        return static::mm2pt($mm);
    }

    private static function y(float $mm) : float
    {
        return static::mm2pt(self::A4_HEIGHT_MM - $mm);
    }

    private static function mm2pt(float $mm) : float
    {
        return $mm * 72 / 25.4;
    }

    private static function pt2mm(float $pt) : float
    {
        return $pt * 25.4 / 72;
    }
}
