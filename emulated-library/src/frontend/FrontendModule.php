<?php


namespace example\emulated\frontend;


class FrontendModule extends \yii\base\Module
{

    public function init()
    {
        $config = [
            'modules' => [
                'user' => [
                    'class' => \Da\User\Module::class,
                    'enableFlashMessages' => false,
                    'enableEmailConfirmation' => false,
                    'administratorPermissionName' => 'admin',
                    'enableGdprCompliance' => true,
                ],
            ]
        ];

        \Yii::configure($this, $config);

        parent::init();
    }
}
