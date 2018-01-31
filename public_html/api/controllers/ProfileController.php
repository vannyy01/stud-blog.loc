<?php
declare(strict_types=1);

namespace api\controllers;

use common\models\User;
use common\models\UserInfo;
use Yii;
use yii\filters\AccessControl;
use yii\filters\Cors;
use yii\helpers\Json;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\ServerErrorHttpException;

class ProfileController extends ActiveController
{
    public $modelClass = 'common\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['http://localhost:3030'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH','OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
            ],
        ];

        $behaviors['authenticator'] = $auth;
        $behaviors['authenticator']['authMethods'] = [
            HttpBearerAuth::className(),
        ];
        $behaviors['authenticator']['only'] = ['user','update', 'delete'];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['create'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'create', 'validate'],
                    'roles' => ['?'],
                ],
                [
                    'allow' => true,
                    'actions' => ['user'],
                    'roles' => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update']);
        return $actions;
    }

    public function actionIndex()
    {

    }

    public function actionValidate(): bool
    {
        if (@User::findByEmail(Yii::$app->request->queryParams["email"])) {
            return false;
        } elseif (@User::findByUserName(Yii::$app->request->queryParams["username"])) {
            return false;
        } else {
            return true;
        }

    }

    public function actionUser()
    {
        $token = Yii::$app->request->getQueryParam('token');
        return User::findIdentityByAccessToken($token);
    }

    public function actionCreate()
    {
        $model = new User();
        if ($model->load(Yii::$app->request->bodyParams, '')) {
            if (!$model::findByUserName($model->user_name) ||
                !$model::findByEmail($model->email)) {
                $model->role = $model::STATUS_ACTIVE;
                $model->setPassword($model->pass_hash);
                if ($model->save() === false && !$model->hasErrors()) {
                    throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
                }
                return $model;
            }
            Yii::$app->response->setStatusCode(432, 'Incorrect user info')->send();
        } else {
            Yii::$app->response->setStatusCode(501, 'Failed to load users info')->send();
        }
    }

    public function actionUpdate()
    {
        $model = $this->findModel();
        $info = UserInfo::findOne($model->user_id);
        $info->load(Yii::$app->request->getBodyParams(), '');
        $model->load(Yii::$app->request->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }
        if ($info->save() === false && !$info->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }
        return 'Ви успішно оновили дані!';
    }

    public function verbs()
    {
        return [
            'index' => ['get'],
            'create' => ['post'],
            'update' => ['put', 'patch'],
        ];
    }

    /**
     * @return User
     */
    private function findModel()
    {
        return User::findOne(Yii::$app->user->id);
    }
}