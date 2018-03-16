<?php
declare(strict_types=1);

namespace api\controllers;

use common\rbac\DeleteAuthorRule;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\filters\AccessControl;
use yii\rest\ActiveController;
use api\models\LoginForm;
use yii\web\ServerErrorHttpException;

class SiteController extends ActiveController
{
    public $modelClass = 'api\models';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['http://localhost:3030'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
            ],
        ];

        $behaviors['authenticator'] = $auth;

        $behaviors['authenticator']['authMethods'] = [
            HttpBearerAuth::className(),
        ];
        $behaviors['authenticator']['only'] = ['info', 'active'];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['login'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['login'],
                    'roles' => ['?'],
                ]
            ]
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

    public function actionRule()
    {/*

        $admin = Yii::$app->authManager->createRole('admin');
        $admin->description = 'Администратор';
        Yii::$app->authManager->add($admin);

        $moderator = Yii::$app->authManager->createRole('moderator');
        $moderator->description = 'Модератор';
        Yii::$app->authManager->add($moderator);

        $author = Yii::$app->authManager->createRole('author');
        $author->description = 'Автор';
        Yii::$app->authManager->add($author);

        $banned = Yii::$app->authManager->createRole('banned');
        $banned->description = 'Заблокований';
        Yii::$app->authManager->add($banned);

        $permit = Yii::$app->authManager->createPermission('createPost');
        $permit->description = 'Право на створення поста';
        Yii::$app->authManager->add($permit);

        $permit = Yii::$app->authManager->createPermission('updatePost');
        $permit->description = 'Право на оновлення поста';
        Yii::$app->authManager->add($permit);

        $permit = Yii::$app->authManager->createPermission('deletePost');
        $permit->description = 'Право на видалення поста';
        Yii::$app->authManager->add($permit);
    **/
        /**
         * $role = Yii::$app->authManager->getRole('admin');
         * $permit = Yii::$app->authManager->getPermission('createPost');
         * Yii::$app->authManager->addChild($role, $permit);
         *
         * $role = Yii::$app->authManager->getRole('author');
         * $permit = Yii::$app->authManager->getPermission('createPost');
         * Yii::$app->authManager->addChild($role, $permit);
         *
         * $role = Yii::$app->authManager->getRole('moderator');
         * $permit = Yii::$app->authManager->getPermission('createPost');
         * Yii::$app->authManager->addChild($role, $permit);
         **/
        /**
         * $auth = Yii::$app->authManager;
         *
         * // добовляем правило
         * $rule = new DeleteAuthorRule();
         * $auth->add($rule);
         *
         * // добавляем право "updateOwnPost" и связываем правило с ним
         * $deleteOwnPost = $auth->createPermission('deleteOwnPost');
         * $deleteOwnPost->description = 'Удалить свои посты';
         * $deleteOwnPost->ruleName = $rule->name;
         * $auth->add($deleteOwnPost);
         *
         * // "updateOwnPost" наследует право "updatePost"
         * $deletePost = Yii::$app->authManager->getPermission('deletePost');
         * $auth->addChild($deleteOwnPost, $deletePost);
         *
         * $author = Yii::$app->authManager->getRole('author');
         * // и тут мы позволяем автору редактировать свои посты
         * $auth->addChild($author, $deleteOwnPost);
         **/
        /**
        $permit = Yii::$app->authManager->createPermission('evaluateSmth');
        $permit->description = 'Право на оцінку';
        Yii::$app->authManager->add($permit);
         **/
        $role = Yii::$app->authManager->getRole('author');
        $author = Yii::$app->authManager->getPermission('evaluateSmth');
        Yii::$app->authManager->addChild($role, $author);
        return 'ok';
    }

    /**
     * @return bool
     */
    public function actionActive(): bool
    {
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
            'active' => ['get'],
            'login' => ['post', 'options'],
        ];
    }
}
