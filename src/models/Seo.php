<?php

namespace nedarta\seo\models;

use lav45\translate\TranslatedBehavior;
use lav45\translate\TranslatedTrait;
use yii\db\ActiveRecord;

/**
 * SEO metadata associated with an application model.
 *
 * @property int $id
 * @property int $item_id
 * @property string $model_name
 * @property string|null $meta_index
 * @property string|null $redirect_301
 * @property string|null $title
 * @property string|null $description
 * @property string|null $keywords
 * @property string|null $h1
 * @property string|null $text
 *
 * @property SeoLang[] $seoLangs
 */
class Seo extends ActiveRecord
{
    use TranslatedTrait;

    public static function tableName()
    {
        return '{{%seo}}';
    }

    public function behaviors()
    {
        return [
            // Keyed by class name (rather than left as an anonymous array
            // entry) so getBehavior(TranslatedBehavior::class) reliably
            // resolves it - Yii2 stores unnamed behaviors() entries under
            // an auto-incrementing integer index instead.
            TranslatedBehavior::class => [
                'class' => TranslatedBehavior::class,
                'translateRelation' => 'seoLangs',
                'translateAttributes' => [
                    'title',
                    'description',
                    'keywords',
                    'h1',
                    'text',
                ],
            ],
        ];
    }

    public function rules()
    {
        return [
            [['item_id'], 'integer'],
            [['item_id'], 'unique', 'targetAttribute' => ['item_id', 'model_name']],
            [['model_name'], 'string', 'max' => 255],
            [['meta_index'], 'string', 'max' => 255],
            [['redirect_301'], 'string', 'max' => 2048],
            [['title', 'description', 'keywords', 'h1'], 'string', 'max' => 255],
            [['text'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item_id' => 'Item ID',
            'model_name' => 'Model Name',
            'title' => 'SEO Title',
            'description' => 'SEO Description',
            'keywords' => 'SEO Keywords',
            'h1' => 'H1',
            'text' => 'SEO Text',
            'meta_index' => 'Meta Index',
            'redirect_301' => '301 Redirect',
        ];
    }

    public function getSeoLangs()
    {
        return $this->hasMany(SeoLang::class, ['seo_id' => 'id']);
    }
}
