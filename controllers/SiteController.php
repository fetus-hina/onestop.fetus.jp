<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\helpers\TypeHelper;
use app\models\Pdf2016Form;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Application;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Request;
use yii\web\Response;

use function function_exists;
use function opcache_reset;

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
        $app = TypeHelper::instanceOf(Yii::$app, Application::class);
        $model = Yii::createObject(Pdf2016Form::class);
        $req = TypeHelper::instanceOf($app->request, Request::class);
        if ($req->isPost) {
            // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
            if ($model->load($_POST) && $model->validate()) {
                $resp = TypeHelper::instanceOf($app->response, Response::class);
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
        $r = TypeHelper::instanceOf(
            TypeHelper::instanceOf(Yii::$app, Application::class)->response,
            Response::class,
        );
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
