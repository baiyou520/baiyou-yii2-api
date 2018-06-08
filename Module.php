<?php

namespace baiyou\backend;

/**
 * v1 module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $defaultRoute = 'auth';
    /**
     * {@inheritdoc}
     */
//    public $controllerNamespace = 'baiyou\backend\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
