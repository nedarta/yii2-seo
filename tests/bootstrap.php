<?php

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
require dirname(__DIR__) . '/src/migrations/m260922_120000_create_seo_tables.php';

defined('YII_ENV') or define('YII_ENV', 'test');

new yii\web\Application([
    'id' => 'test',
    'basePath' => dirname(__DIR__),
    'vendorPath' => dirname(__DIR__) . '/vendor',
    'components' => [
        'request' => [
            'class' => yii\web\Request::class,
            'enableCsrfValidation' => false,
        ],
        'db' => [
            'class' => yii\db\Connection::class,
            'dsn' => 'sqlite::memory:',
        ],
    ],
]);

// Build the actual schema the suite runs against. Previously nothing ran
// here, so any test touching the database (e.g. SeoFieldsTest::getSeo(),
// which calls Seo::findOne()) would fail with "no such table: seo" against
// the empty in-memory connection above.
$migration = new m260922_120000_create_seo_tables();
ob_start();
$migration->safeUp();
ob_end_clean();

// Throwaway fixture table backing tests\models\TestProduct - not part of
// the package's own schema.
Yii::$app->db->createCommand()->createTable('{{%test_product}}', [
    'id' => $migration->primaryKey(),
    'name' => $migration->string(255),
])->execute();
