<?php

declare(strict_types=1);

namespace frontend\controllers;

use common\components\LanguageBootstrap;
use frontend\components\Controller;
use Yii;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;

final class LanguageController extends Controller
{
    public function actionSwitch(string $lang)
    {
        if(!isset(LanguageBootstrap::LANGUAGES[$lang]))
        {
            throw new NotFoundHttpException();
        }

        $config = [
            'name' => LanguageBootstrap::COOKIE,
            'value' => $lang,
            'expire' => time() + 365 * 86400,
            'path' => '/',
            'httpOnly' => true,
            'secure' => Yii::$app->request->isSecureConnection,
            'sameSite' => Cookie::SAME_SITE_LAX,
        ];

        $domain = trim((string)ht_env('COOKIE_DOMAIN', ''));

        if($domain !== '')
        {
            $config['domain'] = $domain;
        }

        Yii::$app->response->cookies->add(
            new Cookie($config)
        );

        $returnUrl = trim((string)Yii::$app->request->get(
            'return',
            '/'
        ));

        if(
            $returnUrl === ''
            || !str_starts_with($returnUrl, '/')
            || str_starts_with($returnUrl, '//')
        )
        {
            $returnUrl = '/';
        }

        return $this->redirect($returnUrl);
    }
}
