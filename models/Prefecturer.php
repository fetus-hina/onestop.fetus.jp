<?php

declare(strict_types=1);

namespace app\models;

use Yii;

/**
 * This is the model class for table "prefecturer".
 *
 * @property integer $id
 * @property string $name
 */
class Prefecturer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'prefecturer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }
}
