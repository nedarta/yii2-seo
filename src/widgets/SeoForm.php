<?php

namespace nedarta\seo\widgets;

use nedarta\seo\assets\SeoAsset;
use nedarta\seo\models\Seo;
use yii\base\Widget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * Renders SEO fields for an ActiveRecord model.
 *
 * The widget only renders fields. Persistence is handled by SeoFields.
 */
class SeoForm extends Widget
{
    /**
     * @var \yii\db\ActiveRecord
     */
    public $model;

    /**
     * @var ActiveForm
     */
    public $form;

    /**
     * @var string
     */
    public $title = 'SEO';

    /**
     * @var bool
     */
    public $collapsed = false;

    public function init()
    {
        parent::init();

        if ($this->model === null) {
            throw new \yii\base\InvalidConfigException('The "model" property is required.');
        }

        if ($this->form === null) {
            throw new \yii\base\InvalidConfigException('The "form" property is required.');
        }
    }

    public function run()
    {
        SeoAsset::register($this->getView());

        $seo = $this->model->seo;

        if (!$seo instanceof Seo) {
            throw new \yii\base\InvalidConfigException(
                'The supplied model must have the nedarta\\seo\\behaviors\\SeoFields behavior.'
            );
        }

        $body = [];

        $body[] = $this->form->field($seo, 'title')
            ->textInput(['maxlength' => true]);

        $body[] = $this->form->field($seo, 'description')
            ->textInput(['maxlength' => true]);

        $body[] = $this->form->field($seo, 'keywords')
            ->textInput(['maxlength' => true]);

        $body[] = $this->form->field($seo, 'h1')
            ->textInput(['maxlength' => true]);

        $body[] = $this->form->field($seo, 'text')
            ->textarea(['rows' => 6]);

        $body[] = $this->form->field($seo, 'meta_index')
            ->textInput(['maxlength' => true]);

        $body[] = $this->form->field($seo, 'redirect_301')
            ->textInput(['maxlength' => true]);

        $classes = ['nedarta-seo'];

        if ($this->collapsed) {
            $classes[] = 'nedarta-seo--collapsed';
        }

        return Html::tag(
            'section',
            Html::tag(
                'h2',
                Html::encode($this->title),
                ['class' => 'nedarta-seo__title']
            ) .
            Html::tag(
                'div',
                implode("\n", $body),
                ['class' => 'nedarta-seo__body']
            ),
            ['class' => implode(' ', $classes)]
        );
    }
}
