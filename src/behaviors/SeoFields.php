<?php

namespace nedarta\seo\behaviors;

use nedarta\seo\models\Seo;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\IntegrityException;
use yii\web\Request;

/**
 * Adds multilingual SEO metadata to an ActiveRecord model.
 *
 * @property ActiveRecord $owner
 * @property Seo $seo
 */
class SeoFields extends Behavior
{
    /**
     * @var string|null Custom model identifier used in the SEO table.
     */
    public $modelName;

    /**
     * @var string Request body attribute containing SEO data.
     */
    public $formName = 'Seo';

    /**
     * @var Seo|null Cached SEO instance for the current owner.
     */
    private $_seo;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'saveSeo',
            ActiveRecord::EVENT_AFTER_UPDATE => 'saveSeo',
            // Runs after the owner is actually gone, so a cancelled or
            // failed owner delete never leaves an orphaned "SEO deleted,
            // owner still there" state.
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteSeo',
        ];
    }

    /**
     * Returns the SEO model for the owner.
     *
     * A new unsaved SEO model is returned when no row exists yet. The
     * result is cached for the lifetime of the behavior instance so
     * repeated reads of $owner->seo don't re-query the database; saveSeo()
     * and deleteSeo() clear the cache themselves.
     *
     * @return Seo
     */
    public function getSeo()
    {
        if ($this->_seo !== null) {
            return $this->_seo;
        }

        $owner = $this->owner;

        $seo = Seo::findOne([
            'item_id' => $owner->getPrimaryKey(),
            'model_name' => $this->getModelName(),
        ]);

        if ($seo === null) {
            $seo = new Seo([
                'item_id' => $owner->getPrimaryKey(),
                'model_name' => $this->getModelName(),
            ]);
        }

        return $this->_seo = $seo;
    }

    /**
     * Clears the cached SEO instance so the next getSeo() call re-queries
     * the database.
     */
    public function resetSeo()
    {
        $this->_seo = null;
    }

    /**
     * Saves SEO data submitted with the owner model.
     */
    public function saveSeo()
    {
        if ($this->owner->getPrimaryKey() === null) {
            return;
        }

        $data = $this->getRequestData();

        if ($data === null) {
            return;
        }

        $seo = $this->getSeo();

        if (!$seo->load($data, $this->formName)) {
            return;
        }

        $seo->item_id = $this->owner->getPrimaryKey();
        $seo->model_name = $this->getModelName();

        if (!$this->trySaveSeo($seo)) {
            // The owner's own save already succeeded by this point (this
            // runs on EVENT_AFTER_INSERT/EVENT_AFTER_UPDATE), so we can't
            // fail the owner's save - but we can make sure the failure is
            // visible on the owner instead of silently dropping the data.
            $this->owner->addError(
                'seo',
                'SEO data could not be saved: ' . implode(' ', $seo->getErrorSummary(true))
            );
        }

        $this->resetSeo();
    }

    /**
     * Saves $seo, recovering once from a concurrent insert of the same
     * (model_name, item_id) row created between getSeo()'s lookup and
     * this save (the unique index on the seo table would otherwise turn
     * that race into a raw DB exception or a lost update).
     *
     * @return bool
     */
    protected function trySaveSeo(Seo $seo)
    {
        try {
            if ($seo->save()) {
                return true;
            }
        } catch (IntegrityException $e) {
            if (!$seo->getIsNewRecord()) {
                throw $e;
            }
        }

        if (!$seo->getIsNewRecord()) {
            // An existing row failed ordinary validation - nothing to retry.
            return false;
        }

        $existing = Seo::findOne([
            'item_id' => $seo->item_id,
            'model_name' => $seo->model_name,
        ]);

        if ($existing === null) {
            return false;
        }

        $existing->setAttributes($seo->getAttributes($seo->safeAttributes()));

        return $existing->save();
    }

    /**
     * Deletes the SEO record associated with the owner.
     */
    public function deleteSeo()
    {
        $seo = Seo::findOne([
            'item_id' => $this->owner->getPrimaryKey(),
            'model_name' => $this->getModelName(),
        ]);

        if ($seo !== null) {
            $seo->delete();
        }

        $this->resetSeo();
    }

    /**
     * Returns the identifier used to associate SEO with the owner.
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName ?: get_class($this->owner);
    }

    /**
     * @return array|null
     */
    protected function getRequestData()
    {
        if (!\Yii::$app->has('request')) {
            return null;
        }

        $request = \Yii::$app->request;

        if (!$request instanceof Request) {
            return null;
        }

        // Accept POST as well as PUT/PATCH, since owner models exposed
        // through a REST controller are typically updated with PUT/PATCH,
        // where getIsPost() is false even though the body carries data.
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            return null;
        }

        return $request->getBodyParams();
    }
}
