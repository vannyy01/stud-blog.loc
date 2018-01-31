<?php
declare(strict_types=1);

namespace api\controllers;

use api\models\PostSearch;
use common\models\Blog;
use common\models\Category;
use common\models\Post;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class BlogController extends ActiveController
{
    public $modelClass = 'common\models\Post';

    private static function allowedDomains()
    {
        return [
            'http://localhost:3030'
        ];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => static::allowedDomains(),
                'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
            ],
        ];

        $behaviors['authenticator'] = $auth;
        $behaviors['authenticator']['authMethods'] = [
            HttpBearerAuth::className(),
        ];
        $behaviors['authenticator']['only'] = ['test', 'blogs', 'create', 'update', 'delete'];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['test', 'validate', 'create', 'update', 'delete'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['validate'],
                    'roles' => ['?'],
                ],
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];
        return $behaviors;
    }


    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }


    public function actionTest()
    {
        //return Yii::$app->request->getHeaders();
        return !Yii::$app->user->isGuest;

    }

    public function actionBlogs()
    {
        return Blog::findBlogByUserID(Yii::$app->user->id);
    }


    public function actionCreate()
    {
        $model = new Blog();
        $model->user_id = Yii::$app->user->id;
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            if ($model::findByBlogName($model->blog_name)) {
                return Yii::$app->response->setStatusCode(432, 'Blog name has taken')->send();
            } else if ($model->save() === false && !$model->hasErrors()) {
                throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
            }
            return $model;
        }

    }

    public function prepareDataProvider()
    {
        $searchModel = new PostSearch();
        return $searchModel->search(Yii::$app->request->queryParams);
    }


    public function actionValidate(): bool
    {
        if (@Blog::findByBlogName(Yii::$app->request->queryParams["blog_name"])) {
            return false;
        } else {
            return true;
        }

    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['update', 'delete'])) {
            if (!Yii::$app->user->can(Rbac::MANAGE_POST, ['post' => $model])) {
                throw  new ForbiddenHttpException('Forbidden.');
            }
        }
    }

    protected function verbs()
    {
        return ['test' => ['get'],
            'blogs' => ['get']
        ];
    }
}
