<?php

declare(strict_types=1);

namespace app\models;

use DateTimeImmutable;
use TCPDF;
use yii\base\Model;

/**
 * @property-read string $binary
 */
final class Pdf extends Model
{
    private const A4_WIDTH_MM = 210;
    private const A4_HEIGHT_MM = 297;

    private const LINE_WIDTH_BOLD = 0.6;
    private const LINE_WIDTH_REGULAR = self::LINE_WIDTH_BOLD / 2;
    private const LINE_WIDTH_THIN = self::LINE_WIDTH_REGULAR / 2;

    private array $black = [0, 0, 0, 100];
    private ?TCPDF $pdf = null;

    private bool $useWesternYear = false;

    /** @return void */
    public function init()
    {
        parent::init();

        $pdf = new TCPDF('P', 'mm', [self::A4_WIDTH_MM, self::A4_HEIGHT_MM], true, 'UTF-8');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetCellPadding(0);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $this->pdf = $pdf;

        $this->drawLines();
        $this->drawLabels();
    }

    public function getBinary(): string
    {
        assert($this->pdf !== null);

        return $this->pdf->Output('', 'S');
    }

    public function setUseWesternYear(bool $flag): self
    {
        $this->useWesternYear = $flag;
        return $this;
    }

    public function setEnvelope(DateTimeImmutable $date, string $localGovName): self
    {
        if ($formatted = $this->formatDate($date)) {
            $this->drawTextToBox(38.5, 30.8, 89.5, 33.95, $formatted, 'L', 'M');
        }

        $this->drawTextToBox(38.5, 33.95, 89.5 - 6, 37.1, $localGovName . '長　殿', 'R', 'M');

        return $this;
    }

    public function setAddress(
        string $zipCode,
        Prefecturer $prefecturer,
        string $city,
        string $address1,
        ?string $address2
    ): self {
        $text = mb_convert_kana(
            trim(implode("\n", [
                vsprintf('〒%s-%s', [
                    substr($zipCode, 0, 3),
                    substr($zipCode, 3, 4),
                ]),
                $prefecturer->name . '　' . $city,
                trim((string)$address1),
                trim((string)$address2),
            ])),
            'ASKV',
            'UTF-8'
        );
        $text = (string)preg_replace_callback(
            '/[\x{ff10}-\x{ff19}]{2,}/u',
            function (array $match): string {
                return mb_convert_kana($match[0], 'n', 'UTF-8');
            },
            $text
        );
        $this->drawTextToBox(
            52.3 + 0.5,
            37.1 + 0.5,
            102.2 - 0.5,
            61.4 - 0.5,
            $text,
            'L',
            'T',
            0.1,
            3.5
        );

        $text = mb_convert_kana(
            trim(implode("\n", [
                vsprintf('〒%s-%s %s %s', [
                    substr($zipCode, 0, 3),
                    substr($zipCode, 3, 4),
                    $prefecturer->name,
                    $city,
                ]),
                trim((string)$address1 . ' ' . (string)$address2),
            ])),
            'ASKV',
            'UTF-8'
        );
        $text = (string)preg_replace_callback(
            '/[\x{ff10}-\x{ff19}]{2,}/u',
            function (array $match): string {
                return mb_convert_kana($match[0], 'n', 'UTF-8');
            },
            $text
        );
        $this->drawTextToBox(
            53 + 0.5,
            240.8 + 0.5,
            140.2 - 0.5,
            251.4 - 0.5,
            $text,
            'L',
            'M'
        );

        return $this;
    }

    public function setPhone(string $phoneNumber): self
    {
        $this->drawTextToBox(
            52.3 + 0.5,
            61.4 + 0.5,
            102.2 - 0.5,
            69.8 - 0.5,
            mb_convert_kana(
                mb_convert_kana($phoneNumber, 'A', 'UTF-8'),
                'n',
                'UTF-8'
            ),
            'L',
            'M',
            0.1,
            3.5
        );
        return $this;
    }

    public function setName(?string $name, string $kana, bool $sign): self
    {
        $kana = mb_convert_kana(trim($kana), 'ASKV', 'UTF-8');
        $this->drawTextToBox(
            116.5 + 0.5,
            37.1 - 0.3,
            167 + 0.5,
            40.1 + 0.3,
            $kana,
            'L',
            'M'
        );

        $name = mb_convert_kana(trim((string)$name), 'ASKV', 'UTF-8');
        if ($name !== '') {
            if (!$sign) {
                $this->drawTextToBox(
                    116.5 + 0.5,
                    40.1 + 0.5,
                    157,
                    50.0 - 0.5,
                    $name,
                    'L',
                    'M',
                    0.1,
                    6
                );
            }

            $this->drawTextToBox(
                53 + 0.5,
                251.4 + 0.5,
                131 - 0.5,
                261.9 - 0.5,
                $name,
                'L',
                'M',
                0.1,
                6
            );
        }

        return $this;
    }

    public function setIndividualNumber(string $numbers): self
    {
        $numbers = mb_convert_kana($numbers, 'n', 'UTF-8');
        $length = min(12, mb_strlen($numbers, 'UTF-8'));
        for ($i = 0; $i < $length; ++$i) {
            $number = mb_substr($numbers, $i, 1, 'UTF-8');
            if (preg_match('/\A[0-9]\z/', $number)) {
                $this->drawTextToBox(
                    116.5 + 0.5 + (167 - 116.5) / 12 * $i,
                    50,
                    116.5 - 0.5 + (167 - 116.5) / 12 * ($i + 1),
                    56.5,
                    $number,
                    'C',
                    'M',
                    0.1,
                    10,
                    'ocrb_aizu_1_1'
                );
            }
        }
        return $this;
    }

    public function setSex(bool $isMale): self
    {
        assert($this->pdf !== null);

        $size = 2.9;
        $this->pdf->SetFont('ipaexm', '', self::mm2pt($size));
        list($width,) = $this->calcTextSize('男');
        $size = 4.35;
        $this->drawTextToBox(
            ($isMale ? 133 : 147.5) + $width / 2,
            56.5,
            ($isMale ? 133 : 147.5) + $width / 2,
            61.1,
            '◯',
            'C',
            'M',
            $size,
            $size
        );
        return $this;
    }

    public function setBirthday(DateTimeImmutable $date): self
    {
        if ($formatted = $this->formatDate($date)) {
            $this->drawTextToBox(
                116.5 + 0.5,
                61.4 + 0.5,
                167 - 0.5,
                69.8 - 0.5,
                $formatted,
                'L',
                'M',
                0.1,
                3.5
            );
        }
        return $this;
    }

    public function setKifuData(DateTimeImmutable $date, int $amount): self
    {
        if ($formatted = $this->formatDate($date)) {
            $this->drawTextToBox(38, 139.4, 102.2, 144.8, $formatted, 'C', 'M', 0.1, 2.9);
            $this->renderHeading($date);
        }

        $this->drawTextToBox(102.2, 139.4, 167, 144.8, (string)$amount, 'C', 'M', 0.1, 0, 'ocrb_aizu_1_1');

        return $this;
    }

    private function renderHeading(DateTimeImmutable $date): void
    {
        assert($this->pdf !== null);

        $size = 3.7;
        $left = ($year = $this->formatDate($date, true))
            ? sprintf('%s寄附分', $year)
            : '';
        $center = "市町村民税\n道府県民税";
        $right = '寄附金税額控除に係る申告特例申請書';
        $this->pdf->SetFont('ipaexm', '', self::mm2pt($size));
        list($leftWidth, ) = $this->calcTextSize($left);
        list($rightWidth, ) = $this->calcTextSize($right);
        $this->drawTextToBox(38, 16, 163.5, 24, $left, 'L', 'M', $size, $size);
        $this->drawTextToBox(
            38 + $leftWidth,
            16,
            163.5 - $rightWidth,
            24,
            $center,
            'C',
            'M',
            $size,
            $size
        );
        $this->drawTextToBox(38, 16, 163.5, 24, $right, 'R', 'M', $size, $size);

        $size = 3.2;
        $right = '寄附金税額控除に係る申告特例申請書受付書';
        $this->pdf->SetFont('ipaexm', '', self::mm2pt($size));
        list($leftWidth, ) = $this->calcTextSize($left);
        list($rightWidth, ) = $this->calcTextSize($right);
        $this->drawTextToBox(42, 229.2, 163, 240.8, $left, 'L', 'M', $size, $size);
        $this->drawTextToBox(
            42 + $leftWidth,
            229.2,
            163 - $rightWidth,
            240.8,
            $center,
            'C',
            'M',
            $size,
            $size
        );
        $this->drawTextToBox(42, 229.2, 163, 240.8, $right, 'R', 'M', $size, $size);
    }

    private function drawLines(): void
    {
        $this->drawBoldLines();
        $this->drawRegularLines();
        $this->drawThinLines();
        $this->drawDottedLines();
        $this->drawDashedLines();
    }

    private function drawBoldLines(): void
    {
        assert($this->pdf !== null);

        $this->pdf->SetLineStyle([
            'width' => self::LINE_WIDTH_BOLD,
            'cap' => 'square',
            'join' => 'square',
            'dash' => 0,
            'color' => $this->black,
        ]);

        $this->pdf->Line(38, 30.8, 90, 30.8);
        $this->pdf->Line(38, 37.1, 167, 37.1);
        $this->pdf->Line(38, 69.8, 167, 69.8);
        $this->pdf->Line(38, 30.8, 38, 69.8);
        $this->pdf->Line(90, 30.8, 90, 37.1);
        $this->pdf->Line(102.2, 37.1, 102.2, 69.8);
        $this->pdf->Line(167, 37.1, 167, 69.8);
    }

    private function drawRegularLines(): void
    {
        assert($this->pdf !== null);

        $this->pdf->SetLineStyle([
            'width' => self::LINE_WIDTH_REGULAR,
            'cap' => 'square',
            'join' => 'square',
            'dash' => 0,
            'color' => $this->black,
        ]);

        $this->pdf->Line(102.2, 30.8, 167, 30.8);
        $this->pdf->Line(102.2, 50, 167, 50);
        $this->pdf->Line(102.2, 56.5, 167, 56.5);
        $this->pdf->Line(38, 61.1, 167, 61.1);
        $this->pdf->Line(52.3, 37.1, 52.3, 69.8);
        $this->pdf->Line(102.2, 30.8, 102.2, 37.1);
        $this->pdf->Line(116.5, 30.8, 116.5, 69.8);
        $this->pdf->Line(167, 30.8, 167, 37.1);

        // マイナンバー
        for ($i = 1; $i < 3; ++$i) {
            $x = 116.5 + $i * (167 - 116.5) / 3;
            $this->pdf->Line($x, 50, $x, 56.5);
        }

        // 1.
        $this->pdf->Line(38, 134, 167, 134);
        $this->pdf->Line(38, 139.4, 167, 139.4);
        $this->pdf->Line(38, 144.8, 167, 144.8);
        $this->pdf->Line(38, 134, 38, 144.8);
        $this->pdf->Line(102.2, 134, 102.2, 144.8);
        $this->pdf->Line(167, 134, 167, 144.8);

        // 2.
        $this->pdf->Line(38, 163.5, 167, 163.5);
        $this->pdf->Line(38, 172.9, 167, 172.9);
        $this->pdf->Line(38, 163.5, 38, 172.9);
        $this->pdf->Line(152.9, 163.5, 152.9, 172.9);
        $this->pdf->Line(167, 163.5, 167, 172.9);

        $this->pdf->Line(38, 202.2, 167, 202.2);
        $this->pdf->Line(38, 211.6, 167, 211.6);
        $this->pdf->Line(38, 202.2, 38, 211.6);
        $this->pdf->Line(152.9, 202.2, 152.9, 211.6);
        $this->pdf->Line(167, 202.2, 167, 211.6);

        // 受付
        $this->pdf->Line(38, 240.8, 167, 240.8);
        $this->pdf->Line(38, 261.9, 167, 261.9);
        $this->pdf->Line(102, 264.3, 167, 264.3);
        $this->pdf->Line(102, 269.8, 167, 269.8);
        $this->pdf->Line(38, 240.8, 38, 261.9);
        $this->pdf->Line(167, 240.8, 167, 261.9);
        $this->pdf->Line(102, 264.3, 102, 269.8);
        $this->pdf->Line(119.7, 264.3, 119.7, 269.8);
        $this->pdf->Line(167, 264.3, 167, 269.8);
    }

    private function drawThinLines(): void
    {
        assert($this->pdf !== null);

        $this->pdf->SetLineStyle([
            'width' => self::LINE_WIDTH_THIN,
            'cap' => 'square',
            'join' => 'square',
            'dash' => 0,
            'color' => $this->black,
        ]);

        // マイナンバー
        for ($i = 1; $i < 12; ++$i) {
            if ($i % 4  == 0) {
                continue;
            }
            $x = 116.5 + $i * (167 - 116.5) / 12;
            $this->pdf->Line($x, 50, $x, 56.5);
        }

        // 受付
        $this->pdf->Line(38, 251.4, 140.2, 251.4);
        $this->pdf->Line(53, 240.8, 53, 261.9);
        $this->pdf->Line(140.2, 240.8, 140.2, 261.9);
    }

    private function drawDottedLines(): void
    {
        assert($this->pdf !== null);

        $this->pdf->SetLineStyle([
            'width' => self::LINE_WIDTH_REGULAR,
            'cap' => 'square',
            'join' => 'square',
            'dash' => '1,2',
            'color' => $this->black,
        ]);

        $this->pdf->Line(102.2, 40.1, 167, 40.1);
    }

    private function drawDashedLines(): void
    {
        assert($this->pdf !== null);

        $this->pdf->SetLineStyle([
            'width' => self::LINE_WIDTH_REGULAR,
            'cap' => 'square',
            'join' => 'square',
            'dash' => 2,
            'color' => $this->black,
        ]);

        $this->pdf->Line(38, 229.2, 80, 229.2);
        $this->pdf->Line(125, 229.2, 167, 229.2);
    }

    private function drawLabels(): void
    {
        assert($this->pdf !== null);

        $this->pdf->SetTextColorArray($this->black);

        $size = 2.9;
        $this->drawTextToBox(38, 37.1, 52.3, 61.4, '住　所', 'C', 'M', $size, $size);
        $this->drawTextToBox(38, 61.4, 52.3, 69.8, '電話番号', 'C', 'M', $size, $size);
        $this->drawTextToBox(102.2, 30.8 - 1, 116.5, 38, '整理番号', 'C', 'M', $size, $size);
        $this->drawTextToBox(102.2, 38 - 0.6, 116.5, 40.1, 'フリガナ', 'C', 'M', $size, $size);
        $this->drawTextToBox(102.2, 40.1, 116.5, 50, '氏　名', 'C', 'M', $size, $size);
        $this->drawTextToBox(102.2, 50, 116.5, 56.5, '個人番号', 'C', 'M', $size, $size);
        $this->drawTextToBox(102.2, 56.5, 116.5, 61.1, '性　別', 'C', 'M', $size, $size);
        $this->drawTextToBox(102.2, 61.1, 116.5, 69.8, '生年月日', 'C', 'M', $size, $size);
        $this->drawTextToBox(133, 56.5, 133, 61.1, '男', 'L', 'M', $size, $size);
        $this->drawTextToBox(147.5, 56.5, 147.5, 61.1, '女', 'L', 'M', $size, $size);

        $this->drawTextToBox(38, 134, 102.2, 139.4, '寄附年月日', 'C', 'M', $size, $size);
        $this->drawTextToBox(102.2, 134, 167, 139.4, '寄附金額', 'C', 'M', $size, $size);
        $this->drawTextToBox(164, 139.6, 164, 139.6, '円', 'L', 'T', 2.4, 2.4);

        $this->drawTextToBox(
            38 + 4,
            163.5,
            152.9,
            172.9,
            '①　地方税法附則第７条第１項（第８項）に規定する申告特例対象寄附者である',
            'L',
            'M',
            $size,
            $size
        );
        $this->drawTextToBox(152.9, 163.5, 167, 172.9, '□', 'C', 'M', $size * 1.2, $size * 1.2);
        $this->drawTextToBox(152.9, 163.5, 167, 172.9, '✓', 'C', 'M', $size * 1.2, $size * 1.2);
        $this->drawTextToBox(
            38 + 4,
            202.2,
            152.9,
            211.6,
            '②　地方税法附則第７条第２項（第９項）に規定する申告特例対象寄附者である',
            'L',
            'M',
            $size,
            $size
        );
        $this->drawTextToBox(152.9, 202.2, 167, 211.6, '□', 'C', 'M', $size * 1.2, $size * 1.2);
        $this->drawTextToBox(152.9, 202.2, 167, 211.6, '✓', 'C', 'M', $size * 1.2, $size * 1.2);
        $this->drawTextToBox(
            80,
            219.2,
            125,
            239.2,
            '（切り取らないでください。）',
            'C',
            'M',
            $size / 1.25,
            $size / 1.25
        );
        $this->drawTextToBox(39.5, 240.8, 51.5, 251.4, '住　　所', 'C', 'M');
        $this->drawTextToBox(39.5, 251.4, 51.5, 261.9, '氏　　名', 'C', 'M');
        $this->drawTextToBox(131, 251.4, 131, 261.9, '殿', 'C', 'M', $size, $size);
        $this->drawTextToBox(
            140.2,
            241.8,
            167,
            241.8,
            '受付日付印',
            'C',
            'T',
            $size / 1.35,
            $size / 1.35
        );
        $this->drawTextToBox(102, 264.3, 119.7, 269.8, '受付団体名', 'C', 'M', $size, $size);

        $this->drawTextToBox(
            37,
            71.3,
            167,
            self::A4_HEIGHT_MM,
            implode("\n", [
                '　「個人番号」欄には、あなたの個人番号（行政手続における特定の個人を識別するための番号の利',
                '用等に関する法律第２条第５項に規定する個人番号をいう。）を記載してください。',
                '',
                '　あなたが支出した地方税法第37条の２（第314条の７）第２項に規定する特例控除対象寄附金',
                '（以下「特例控除対象寄附金」という。）について、同法附則第７条第１項（第８項）の規定によ',
                'る寄附金税額控除に係る申告の特例（以下「申告の特例」という。）の適用を受けようとするとき',
                'は、下の欄に必要な事項を記載してください。',
                '',
                '（注１）　上記に記載した内容に変更があった場合、申告特例対象年の翌年の１月10日までに、申',
                '　　　　告特例申請事項変更届出書を提出してください。',
                '（注２）　申告の特例の適用を受けるために申請を行った者が、地方税法附則第７条第６項（第13',
                '　　　　項）各号のいずれかに該当する場合には、申告特例対象年に支出した全ての寄附金（同項',
                '　　　　第４号に該当する場合にあっては、同号に係るものに限る。）について申告の特例の適用',
                '　　　　は受けられなくなります。その場合に寄附金税額控除の適用を受けるためには、当該寄附',
                '　　　　金税額控除に関する事項を記載した確定申告書又は市町村民税・道府県民税の申告書を提',
                '　　　　出してください。',
            ]),
            'C',
            'T',
            2.8,
            2.8
        );
        $this->drawTextToBox(
            38,
            129,
            38,
            129,
            '１．当団体に対する寄附に関する事項',
            'L',
            'T',
            3.65,
            3.65
        );
        $this->drawTextToBox(
            38,
            154,
            167,
            self::A4_HEIGHT_MM,
            implode("\n", [
                '　申告の特例の適用を受けるための申請は、①及び②に該当する場合のみすることができます。',
                '①及び②に該当する場合、それぞれ下の欄の□にチェックをしてください。',
            ]),
            'C',
            'T',
            2.8,
            2.8
        );
        $this->drawTextToBox(
            38,
            149,
            38,
            149,
            '２．申告の特例の適用に関する事項',
            'L',
            'T',
            3.65,
            3.65
        );
        $this->drawTextToBox(
            38,
            173.5,
            167,
            self::A4_HEIGHT_MM,
            implode("\n", [
                '（注）　地方税法附則第７条第１項（第８項）に規定する申告特例対象寄附者とは、ⅰ及びⅱに該',
                '　　　当すると見込まれる者をいいます。',
            ]),
            'C',
            'T',
            2.8,
            2.8
        );
        $this->drawTextToBox(
            46,
            180.5,
            167,
            201.5,
            implode("\n", [
                'ⅰ　特例控除対象寄附金を支出する年の年分の所得税について所得税法第120条第１項の規定による申',
                '　告書を提出する義務がない者又は同法第121条（第１項ただし書を除く。）の規定の適用を受ける者',
                'ⅱ　特例控除対象寄附金を支出する年の翌年の４月１日の属する年度分の市町村民税・道府県民税に',
                '　ついて、当該寄附金に係る寄附金税額控除の控除を受ける目的以外に、市町村民税・道府県民税の',
                '　申告書の提出（当該申告書の提出がされたものとみなされる確定申告書の提出を含む。）を要しな',
                '　い者',
            ]),
            'C',
            'T'
        );
        $this->drawTextToBox(
            38,
            212,
            167,
            self::A4_HEIGHT_MM,
            implode("\n", [
                '（注）　地方税法附則第７条第２項（第９項）に規定する要件に該当する者とは、この申請を含め',
                '　　　申告特例対象年の１月１日から12月31日の間に申告の特例の適用を受けるための申請を行う',
                '　　　都道府県の知事又は市町村若しくは特別区の長の数が５以下であると見込まれる者をいいま',
                '　　　す。',
            ]),
            'C',
            'T',
            2.8,
            2.8
        );
        $this->drawTextToBox(
            171,
            20.5,
            176,
            self::A4_HEIGHT_MM,
            "第\n五\n十\n五\n号\nの\n五\n様\n式",
            'C',
            'T',
            3.4,
            3.4,
            'ipaexg'
        );
        $this->drawTextToBox(
            171,
            60,
            176,
            self::A4_HEIGHT_MM,
            "︵\n附\n則\n第\n二\n条\nの\n四\n関\n係\n︶",
            'C',
            'T',
            3.4,
            3.4
        );
    }

    private function drawTextToBox(
        float $left,
        float $top,
        float $right,
        float $bottom,
        string $text,
        string $align = 'L',
        string $valign = 'M',
        float $minFontSize = 0.1,
        float $maxFontSize = 0,
        string $fontName = 'ipaexm'
    ): self {
        assert($this->pdf !== null);

        if ($maxFontSize <= 0.1) {
            $maxFontSize = self::pt2mm(10.5);
        }

        $left = (float)number_format($left, 2, '.', '');
        $top = (float)number_format($top, 2, '.', '');
        $right = (float)number_format($right, 2, '.', '');
        $bottom = (float)number_format($bottom, 2, '.', '');
        $width = (float)number_format($right - $left, 2, '.', '');
        $height = (float)number_format($bottom - $top, 2, '.', '');
        $this->pdf->SetFont($fontName, '', 0);
        $fontSize = $this->calcFontSize($text, $width, $height, $maxFontSize, $minFontSize);
        $this->pdf->SetFont($fontName, '', self::mm2pt($fontSize));
        list ($textWidth, $textHeight) = $this->calcTextSize($text);
        $this->pdf->SetXY(
            (function () use ($align, $left, $right, $width, $textWidth): float {
                switch ($align) {
                    case 'C':
                        return $left + ($width / 2 - $textWidth / 2);
                    case 'R':
                        return $right - $textWidth;
                    case 'L':
                    default:
                        return $left;
                }
            })(),
            $valign === 'T'
                ? $top
                : ($top + ($height / 2 - $textHeight / 2))
        );
        $this->pdf->MultiCell(
            0,
            $textHeight,
            $text,
            0,      // border
            'L',    // align
            false,  // fill
            0       // ln
        );
        return $this;
    }

    private function calcTextSize(string $text): array
    {
        assert($this->pdf !== null);
        $lines = explode("\n", $text);
        $this->pdf->SetXY(0, 0);
        return [
            // width
            max(array_map(
                function (string $text): float {
                    assert($this->pdf !== null);
                    return (float)$this->pdf->GetStringWidth($text);
                },
                $lines
            )),
            // height
            array_reduce(
                $lines,
                function (float $carry, string $item): float {
                    assert($this->pdf !== null);
                    return $carry + $this->pdf->GetStringHeight(0, $item, false, false);
                },
                0.0
            ),
        ];
    }

    private function calcFontSize(
        string $text,
        float $width,
        float $height,
        float $maxFontSize = 20.0,
        float $minFontSize = 0.1
    ): float {
        assert($this->pdf !== null);

        for ($i = 0;; ++$i) {
            $fontSize = (float)number_format($maxFontSize - 0.1 * $i, 2, '.', '');
            if ($fontSize <= $minFontSize || $fontSize <= 0) {
                return $minFontSize;
            }
            $this->pdf->SetFont('', '', self::mm2pt($fontSize));
            list($textWidth, $textHeight) = $this->calcTextSize($text);
            if ($textWidth <= $width && $textHeight <= $height) {
                return $fontSize;
            }
        }
        return $minFontSize;
    }

    private function formatDate(DateTimeImmutable $date, bool $isYearOnly = false): ?string
    {
        if (!$year = $this->formatYear($date)) {
            return null;
        }

        if ($isYearOnly) {
            return $year;
        }

        return vsprintf('%s%s月%s日', [
            $year,
            self::num2str((int)$date->format('n')),
            self::num2str((int)$date->format('j')),
        ]);
    }

    private function formatYear(DateTimeImmutable $date): ?string
    {
        if ($this->useWesternYear) {
            return sprintf('%s年', self::num2str((int)$date->format('Y')));
        }

        if (!$_ = Era::calcYear($date)) {
            return null;
        }

        list($era, $year) = $_;
        return vsprintf('%s%s年', [
            $era->name,
            self::num2str($year, true),
        ]);
    }

    private static function mm2pt(float $mm): float
    {
        return $mm * 72 / 25.4;
    }

    private static function pt2mm(float $pt): float
    {
        return $pt * 25.4 / 72;
    }

    private static function num2str(int $num, bool $isYear = false): string
    {
        if ($num >= 10) {
            return (string)$num;
        }

        if ($num === 1 && $isYear) {
            return '元';
        }

        return mb_convert_kana((string)$num, 'N', 'UTF-8');
    }
}
