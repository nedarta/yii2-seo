<?php

namespace tests\behaviors;

use nedarta\seo\behaviors\SeoFields;
use nedarta\seo\models\Seo;
use PHPUnit\Framework\TestCase;
use tests\models\TestProduct;

class SeoFieldsTest extends TestCase
{
    public function testModelNameDefaultsToFullClassName()
    {
        $product = new TestProduct();

        $behavior = new SeoFields();
        $behavior->attach($product);

        $this->assertSame(TestProduct::class, $behavior->getModelName());
    }

    public function testCustomModelName()
    {
        $product = new TestProduct();

        $behavior = new SeoFields([
            'modelName' => 'catalog_product',
        ]);
        $behavior->attach($product);

        $this->assertSame('catalog_product', $behavior->getModelName());
    }

    public function testSeoReturnsUnsavedModelForNewOwner()
    {
        $product = new TestProduct();

        $behavior = new SeoFields();
        $behavior->attach($product);

        $seo = $behavior->getSeo();

        $this->assertInstanceOf(Seo::class, $seo);
        $this->assertTrue($seo->getIsNewRecord());
        $this->assertSame(TestProduct::class, $seo->model_name);
    }
}
