<?php
declare(strict_types=1);

namespace api\controllers;

use common\models\User;
use Yii;
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
        $behaviors['authenticator']['only'] = ['create', 'update', 'delete'];
        $behaviors['authenticator']['authMethods'] = [
            HttpBearerAuth::className(),
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                ['allow' => true,
                    'actions' => ['login', 'test'],
                    'roles' => ['?'],
                ],
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'login' => ['post', 'options'],
            ]
        ];
        return array_merge($behaviors, [

            // For cross-domain AJAX request
            'corsFilter' => [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    //'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                ],
            ],
        ]);
    }

    public function beforeAction($action)
    {
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

    public function actionTest()
    {
        return Yii::$app->user->isGuest;
    }

    public function actionLogin()
    {
        $this->enableCsrfValidation = false;
        $result = [];
        $user = User::findByEmail(Yii::$app->request->getBodyParam('login'));
        if (!$user) {
            $user = User::findByUserName(Yii::$app->request->getBodyParam('login'));
            if (!$user)
                $result = [
                    'success' => 0,
                    'message' => 'No such user found'
                ];
        }
        if ($user)
            if (!$user->validatePassword(Yii::$app->request->getBodyParam('password'))) {
                $result = [
                    'success' => 0,
                    'message' => 'Incorrect password'
                ];
                throw new ServerErrorHttpException($result);
            } else {
                $token = new Token();
                $token->token = Yii::$app->getSecurity()->generateRandomString(12);
                $token->user_id = $user->user_id;
                $oldToken = Token::getTokenByUserId($token->user_id);
                if ($oldToken->token) {
                    $oldToken->updateAttributes([
                        'token' => $token->token,
                        'expired_at' => time() + 3600 * 5
                    ]);
                    Token::deleteAll('expired_at < ' . time());
                    $result = [
                        'success' => 1,
                        'username' => $user->user_name,
                        'payload' => $user,
                        'token' => $token->token
                    ];
                    return $result;
                }
                $token->expired_at = time() + 3600 * 5;
                $token->save();
                Token::deleteAll('expired_at < ' . time());
                $result = [
                    'success' => 1,
                    'username' => $user->user_name,
                    'payload' => $user,
                    'token' => $token->token
                ];
            }
        return $result;
    }
}
