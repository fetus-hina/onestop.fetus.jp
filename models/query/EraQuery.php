<?php

declare(strict_types=1);

namespace app\models\query;

use DateTimeInterface;
use Yii;
use yii\db\ActiveQuery;

class EraQuery extends ActiveQuery
{
    /** @return void */
    public function init()
    {
        parent::init();
        $this->andWhere(['<>', '{{era}}.[[enabled]]', 0]);
        $this->orderBy(['{{era}}.[[start_date]]' => SORT_DESC]);
    }

    public function andByDate(DateTimeInterface $date): self
    {
        $this->andWhere(['<=', '{{era}}.[[start_date]]', $date->getTimestamp()]);
        return $this;
    }
}
