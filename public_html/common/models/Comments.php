<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "comments".
 *
 * @property int $comment_id
 * @property int $parent_id
 * @property int $post_id
 * @property string $comment_text
 * @property string $created_at
 * @property int $rait
 * @property int $user_id
 * @property Post $post
 * @property User $user
 */
class Comments extends \yii\db\ActiveRecord
{
    use UserTrait;

    /**
     * @return string
     */
    public static function tableName():string
    {
        return 'comments';
    }

    /**
     * @return array
     */
    public function rules():array
    {
        return [
            [['parent_id', 'post_id', 'rait', 'user_id'], 'integer'],
            [['post_id', 'comment_text', 'user_id'], 'required'],
            [['comment_text'], 'string'],
            [['created_at'], 'safe'],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::className(), 'targetAttribute' => ['post_id' => 'post_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels():array
    {
        return [
            'comment_id' => 'Comment ID',
            'parent_id' => 'Parent ID',
            'post_id' => 'Post ID',
            'comment_text' => 'Comment Text',
            'created_at' => 'Created At',
            'rait' => 'Rait',
            'user_id' => 'User ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPost():ActiveQuery
    {
        return $this->hasOne(Post::className(), ['post_id' => 'post_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser():ActiveQuery
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return array
     */
    public function fields(): array
    {
        return [
            'comment_id',
            'parent_id',
            'post_id',
            'comment_text',
            'created_at',
            'rait',
            'user',
            'avatar'
        ];
    }

    /**
     * @param $id
     * @return bool
     */
    public static function IncrementComment($id):bool
    {
        $comment = self::findOne(["comment_id" => $id]);
        if($comment){
           return $comment->updateCounters(['rait' => 1]);
        } else {
            return false;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public static function DecrementComment($id):bool
    {
        $comment = self::findOne(["comment_id" => $id]);
        if($comment){
            return $comment->updateCounters(['rait' => -1]);
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     * @return CommentsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CommentsQuery(get_called_class());
    }
}
