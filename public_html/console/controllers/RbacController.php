<?php
declare(strict_types=1);
namespace console\controllers;

use yii\console\Controller;
use Yii;
use common\models\User;
use yii\base\InvalidParamException;

class RbacController extends Controller
{
    public function actionInit()
    {
        if (!$this->confirm("Are you sure? It will re-create permissions tree.")) {
            return self::EXIT_CODE_ERROR;
        }

        $auth = Yii::$app->authManager;
        $auth->removeAll();


        $manageArticles = $auth->createPermission('managePosts');
        $manageArticles->description = 'Manage Posts';
        $auth->add($manageArticles);

        $manageUsers = $auth->createPermission('manageUsers');
        $manageUsers->description = 'Manage users';
        $auth->add($manageUsers);

        $moderator = $auth->createRole('moderator');
        $moderator->description = 'Moderator';
        $auth->add($moderator);
        $auth->addChild($moderator, $manageArticles);

        $admin = $auth->createRole('admin');
        $admin->description = 'Administrator';
        $auth->add($admin);
        $auth->addChild($admin, $moderator);
        $auth->addChild($admin, $manageUsers);
    }

    public function actionAssign($role, $username):void
    {
        $user = User::find()->where(['user_name' => $username])->one();
        if (!$user) {
            throw new InvalidParamException("There is no user \"$username\".");
        }

        $auth = Yii::$app->authManager;
        $roleObject = $auth->getRole($role);
        if (!$roleObject) {
            throw new InvalidParamException("There is no role \"$role\".");
        }

        $auth->assign($roleObject, $user->user_id);
    }
}