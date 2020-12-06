<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace example\emulated\frontend\controllers;

use Da\User\Controller\SettingsController as BaseSettingsController;
use Da\User\Model\User;
use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SettingsController extends BaseSettingsController
{

    /**
     * Exports the data from the current user in a mechanical readable format (csv). Properties exported can be defined
     * in the module configuration.
     * @throws NotFoundHttpException if gdpr compliance is not enabled
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionExport()
    {
        if (!$this->module->enableGdprCompliance) {
            throw new NotFoundHttpException();
        }
        try {
            $properties = $this->module->gdprExportProperties;
            $user = Yii::$app->user->identity;
            $data = [];

            $formatter = Yii::$app->formatter;
            // override the default html-specific format for nulls
            $formatter->nullDisplay = "";
            $data[0] = $baseData = [];
            $multidata = false;
            $count = 0;
            foreach ($properties as $property) {
                if (!is_array($property)) {
                    $splitted = explode('.', $property);
                    $property = array_pop($splitted);
                    $data[0][$count] = $property;
                    $value = ArrayHelper::getValue($user, $property);
                    $baseData[$count] = $formatter->asText($value);
                    $count++;

                } else {
                    foreach ($property as $value) {
                        $splitted = explode('.', $value);
                        $value = array_pop($splitted);
                        $data[0][$count] = $value;
                        $count++;
                    }
                    $multidata = true;
                }
            }
            $count = 0;
            if ($multidata) {
                $aux = [];
                foreach ($properties as $key => $property) {
                    if (is_array($property)) {
                        $value = ArrayHelper::getValue($user, $key);
                        foreach ($value as $item) {
                            $aux = $baseData;
                            $subcount = $count;
                            foreach ($property as $value) {
                                $iValue = ArrayHelper::getValue($item, $value);
                                $aux[$subcount] = $iValue;
                                $subcount++;
                            }
                            $data[] = $aux;
                        }
                    } else
                        $count++;
                }
            } else {
                $data[1] = $baseData;
            }
            Yii::$app->response->headers->removeAll();
            Yii::$app->response->headers->add('Content-type', 'text/csv');
            Yii::$app->response->headers->add('Content-Disposition', 'attachment;filename=gdpr-data.csv');
            Yii::$app->response->send();
            $f = fopen('php://output', 'w');
            foreach ($data as $line) {
                fputcsv($f, $line);
            }
        } catch (\Exception $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        if ($this->checkUserOrders())
            return parent::actionDelete();

        return $this->goHome();
    }

    /**
     * Checks whether an account has orders
     * @return bool
     * @throws UserException
     */
    private function checkUserOrders()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        if (!empty($user->orders))
            throw new UserException(Yii::t('xenon', 'We can not delete your data because you have valid orders in your account.'));

        return true;
    }

    /**
     * @return string|Response
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionGdprDelete()
    {
        if ($this->checkUserOrders())
            return parent::actionGdprDelete();

        return $this->goHome();
    }

}
