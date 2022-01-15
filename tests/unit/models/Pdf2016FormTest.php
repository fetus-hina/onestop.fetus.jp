<?php

declare(strict_types=1);

namespace tests\unit\models;

use Codeception\Test\Unit;
use UnitTester;
use Yii;
use app\models\Pdf2016Form;
use app\models\Prefecturer;

class Pdf2016FormTest extends Unit
{
    protected UnitTester $tester;

    public function testFaker(): void
    {
        $model = Yii::createObject(Pdf2016Form::class);
        $this->assertInstanceOf(Pdf2016Form::class, $model->faker());

        $this->assertTrue($model->validate());

        $pref = $model->prefecturer;
        $this->assertInstanceOf(Prefecturer::class, $pref);
    }

    public function testValidate(): void
    {
    }

    public function testCreatePdf(): void
    {
        $model = Yii::createObject(Pdf2016Form::class)->faker();

        // 投函日を令和2年1月1日に設定する（「元年」を避ける）
        $model->post_year = 2020;
        $model->post_month = 1;
        $model->post_day = 1;

        // 誕生日を平成元年12月31日に設定する（「元年」を使う）
        $model->birth_year = 1989;
        $model->birth_month = 12;
        $model->birth_day = 31;

        $binary = $model->createPdf();
        $this->assertIsString($binary);
        $this->assertGreaterThan(102400, mb_strlen($binary, '8bit'));

        $this->assertEquals('%PDF-1.', mb_substr($binary, 0, 7, '8bit'));
    }
}
