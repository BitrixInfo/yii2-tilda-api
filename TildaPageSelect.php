<?php
namespace daccess1\tilda;

use Yii;
use yii\base\InvalidConfigException;

class TildaPageSelect extends \yii\base\Widget
{
    public $model;
    /** @var array */
    public $pages;
    /** @var integer */
    public $field;
    /** @var integer */
    public $project;

    /**
     * (@inheritdoc)
     */
    public function init()
    {
        if (!isset($this->pages)) {
            if (isset($this->project)) {
                //If project is defined for this instance
                $this->pages = \Yii::$app->tilda->listPages($this->project);
            } else {
                //Load pages from default app project
                $this->pages = \Yii::$app->tilda->listPages();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $modelClass = get_class($this->model);
        $modelName = substr($modelClass,strrpos($modelClass,'\\') + 1);
        return $this->render('page-select',[
            'pages' => $this->pages,
            'model' => $this->model,
            'field' => $this->field,
            'modelName' => $modelName
        ]);
    }
}
