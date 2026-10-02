<?php

namespace tests\models;

use nedarta\seo\behaviors\SeoFields;
use yii\db\ActiveRecord;

class TestProduct extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%test_product}}';
    }

    public function behaviors()
    {
        return [
            'seo' => [
                'class' => SeoFields::class,
            ],
        ];
    }
}
