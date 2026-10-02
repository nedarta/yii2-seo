<?php

namespace nedarta\seo\models;

use yii\db\ActiveRecord;

/**
 * Translation record for SEO metadata.
 *
 * @property int $seo_id
 * @property string $lang_id Language code, e.g. lv, en, ru.
 * @property string|null $title
 * @property string|null $description
 * @property string|null $keywords
 * @property string|null $h1
 * @property string|null $text
 *
 * @property Seo $seo
 */
class SeoLang extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%seo_lang}}';
    }

    public function rules()
    {
        return [
            [['seo_id'], 'integer'],
            [['lang_id'], 'string', 'max' => 10],
            [['title', 'description', 'keywords', 'h1'], 'string', 'max' => 255],
            [['text'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'seo_id' => 'SEO',
            'lang_id' => 'Language',
            'title' => 'SEO Title',
            'description' => 'SEO Description',
            'keywords' => 'SEO Keywords',
            'h1' => 'H1',
            'text' => 'SEO Text',
        ];
    }

    public function getSeo()
    {
        return $this->hasOne(Seo::class, ['id' => 'seo_id']);
    }
}
