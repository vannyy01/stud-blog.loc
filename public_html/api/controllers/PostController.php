<?php
declare(strict_types=1);

namespace api\controllers;

use api\models\PostFull;
use api\models\PostSearch;
use common\models\Category;
use common\models\Post;
use common\models\Tags;
use Yii;
use yii\data\ActiveDataProvider;
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
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
            ],
        ];

        $behaviors['authenticator'] = $auth;
        $behaviors['authenticator']['authMethods'] = [
            HttpBearerAuth::className(),
        ];
        $behaviors['authenticator']['only'] = ['test', 'likes', 'create', 'update', 'delete'];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['validate', 'create', 'update', 'delete'],
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

    /**
     * @return array
     */
    public function actions(): array
    {
        $actions = parent::actions();
        unset($actions['create']);
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    /**
     * public function beforeAction($action)
     * {
     * if (parent::beforeAction($action)) {
     * if (!Yii::$app->user->can('admin',['post' => $this->modelClass])) {
     * // var_dump(Yii::$app->user->id);die();
     * throw new ForbiddenHttpException('Access denied');
     * }
     * return true;
     * } else {
     * return false;
     * }
     * }**/

    public function actionValidate(): bool
    {
        if (@Post::findByPostName(Yii::$app->request->queryParams["post_name"])) {
            return false;
        }
            return true;
    }

    /**
     * @return array|Post[]
     * @throws ServerErrorHttpException
     */
    public function actionLikes(): array
    {
        $arr = [];
        $favs = Yii::$app->getRequest()->getQueryParam("likes");
        $post_name = Yii::$app->getRequest()->getQueryParam("post_name");
        if (!empty($favs)) {
            $likes = ltrim($favs, '[');
            $likes = rtrim($likes, ']');
            $likes = explode(",", $likes);
            if (!empty($post_name)) {
                $arr = PostFull::find()->where(["like", "post_name", $post_name])->all();
                $posts = [];
                foreach ($likes as $like) {
                    foreach ($arr as $value) {
                        if ($value->post_id == $like) $posts[] = $value;
                    }
                }
                $arr = $posts;
            } else {
                foreach ($likes as $key => $like) {
                    if ($post = PostFull::findOne(["post_id" => $like])) {
                        $arr[] = $post;
                    }
                    if (count($arr) >= 5) break;
                }
            }
        }
        if (empty($favs)) {
            throw new ServerErrorHttpException('Doesn`t find posts with these ids');
        }
        return $arr;
    }

    public function actionTest()
    {
        //return Yii::$app->request->getHeaders();
        return !Yii::$app->user->isGuest;

    }

    /**
     * @return array
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate(): array
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
            return ['status' => $model];
        }
        throw new ServerErrorHttpException('Didn`tt create post.');
    }

    /**
     * @return array|Tags[]
     */
    public function actionTags(): array
    {
        $tag = Yii::$app->getRequest()->getQueryParam("tag");
        $tag = \GuzzleHttp\json_decode($tag);
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

    /**
     * @return bool
     * @throws ServerErrorHttpException
     */
    public function actionIncrement(): bool
    {
        $id = Yii::$app->getRequest()->getBodyParam('id');
        if ($id) {
            return Post::IncrementComment($id);
        }
        throw new ServerErrorHttpException('Incorect comment id.');

    }

    /**
     * @return bool
     * @throws ServerErrorHttpException
     */
    public function actionDecrement(): bool
    {
        $id = Yii::$app->getRequest()->getBodyParam('id');
        if ($id) {
            return Post::DecrementComment($id);
        }
        throw new ServerErrorHttpException('Incorect comment id.');

    }

    /**
     * @return \yii\data\ActiveDataProvider
     */
    public function prepareDataProvider(): ActiveDataProvider
    {
        $searchModel = new PostSearch();
        return $searchModel->search(Yii::$app->request->queryParams);
    }

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['test', 'likes', 'create', 'update', 'delete', 'validate'])) {
            if (!Yii::$app->user->can(Rbac::MANAGE_POST, ['post' => $model])) {
                throw  new ForbiddenHttpException('Forbidden.');
            }
        }
    }

    /**
     * @return array
     */
    protected function verbs(): array
    {
        return ['test' => ['get'],
            'create' => ['post', 'options'],
            'tags' => ['get', 'options'],
            'category' => ['get', 'options'],
            'increment' => ['put', 'patch'],
            'decrement' => ['put', 'patch'],
            'likes' => ['get', 'options'],
        ];
    }
}
