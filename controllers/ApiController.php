<?php

declare(strict_types=1);

namespace app\controllers;

use Curl\Curl;
use Exception;
use Throwable;
use Yii;
use app\models\Pdf2016Form;
use yii\base\DynamicModel;
use yii\base\Model;
use yii\bootstrap5\Html;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

use function array_values;
use function implode;
use function is_array;
use function substr;
use function vsprintf;

class ApiController extends Controller
{
    /** @return void */
    public function init()
    {
        parent::init();
        Yii::$app->language = 'en-US';
        Yii::$app->timeZone = 'Etc/UTC';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => VerbFilter::class,
                'actions' => [
                    'fake-data' => ['get'],
                    'postal-code' => ['get', 'post'],
                ],
            ],
        ];
    }

    public function actionFakeData(): Response
    {
        $fakeModel = Yii::createObject(Pdf2016Form::class)->faker();
        $fakeData = [];
        foreach ($fakeModel->attributes as $k => $v) {
            if ($k === 'sign' || $k === 'use_western_year') {
                continue;
            }

            if (substr((string)$k, 0, 8) === 'checkbox') {
                $v = (bool)$v;
            }

            $fakeData[Html::getInputId($fakeModel, (string)$k)] = $v;
        }

        $resp = Yii::$app->response;
        $resp->format = Response::FORMAT_JSON;
        $resp->setStatusCode(200, 'OK');
        $resp->headers->set('Content-Type', 'application/json; charset=UTF-8');
        $resp->headers->set('Content-Language', 'ja');
        $resp->data = $fakeData;
        return $resp;
    }

    public function actionPostalCode(): Response
    {
        $req = Yii::$app->request;
        $model = DynamicModel::validateData(
            [
                'code' => ($req->isPost ? $req->post('code') : null) ?? $req->get('code'),
            ],
            [
                [['code'], 'required'],
                [['code'], 'string',
                    'skipOnError' => true,
                    'min' => 7,
                    'max' => 7,
                ],
                [['code'], 'match',
                    'skipOnError' => true,
                    'pattern' => '/^[0-9]{7}$/',
                ],
            ],
        );
        if ($model->hasErrors()) {
            return $this->makeInputError($model, ['api/postal-code']);
        }

        $apiResp = $this->requestPostalCodeApi($model->code); // @phpstan-ignore-line
        if ((int)($apiResp['status'] ?? 500) !== 200) {
            return $this->makeResponseError($apiResp, ['api/postal-code']);
        }

        $resp = Yii::$app->response;
        $resp->format = Response::FORMAT_JSON;
        $resp->setStatusCode(200, 'OK');
        $resp->headers->set('Content-Type', 'application/json; charset=UTF-8');
        $resp->headers->set('Content-Language', 'ja');
        $resp->data = $apiResp['results'] ?? null ?: [];
        return $resp;
    }

    private function requestPostalCodeApi(string $postalCode): array
    {
        $curl = new Curl();
        $curl->setUserAgent(vsprintf('%s (%s)', [
            'OnestopFetusJP',
            implode('; ', [
                '+https://onestop.fetus.jp/',
                '+https://github.com/fetus-hina/onestop.fetus.jp',
            ]),
        ]));
        $curl->setReferrer(Url::to(['site/index'], true));
        $curl->jsonDecoder = false;
        $curl->xmlDecoder = false;
        $curl->get('https://zip-cloud.appspot.com/api/search', [
            'zipcode' => $postalCode,
            'limit' => '100',
        ]);
        if ($curl->curlError) {
            return [
                'message' => vsprintf('cURLエラー: #%d, %s', [
                    $curl->curlErrorCode,
                    $curl->curlErrorMessage,
                ]),
                'results' => null,
                'status' => 500,
            ];
        } elseif ($curl->httpError) {
            return [
                'message' => vsprintf('リモートAPIエラー: #%d, %s', [
                    $curl->httpStatusCode,
                    $curl->httpErrorMessage,
                ]),
                'results' => null,
                'status' => $curl->httpStatusCode,
            ];
        }

        try {
            $result = Json::decode($curl->rawResponse, true);
            return is_array($result)
                ? $result
                : throw new Exception('JSON decoded, but not expected value');
        } catch (Throwable $e) {
            return [
                'message' => vsprintf('JSONデコードエラー: #%d, %s', [
                    $e->getCode(),
                    $e->getMessage(),
                ]),
                'results' => null,
                'status' => 500,
            ];
        }
    }

    private function makeInputError(Model $model, array $url): Response
    {
        $errors = $model->getErrors();
        $firstError = array_values(array_values($errors)[0])[0];
        $invalidParams = [];
        foreach ($errors as $key => $strings) {
            foreach ($strings as $string) {
                $invalidParams[] = [
                    'name' => $key,
                    'reason' => $string,
                ];
            }
        }

        $resp = Yii::$app->response;
        $resp->format = Response::FORMAT_JSON;
        $resp->setStatusCode(400, 'Bad Request');
        $resp->headers->set('Content-Type', 'application/problem+json; charset=UTF-8');
        $resp->headers->set('Content-Language', 'en');
        $resp->data = [
            'type' => 'about:black',
            'title' => 'Your request parameters didn\'t validate.',
            'status' => 400,
            'detail' => $firstError,
            'instance' => Url::to($url, true),
            'invalid-params' => $invalidParams,
        ];
        return $resp;
    }

    private function makeResponseError(array $apiResp, array $url): Response
    {
        $resp = Yii::$app->response;
        $resp->format = Response::FORMAT_JSON;
        $resp->setStatusCode(503, 'Service Unavailable');
        $resp->headers->set('Content-Type', 'application/problem+json; charset=UTF-8');
        $resp->headers->set('Content-Language', 'ja');
        $resp->data = [
            'type' => 'http://zipcloud.ibsnet.co.jp/doc/api',
            'title' => 'リモートAPIエラー',
            'status' => 503,
            'detail' => $apiResp['message'] ?? '(不明なエラー)',
            'instance' => Url::to($url, true),
        ];
        return $resp;
    }
}
