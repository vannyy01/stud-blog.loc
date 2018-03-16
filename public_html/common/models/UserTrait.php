<?php
namespace common\models;

trait UserTrait {
    /**
     * @return UserQuery
     */
    public function getUser(): UserQuery
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }
    /**
     * @return mixed
     */
    public function getAvatar(){
        return UserInfo::findOne(['user_id' => $this->user_id])["avatar"];
    }
}