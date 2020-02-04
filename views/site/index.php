<?php

declare(strict_types=1);

use app\models\Pdf2016Form as Form;
use app\models\Prefecturer;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

$this->title = 'onestop.fetus.jp';

$now = ((int)$_SERVER['REQUEST_TIME'] ?? time());
$thisYear = (int)date('Y', $now);
?>
<main>
  <h1 class="sr-only">
    <span class="font-script"><?= Html::encode(Yii::$app->name); ?></span>
  </h1>
  <p>
    ふるさと納税ワンストップ特例申請書をそれなりに埋めたPDFを生成するだけのサイトです。<br>
    ここで入力した情報は一切保存されていません（<a href="https://github.com/fetus-hina/onestop.fetus.jp/" rel="external" target="_blank">ソースコード</a>）。<br>
    一文字でも手書きを減らしたい自分のためのフォームなので細かいことはあまり考えてないです。
  </p>

  <script type="application/json" id="fake-data"><?= Json::encode($fake) ?></script>
  <div class="text-right">
    <button type="button" class="btn btn-outline-secondary" id="use-fake-data" data-data="#fake-data">
      サンプルデータ
    </button>
<?php $this->registerJs('$("#use-fake-data").fakeData();') ?>
  </div>

  <?php $form = ActiveForm::begin(); echo "\n"; ?>
    
  <fieldset>
    <legend>エンベロープ</legend>
    <div class="form-group">
      <label for="dummy">投函予定日</label><br>
      <div class="form-inline">
        <?= $form->field($model, 'post_year')
          ->dropDownList(array_map(
            function (int $v): string {
              return $v . '年';
            },
            array_combine(
              range(2008, $thisYear + 1),
              range(2008, $thisYear + 1)
            )
          ))
          ->label(false) . "\n"
        ?>
        <?= $form->field($model, 'post_month')
          ->dropDownList(array_map(
            function (int $v): string {
              return $v . '月';
            },
            array_combine(range(1, 12), range(1, 12))
          ))
          ->label(false) . "\n"
        ?>
        <?= $form->field($model, 'post_day')
          ->dropDownList(array_map(
            function (int $v): string {
              return $v . '日';
            },
            array_combine(range(1, 31), range(1, 31))
          ))
          ->label(false) . "\n"
        ?>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend>寄付に関する情報</legend>
    <div class="form-group">
      <label for="dummy">寄付年月日</label><br>
      <div class="form-inline">
        <?= $form->field($model, 'kifu_year')
          ->dropDownList(array_map(
            function (int $v): string {
              return $v . '年';
            },
            array_combine(
              range(2008, $thisYear),
              range(2008, $thisYear)
            )
          ))
          ->label(false) . "\n"
        ?>
        <?= $form->field($model, 'kifu_month')
          ->dropDownList(array_map(
            function (int $v): string {
              return $v . '月';
            },
            array_combine(range(1, 12), range(1, 12))
          ))
          ->label(false) . "\n"
        ?>
        <?= $form->field($model, 'kifu_day')
          ->dropDownList(array_map(
            function (int $v): string {
              return $v . '日';
            },
            array_combine(range(1, 31), range(1, 31))
          ))
          ->label(false) . "\n"
        ?>
      </div>
    </div>
    <?= $form->field($model, 'local_gov')
      ->textInput(['placeholder' => '例: 夕張市'])
      ->hint('自治体名のみを入力します') . "\n"
    ?>
    <?= $form->field($model, 'kifu_amount')
      ->textInput(['type' => 'number', 'placeholder' => '例: 1000000'])
      ->hint('数字のみを入力します') . "\n"
    ?>
  </fieldset>
  <fieldset>
    <legend>寄付者（あなた）の個人情報</legend>
    <?= $form
      ->field($model, 'zipcode', [
        'inputTemplate' => Html::tag(
          'div',
          implode('', [
            '{input}',
            Html::tag(
              'div',
              implode('', [
                Html::button(Html::encode('住所入力'), [
                  'id' => Html::getInputId($model, 'zipcode') . '--zipquerybtn',
                  'class' => 'btn btn-info',
                ]),
                Html::button(Html::tag('span', '', ['class' => 'far fa-question-circle']), [
                  'class' => 'btn btn-outline-info',
                  'data' => [
                    'toggle' => 'modal',
                    'target' => sprintf('#%s--apiinfomodal', Html::getInputId($model, 'zipcode')),
                  ],
                ]),
              ]),
              ['class' => 'input-group-append']
            ),
          ]),
          ['class' => 'input-group']
        ),
      ])
      ->textInput(['placeholder' => '例: 1234567'])
      ->hint('数字のみを入力します') . "\n"
    ?>
<?php $this->registerJs(vsprintf('$(%s).zipSearch(%s, %s);', [
  Json::encode('#' . Html::getInputId($model, 'zipcode') . '--zipquerybtn'),
  Json::encode('#' . Html::getInputId($model, 'zipcode')),
  implode(', ', [
    Json::encode('#' . Html::getInputId($model, 'zipcode') . '--choice'),
    Json::encode('#' . Html::getInputId($model, 'zipcode') . '--error'),
    Json::encode([
      '#' . Html::getInputId($model, 'pref_id') => 'prefcode',
      '#' . Html::getInputId($model, 'city') => 'address2',
      '#' . Html::getInputId($model, 'address1') => 'address3',
      '#' . Html::getInputId($model, 'address2') => null,
    ]),
 ]),
])) ?>
<?php // API利用警告 {{{ ?>
    <?= Html::beginTag('div', [
      'aria-hidden' => 'true',
      'class' => 'modal fade',
      'id' => sprintf('%s--apiinfomodal', Html::getInputId($model, 'zipcode')),
      'role' => 'dialog',
      'tabindex' => '-1',
    ]) . "\n" ?>
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">住所入力機能について</h5>
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true"><span class="fas fa-times"></span></span>
            </button>
          </div>
          <div class="modal-body">
            郵便番号に対応する住所を取得するため、<strong>株式会社アイビスの提供する<a href="http://zipcloud.ibsnet.co.jp/doc/api" target="_blank">検索API</a>を利用しています。</strong><br>
            <br>
            住所入力ボタンを押すと、このサイトのサーバを経由して問い合わせが行われます。<br>
            当該サーバへは郵便番号のみが送信されますので、あなたの個人情報としての「価値」は低い状態になっているはずですが、その郵便番号の人がこのサービス（<?= Html::encode(Yii::$app->name) ?>）を利用していることは伝わります。<br>
            住所入力ボタンを押した場合、あなたはこの内容を理解しているものとみなします。<br>
            <br>
            なお、このサイトの運営者と、当該サービスの運用者には一切の関係はありません。
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-primary" data-dismiss="modal">閉じる</button>
          </div>
        </div>
      </div>
    </div>
<?php // }}} ?>
<?php // 複数ヒットする住所の選択 {{{ ?>
    <?= Html::beginTag('div', [
      'aria-hidden' => 'true',
      'class' => 'modal fade',
      'id' => sprintf('%s--choice', Html::getInputId($model, 'zipcode')),
      'role' => 'dialog',
      'tabindex' => '-1',
    ]) . "\n" ?>
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">複数の住所が該当しました</h5>
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true"><span class="fas fa-times"></span></span>
            </button>
          </div>
          <div class="modal-body p-0">
            <div class="list-group overflow-auto" style="max-height:70vh">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">閉じる</button>
          </div>
        </div>
      </div>
    </div>
<?php // }}} ?>
<?php // エラー表示 {{{ ?>
    <?= Html::beginTag('div', [
      'aria-hidden' => 'true',
      'class' => 'modal fade',
      'id' => sprintf('%s--error', Html::getInputId($model, 'zipcode')),
      'role' => 'dialog',
      'tabindex' => '-1',
    ]) . "\n" ?>
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">エラー - 住所入力</h5>
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true"><span class="fas fa-times"></span></span>
            </button>
          </div>
          <div class="modal-body">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-dismiss="modal">閉じる</button>
          </div>
        </div>
      </div>
    </div>
<?php // }}} ?>
    <?= $form->field($model, 'pref_id')
      ->dropDownList(ArrayHelper::map(
        Prefecturer::find()->orderBy('[[id]] ASC')->asArray()->all(),
        'id',
        'name'
      )) . "\n"
    ?>
    <?= $form->field($model, 'city')
      ->textInput() . "\n"
    ?>
    <?= $form->field($model, 'address1')
      ->textInput() . "\n"
    ?>
    <?= $form->field($model, 'address2')
      ->textInput() . "\n"
    ?>
    <?= $form->field($model, 'phone')
      ->textInput(['placeholder' => '例: 090-1234-5678'])
      ->hint('数字とハイフンで入力します') . "\n"
    ?>
    <?= $form->field($model, 'name')
      ->textInput(['placeholder' => '例: 相沢　陽菜（省略可能）'])
      ->hint('漢字の名前はあとで手書きしたほうがいいかもしれません') . "\n"
    ?>
    <?= $form->field($model, 'name_kana')
      ->textInput(['placeholder' => '例: アイザワ　ヒナ']) . "\n"
    ?>
    <?= $form->field($model, 'sex')
      ->dropDownList([
        Form::SEX_MALE   => '男性',
        Form::SEX_FEMALE => '女性',
      ])
      ->hint('その他の性の人は自治体に問い合わせてください') . "\n"
    ?>
    <div class="form-group">
      <label for="dummy">生年月日</label><br>
      <div class="form-inline">
        <?= $form->field($model, 'birth_year')
          ->dropDownList(array_map(
            function (int $v): string {
              return $v . '年';
            },
            array_combine(
              range(1903, $thisYear),
              range(1903, $thisYear)
            )
          ))
          ->label(false) . "\n"
        ?>
        <?= $form->field($model, 'birth_month')
          ->dropDownList(array_map(
            function (int $v): string {
              return $v . '月';
            },
            array_combine(range(1, 12), range(1, 12))
          ))
          ->label(false) . "\n"
        ?>
        <?= $form->field($model, 'birth_day')
          ->dropDownList(array_map(
            function (int $v): string {
              return $v . '日';
            },
            array_combine(range(1, 31), range(1, 31))
          ))
          ->label(false) . "\n"
        ?>
      </div>
    </div>
    <?= $form->field($model, 'individual_number')
      ->textInput(['placeholder' => '123412341234'])
      ->hint('数字のみを入力します。テスト用ダミー: <span id="dummy-mynumber"></span>') . "\n"
    ?>
<?php $this->registerJs('$("#dummy-mynumber").dummyMyNumber();') ?>
  </fieldset>
  <fieldset>
    <legend>特例が利用できる人かチェック</legend>
    <p>詳しい内容は各種解説ページを見てください。</p>
    <?= $form->field($model, 'checkbox1')
      ->checkbox()
      ->hint('確定申告を行う必要がなく、医療控除等もないことを申告します。') . "\n"
    ?>
    <?= $form->field($model, 'checkbox2')
      ->checkbox()
      ->hint('同年中の寄附先が5自治体以下であることを申告します。') . "\n"
    ?>
  </fieldset>

  <div class="form-group">
    <button type="submit" class="btn btn-primary">
      <span class="fas fa-fw fa-file-pdf"></span>
      PDF作成
      <span class="fas fa-fw fa-download"></span>
    </button>
  </div>

  <?php ActiveForm::end(); echo "\n"; ?>
</main>
