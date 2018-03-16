<?php
namespace common\rbac;
use yii\rbac\Rule;

class DeleteAuthorRule extends Rule
{
    public $name = 'canDeleteOwnPost'; // Имя правила

    public function execute($user_id, $item, $params)
    {
        return isset($params['post']) ? $params['post']->createdBy == $user_id : false;
    }
}