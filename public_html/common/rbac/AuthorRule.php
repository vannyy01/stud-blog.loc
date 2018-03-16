<?php
/**
 * Created by PhpStorm.
 * User: vannyy
 * Date: 28.02.18
 * Time: 11:33
 */
namespace common\rbac;
use yii\rbac\Rule;

class AuthorRule extends Rule
{
    public $name = 'canUpdateOwnPost'; // Имя правила

    public function execute($user_id, $item, $params)
    {
        return isset($params['post']) ? $params['post']->createdBy == $user_id : false;
    }
}
