<?php

namespace baiyou\api;

use Yii;

class Module extends \yii\base\Module
{
    /**
    * @inheritdoc
    */
//    public $defaultRoute = 'log';

    /**
    * @inheritdoc
    */
    public function init()
    {
        parent::init();
//        Yii::setAlias('@actionlog', $this->getBasePath());
//        $this->registerTranslations();
        $this->modules = [
            'backend' => [
                // 此处应考虑使用一个更短的命名空间
                'class' => 'baiyou\backend\Module',
            ],
            'frontend' => [
                // 此处应考虑使用一个更短的命名空间
                'class' => 'baiyou\frontend\Module',
            ],
        ];
    }

//    /**
//    * Translating module messages
//    */
//    public function registerTranslations()
//    {
//        Yii::$app->i18n->translations['actionlog'] = [
//            'class' => 'yii\i18n\PhpMessageSource',
//            'basePath' => '@actionlog/messages',
//            'sourceLanguage' => 'en-US',
//        ];
//    }
}
