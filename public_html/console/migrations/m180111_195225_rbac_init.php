<?php

use yii\db\Migration;

/**
 * Class m180111_195225_rbac_init
 */
class m180111_195225_rbac_init extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $auth = Yii::$app->authManager;

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

    public function down()
    {
        Yii::$app->authManager->removeAll();
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180111_195225_rbac_init cannot be reverted.\n";

        return false;
    }
    */
}
