<?php
declare(strict_types=1);

namespace api\controllers;

use api\models\Node;
use common\models\Comments;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class CommentController extends ActiveController
{
    public $modelClass = 'common\models\Comments';

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
        $behaviors['authenticator']['only'] = ['test', 'increment', 'decrement',
            'create', 'update', 'delete'];
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
        unset($actions['index']);
        $actions['increment']['checkAccess'] = [$this, 'checkAccess'];
        $actions['increment']['modelClass'] = $this->modelClass;

        return $actions;
    }

    /**
     * @param array $parent
     * @return array
     */
    private function getChild(array $parent): array
    {
        $a_rows = new Node();
        $a_rows = $a_rows->getData();
        $array = [];
        $i = 0;
        foreach ($a_rows as $row) {
            if ($parent['comment_id'] != $row['parent_id'])
                continue;
            $array[$i] = $row;
            $array[$i]['child'] = [];
            $array[$i]['child'] = $this->getChild($row);
            $i++;
        }
        return $array;
    }

    public function actionIndex(): array
    {
        $post_id = Yii::$app->request->getQueryParam("post_id");
        $comments = Comments::find()->where(["post_id" => $post_id])->all();
        $comments = ArrayHelper::toArray($comments);
        $wood = [];
        $a_rows = new Node();
        $a_rows->setData($comments);
        foreach ($a_rows->getData() as $row) {
            if (empty($row['parent_id'])) {
                $row['child'] = $this->getChild($row);
                $wood[$row['comment_id']] = $row;
            }
        }
        $a_rows->unsetData();
        return $wood;
    }

    public function actionCreate()
    {
        $model = new Comments();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            $model->user_id = Yii::$app->user->id;
            $model->comment_text = trim(htmlentities($model->comment_text));
            if ($model->save() === false) {
                if (!$model->hasErrors()) {
                    throw new ServerErrorHttpException('Failed to create the comment for unknown reason.');
                }
                throw new ServerErrorHttpException('Failed to create the comment for incorect data.');
            }
            return $model;
        }
        throw new ServerErrorHttpException('Didn`t create comment.');
    }

    public function actionIncrement(): bool
    {
        $id = Yii::$app->getRequest()->getBodyParam('id');
        if ($id) {
            return Comments::IncrementComment($id);
        }
        throw new ServerErrorHttpException('Incorect comment id.');
    }

    public function actionDecrement(): bool
    {
        $id = Yii::$app->getRequest()->getBodyParam('id');
        if ($id) {
            return Comments::DecrementComment($id);
        }
        throw new ServerErrorHttpException('Incorect comment id.');
    }

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        //var_dump($action);die();
        if (in_array($action, ['increment', 'decrement'])) {
            if (!Yii::$app->user->can('evaluateSmth', ['post' => $model])) {
                throw  new ForbiddenHttpException('Forbidden.');
            }
        }
    }

    protected
    function verbs(): array
    {
        return [
            'index' => ['get', 'options'],
            'create' => ['post', 'options'],
            'increment' => ['put', 'patch', 'options'],
            'decrement' => ['put', 'patch', 'options']
        ];
    }
}