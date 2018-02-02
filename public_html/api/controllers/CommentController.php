<?php
declare(strict_types=1);

namespace api\controllers;

use api\models\BinaryTree;
use api\models\PostSearch;
use api\models\Tree;
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

    public function actionComments()
    {
        $post_id = Yii::$app->request->getQueryParam("post_id");
        $comments = Comments::find()->where(["post_id" => $post_id])->asArray()->all();
        global $a_rows;
        $wood = [];
        function getChild($parent)
        {
            global $a_rows;
            $array = [];
            $i = 0;
            foreach ($a_rows as $row) {
                if ($parent['comment_id'] == $row['parent_id']) {
                    $array[$i] = $row;
                    $array[$i]['child'] = [];
                    $array[$i]['child'] = getChild($row);
                    $i++;
                }
            }
            return $array;
        }

        $a_rows = $comments;
        foreach ($a_rows as $row) {
            if (empty($row['parent_id'])) {
                $row['child'] = getChild($row);
                // print_r($row); die();
                $wood[$row['comment_id']] = $row;
            }
        }
        return $wood;
    }
}