<?php
declare(strict_types=1);

namespace api\controllers;

use common\models\User;
use Yii;
use yii\base\UserException;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use api\models\LoginForm;
use common\models\Token;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class SiteController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        //unset($behaviors['authenticator']);

        $behaviors['authenticator']['authMethods'] = [
            HttpBearerAuth::className(),
        ];
        $behaviors['authenticator']['only'] = ['info', ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                ['allow' => true,
                    'actions' => ['login', 'active'],
                    'roles' => ['?'],
                ],
            ],
        ];
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
        ];
        return $behaviors;
    }

    public function beforeAction($action)
    {
        $actions = parent::actions();
        if (in_array($action->id, ['login'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return 'api';
    }

    public function actionInfo(): array
    {
        return [
            'site_name' => 'kraviv.xyz',
            'date' => date_default_timezone_get(),
        ];
    }

    public function actionActive()
    {
        return Yii::$app->request->getHeaders();
        return !Yii::$app->user->isGuest;
    }

    public function actionLogin()
    {
        $this->enableCsrfValidation = false;
        $model = new LoginForm();
        $model->load(Yii::$app->request->bodyParams, '');
        if ($token = $model->auth()) {
            return $token;
        } elseif (!$token = $model->auth()) {
            $model->load(Yii::$app->request->bodyParams, '');
            if ($token = $model->auth()) {
                return $token;
            }
        } else {
            return $model;
            throw new ServerErrorHttpException('Invalid login or password');
        }

    }

    protected function verbs()
    {
        return [
             'info' => ['get'],
            'login' => ['post' ,'options'],
        ];
    }
}
