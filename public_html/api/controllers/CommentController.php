<?php
declare(strict_types=1);

namespace api\controllers;

use api\models\PostSearch;
use common\models\Category;
use common\models\Post;
use common\models\Comments;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class CommentController extends ActiveController
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

    public function actionComments():array
    {
        $post_id = Yii::$app->request->getQueryParam("post_id");
        $comments = Comments::findAll(["post_id" => $post_id]);
        return $comments;
    }
}