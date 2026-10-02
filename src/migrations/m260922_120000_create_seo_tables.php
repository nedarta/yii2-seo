<?php

use yii\db\Migration;

class m260922_120000_create_seo_tables extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%seo}}', [
            'id' => $this->primaryKey(),
            'item_id' => $this->integer()->notNull(),
            'model_name' => $this->string(255)->notNull(),
            'meta_index' => $this->string(255),
            'redirect_301' => $this->string(2048),
        ]);

        $this->createIndex(
            'ux-seo-model-item',
            '{{%seo}}',
            ['model_name', 'item_id'],
            true
        );

        // The composite primary key and the foreign key are declared
        // inline, as extra unkeyed entries in the columns array, rather
        // than via addPrimaryKey()/addForeignKey() after the table exists.
        // SQLite does not support adding a primary key or a foreign key
        // constraint to an existing table, so the previous two-step
        // version of this migration could never run against SQLite (used
        // by the test suite, and by some lightweight deployments) even
        // though it worked fine on MySQL/PostgreSQL.
        $this->createTable('{{%seo_lang}}', [
            'seo_id' => $this->integer()->notNull(),
            'lang_id' => $this->string(10)->notNull(),
            'title' => $this->string(255),
            'description' => $this->string(255),
            'keywords' => $this->string(255),
            'h1' => $this->string(255),
            'text' => $this->text(),
            'PRIMARY KEY (seo_id, lang_id)',
            'FOREIGN KEY (seo_id) REFERENCES {{%seo}}(id) ON DELETE CASCADE ON UPDATE CASCADE',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%seo_lang}}');
        $this->dropTable('{{%seo}}');
    }
}
