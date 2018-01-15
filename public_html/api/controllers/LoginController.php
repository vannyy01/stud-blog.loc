<?php

namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use common\models\User;
use common\models\Token;
use yii\filters\Cors;

class LoginController extends Controller
{
    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    //'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['post', 'options'],
                ]
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    ['allow' => true,
                        'actions' => ['index', 'test'],
                        'roles' => ['?'],
                    ],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (in_array($action->id, ['index'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /* отключаем цсрф чтобы не иметь 400 ошибки при запросе*/
    public function actionIndex()
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
            } else {
                $token = new Token();
                $token->token = Yii::$app->getSecurity()->generateRandomString(12);
                $token->user_id = $user->user_id;
                $oldToken = Token::getTokenByUserId($token->user_id);
                if($oldToken->token){
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