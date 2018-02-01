<?php
declare(strict_types=1);

namespace api\controllers;

use api\models\PostSearch;
use common\models\Category;
use common\models\Post;
use common\models\Tags;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class PostController extends ActiveController
{
    public $modelClass = 'common\models\Post';

    private static function allowedDomains(): array
    {
        return [
            'http://localhost:3030'
        ];
    }

    /**
     * @return array
     */
    public function behaviors(): array
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
        $behaviors['authenticator']['only'] = ['test', 'create', 'update', 'delete'];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['test', 'update', 'delete'],
            'rules' => [
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


    public function actionValidate(): bool
    {
        if (@Post::findByPostName(Yii::$app->request->queryParams["post_name"])) {
            return false;
        } else {
            return true;
        }

    }

    public function actionTest()
    {
        //return Yii::$app->request->getHeaders();
        return !Yii::$app->user->isGuest;

    }

    public function actionCreate()
    {
        $model = new Post();
        $items = [];
        $existTags = Yii::$app->getRequest()->getBodyParam("existTags") == false || NULL
            ? [] : Yii::$app->getRequest()->getBodyParam("existTags");
        $newTags = Yii::$app->getRequest()->getBodyParam("newTags");
        if ($newTags) {
            foreach ($newTags as $value) {
                $tag = new Tags();
                $tag->tag = $value;
                $tag->save();
            }
            foreach ($newTags as $key => $value) {
                $items[$key] = Tags::getTagId($value)->id;
            }
        }
        $model->user_id = Yii::$app->user->id;
        $model->tagsIds = array_merge($existTags, $items);
        $model->categoryIds = floatval(Yii::$app->getRequest()->getBodyParam("category"));
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            $model->post_name = trim(htmlentities($model->post_name));
            $model->post_text = trim(htmlentities($model->post_text));
            $model->short_description = trim(htmlentities($model->short_description));
            if ($model->save() === false) {
                if (!$model->hasErrors()) {
                    throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
                }
                throw new ServerErrorHttpException('Failed to create the object for incorect data.');
            }
            return ['status' => true];
        } else {
            throw new ServerErrorHttpException('Don`t create post.');
        }
    }

    /**
     * @return array|Tags[]
     */
    public function actionTags(): array
    {
        $tag = Yii::$app->getRequest()->getQueryParam("tag");
        return Tags::findByTag($tag);
    }

    /**
     * @return array|Category[]
     */
    public function actionCategory(): array
    {
        $tag = Yii::$app->getRequest()->getQueryParam("category");
        return Category::findCategory($tag);
    }

    public function prepareDataProvider()
    {
        $searchModel = new PostSearch();
        return $searchModel->search(Yii::$app->request->queryParams);
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['update', 'delete'])) {
            if (!Yii::$app->user->can(Rbac::MANAGE_POST, ['post' => $model])) {
                throw  new ForbiddenHttpException('Forbidden.');
            }
        }
    }

    protected function verbs(): array
    {
        return ['test' => ['get'],
            'create' => ['post', 'options'],
            'tags' => ['get', 'options'],
            'category' => ['get', 'options']
        ];
    }
}
