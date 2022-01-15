<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\Pdf2016Form;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;

class SiteController extends Controller
{
    /**
     * @return Array<string, string|array>
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => [
                    'clear-opcache',
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'ips' => [
                            '127.0.0.0/8',
                            '::1',
                        ],
                    ],
                ],
            ],
            'verb' => [
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

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function actionClearOpcache(): string
    {
        $r = Yii::$app->response;
        $r->format = Response::FORMAT_RAW;
        $r->headers->set('Content-Type', 'text/plain; charset=UTF-8');

        if (function_exists('opcache_reset')) {
            opcache_reset();
            return 'ok';
        }

        $r->statusCode = 501;
        return 'not ok';
    }
}
