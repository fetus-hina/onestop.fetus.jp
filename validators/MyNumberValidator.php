<?php

declare(strict_types=1);

namespace app\validators;

use Yii;
use jp3cki\mynumber\MyNumber;
use yii\validators\Validator;

class MyNumberValidator extends Validator
{
    /** @return void */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = '{attribute} は正しい個人番号ではありません。';
        }
    }

    /** @return void */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!is_scalar($value)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }
        if (!MyNumber::isValid($value)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }
    }

    protected function validateValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return [Yii::t('yii', '{attribute} is invalid.'), []];
        }
        if (!MyNumber::isValid($value)) {
            return [$this->message, []];
        }
        return null;
    }
}
