<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\Pdf2016Form;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;

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
                'class' => ErrorAction::class,
            ],
        ];
    }

    /** @return string|Response */
    public function actionIndex()
    {
        $model = Yii::createObject(Pdf2016Form::class);
        if (Yii::$app->request->isPost) {
            if ($model->load($_POST) && $model->validate()) {
                $resp = Yii::$app->response;
                $resp->sendContentAsFile($model->createPdf(), 'onestop.pdf', [
                    'mimeType' => 'application/pdf',
                    'inline' => false,
                ]);
                return $resp;
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
