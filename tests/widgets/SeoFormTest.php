<?php

namespace tests\widgets;

use nedarta\seo\widgets\SeoForm;
use PHPUnit\Framework\TestCase;
use tests\models\TestProduct;

class SeoFormTest extends TestCase
{
    public function testWidgetRequiresModel()
    {
        // yii\base\BaseObject::__construct() calls init() itself, so the
        // exception is thrown by `new SeoForm()` - expectException() has to
        // be set up before that call, and there's nothing left to assert
        // once the object exists, so the previous `$widget->init()` call
        // never actually ran (or was reached).
        $this->expectException(\yii\base\InvalidConfigException::class);

        new SeoForm();
    }

    public function testWidgetRequiresForm()
    {
        $this->expectException(\yii\base\InvalidConfigException::class);

        new SeoForm([
            'model' => new TestProduct(),
        ]);
    }
}
