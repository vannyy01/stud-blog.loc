<?php

namespace app\models\queries\post;

/**
 * This is the ActiveQuery class for [[\app\models\post\Post]].
 *
 * @see \app\models\post\Post
 */
class PostQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \app\models\post\Post[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\post\Post|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
