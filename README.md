# nedarta/yii2-seo

A small Yii2 SEO extension that stores SEO metadata separately from the
application model and supports multilingual fields through
[`lav45/yii2-translated-behavior`](https://github.com/LAV45/yii2-translated-behavior).

The extension is a clean rewrite of the original `dvizh/yii2-seo` concept,
with these changes:

- `nedarta\seo` namespace.
- PSR-4 autoloading.
- snake_case database column names.
- multilingual `title`, `description`, `keywords`, `h1` and `text`.
- language-independent `meta_index` and `redirect_301`.
- no Bootstrap dependency.
- no JavaScript dependency.
- no hard-coded application model classes.
- one SEO record per `(model_name, item_id)`.
- translations stored in a dedicated `seo_lang` table.
- normal Yii2 model/form validation and saving.

## Installation

```bash
composer require nedarta/yii2-seo
```

The package requires:

```text
yiisoft/yii2
lav45/yii2-translated-behavior
```

## Database

Run the included migration:

```bash
yii migrate --migrationPath=@vendor/nedarta/yii2-seo/src/migrations
```

It creates:

### `seo`

```text
id
item_id
model_name
meta_index
redirect_301
```

### `seo_lang`

```text
seo_id
lang_id
title
description
keywords
h1
text
```

The unique key on `seo(model_name, item_id)` prevents duplicate SEO records
for the same application model.

## Configure the model

Attach `SeoFields` to any ActiveRecord model:

```php
<?php

namespace common\models;

use nedarta\seo\behaviors\SeoFields;
use yii\db\ActiveRecord;

class Product extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'seo' => [
                'class' => SeoFields::class,
            ],
        ];
    }
}
```

The behavior exposes:

```php
$product->seo
```

which returns a `nedarta\seo\models\Seo` model.

## Multilingual SEO

`Seo` uses LAV45's translated behavior internally.

Translated fields:

```text
title
description
keywords
h1
text
```

Non-translated fields:

```text
meta_index
redirect_301
```

For example:

```php
$product->seo->title;
$product->seo->description;
```

return the values for the current language configured by
`lav45/yii2-translated-behavior`.

The SEO model contains:

```php
use lav45\translate\TranslatedBehavior;
use lav45\translate\TranslatedTrait;

class Seo extends ActiveRecord
{
    use TranslatedTrait;

    public function behaviors()
    {
        return [
            [
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
}
```

## Form

In a normal Yii2 form:

```php
<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name')->textInput() ?>

<?= \nedarta\seo\widgets\SeoForm::widget([
    'model' => $model,
    'form' => $form,
]) ?>

<?php ActiveForm::end(); ?>
```

The widget does not save anything itself. It only renders fields for the
SEO model. `SeoFields` persists the submitted SEO data when the owner model
is saved.

## Create/update flow

For an existing model:

```php
$model->seo
```

loads the corresponding SEO row.

If no SEO row exists, the behavior returns a new `Seo` instance.

When the owner model is saved, the behavior saves the SEO record if SEO data
was submitted.

The SEO translation rows are then handled by LAV45's
`TranslatedBehavior`.

## Explicit access

You can also work with the SEO model directly:

```php
$seo = $product->seo;

$seo->title = 'Red bicycle';
$seo->description = 'A lightweight red bicycle.';

$seo->save();
```

The language used by translated attributes is controlled by LAV45's behavior
configuration and application language.

## Field requirements

Every SEO field - `title`, `description`, `keywords`, `h1`, `text`,
`meta_index`, `redirect_301` - is optional. Neither `Seo` nor `SeoLang`
declares a `required` rule for them, since a page is allowed to have no SEO
overrides at all. Add your own `required` rule in an application-level
subclass if your project needs specific fields filled in.

## Model name

By default, the extension uses the complete PHP class name:

```text
common\models\Product
```

rather than only:

```text
Product
```

This avoids collisions between classes with the same short name.

You can override this:

```php
'seo' => [
    'class' => SeoFields::class,
    'modelName' => 'catalog_product',
],
```

## Deleting

When an owner model is deleted, its SEO record is deleted as well. The
database foreign key then cascades to `seo_lang`.

## Migration path

The migration class is intentionally named with a current-style timestamp.
If you copy the migration into an application's own migration directory,
rename it according to that application's migration convention.

## Testing

Install development dependencies:

```bash
composer install
```

Run:

```bash
vendor/bin/phpunit
```

The test suite covers:

- SEO model schema rules.
- SEO owner lookup.
- creation of an SEO record.
- model-name isolation.
- multilingual SEO relation configuration.
- deletion behavior.
- form rendering.

## License

BSD-3-Clause. See `LICENSE`.
