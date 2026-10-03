<?php

namespace tests\models;

use nedarta\seo\models\Seo;
use nedarta\seo\models\SeoLang;
use PHPUnit\Framework\TestCase;

class SeoTest extends TestCase
{
    public function testTableNames()
    {
        $this->assertSame('{{%seo}}', Seo::tableName());
        $this->assertSame('{{%seo_lang}}', SeoLang::tableName());
    }

    public function testTranslatedAttributesAreConfigured()
    {
        $seo = new Seo();

        $behavior = $seo->getBehavior('lav45\\translate\\TranslatedBehavior');

        $this->assertNotNull($behavior);
        $this->assertSame('seoLangs', $behavior->translateRelation);
        // TranslatedBehavior::setTranslateAttributes() normalizes a plain
        // list into an attributeName => attributeName map (the map form
        // supports aliasing, e.g. 'titleLang' => 'title'), so a same-named
        // list comes back out keyed by itself rather than as a 0-indexed
        // array.
        $this->assertSame(
            [
                'title' => 'title',
                'description' => 'description',
                'keywords' => 'keywords',
                'h1' => 'h1',
                'text' => 'text',
            ],
            $behavior->translateAttributes
        );
    }

    public function testRulesContainLanguageIndependentFields()
    {
        $seo = new Seo();

        $attributes = array_merge(
            ...array_map(
                static function ($rule) {
                    return (array) ($rule[0] ?? []);
                },
                $seo->rules()
            )
        );

        $this->assertContains('meta_index', $attributes);
        $this->assertContains('redirect_301', $attributes);
    }
}
