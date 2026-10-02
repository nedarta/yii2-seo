<?php

namespace tests\behaviors;

use nedarta\seo\behaviors\SeoFields;
use nedarta\seo\models\Seo;
use PHPUnit\Framework\TestCase;
use tests\models\TestProduct;
use Yii;

/**
 * Exercises SeoFields against the real (in-memory SQLite) schema set up in
 * tests/bootstrap.php - SeoFieldsTest only checks configuration and never
 * touches the database.
 *
 * Only the language-independent columns (meta_index, redirect_301) are
 * asserted on here; the translated attributes go through the separately
 * installed lav45/yii2-translated-behavior package, which this suite
 * doesn't otherwise exercise.
 */
class SeoFieldsIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Yii::$app->db->createCommand('DELETE FROM {{%seo}}')->execute();
        // Each test starts as a plain GET with no body, so a POST/PUT/PATCH
        // simulated in one test can't leak into the next and trigger an
        // unwanted saveSeo() there.
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Yii::$app->request->setBodyParams([]);
    }

    private function simulatePost(array $body): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->request->setBodyParams($body);
    }

    public function testSaveSeoPersistsOnInsert()
    {
        $this->simulatePost([
            'Seo' => [
                'meta_index' => 'noindex',
                'redirect_301' => '/bikes/red',
            ],
        ]);

        $product = new TestProduct(['name' => 'Red bicycle']);
        $product->save(false);

        $seo = Seo::findOne(['item_id' => $product->id, 'model_name' => TestProduct::class]);

        $this->assertNotNull($seo);
        $this->assertSame('noindex', $seo->meta_index);
        $this->assertSame('/bikes/red', $seo->redirect_301);
    }

    public function testSaveSeoPersistsOnUpdate()
    {
        $product = new TestProduct(['name' => 'Blue bicycle']);
        $product->save(false);

        $this->simulatePost(['Seo' => ['meta_index' => 'noindex']]);
        $product->name = 'Blue bicycle (updated)';
        $product->save(false);

        $seo = Seo::findOne(['item_id' => $product->id, 'model_name' => TestProduct::class]);

        $this->assertNotNull($seo);
        $this->assertSame('noindex', $seo->meta_index);
    }

    public function testDeleteSeoRemovesTheRow()
    {
        $this->simulatePost(['Seo' => ['meta_index' => 'noindex']]);

        $product = new TestProduct(['name' => 'Green bicycle']);
        $product->save(false);

        $this->assertNotNull(Seo::findOne(['item_id' => $product->id, 'model_name' => TestProduct::class]));

        $product->delete();

        $this->assertNull(Seo::findOne(['item_id' => $product->id, 'model_name' => TestProduct::class]));
    }

    public function testGetSeoFindsARowInsertedOutOfBand()
    {
        $product = new TestProduct(['name' => 'Yellow bicycle']);
        $product->save(false);

        // A row that appeared without going through this behavior at all
        // (e.g. a concurrent request, or direct model access) - getSeo()
        // must find it rather than building a second, colliding instance.
        $out = new Seo([
            'item_id' => $product->id,
            'model_name' => TestProduct::class,
            'meta_index' => 'index',
        ]);
        $out->save(false);

        /** @var SeoFields $behavior */
        $behavior = $product->getBehavior('seo');
        $seo = $behavior->getSeo();

        $this->assertFalse($seo->getIsNewRecord());
        $this->assertSame($out->id, $seo->id);
    }
}
