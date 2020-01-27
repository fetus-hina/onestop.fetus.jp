<?php

namespace app\controllers;

use Yii;
use app\models\Pdf2016Form;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Controller;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => [ 'get', 'post' ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $model = Yii::createObject(Pdf2016Form::class);
        if (Yii::$app->request->isPost) {
            if ($model->load($_POST) && $model->validate()) {
                $model->createPdf();
                echo "OK";
                exit;
            }
        }

        $fakeModel = Yii::createObject(Pdf2016Form::class)->faker();
        $fakeData = [];
        foreach ($fakeModel->attributes as $k => $v) {
            if (substr($k, 0, 8) === 'checkbox') {
                $v = $v ? true : false;
            }

            $fakeData['#' . Html::getInputId($fakeModel, $k)] = $v;
        }

        return $this->render('index', [
            'model' => $model,
            'fake' => $fakeData,
        ]);
    }
}
