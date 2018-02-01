<?php

namespace common\models;

use Yii;

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
 *
 * @property Posts $post
 * @property Users $user
 */
class Comments extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'post_id', 'rait', 'user_id'], 'integer'],
            [['post_id', 'comment_text', 'user_id'], 'required'],
            [['comment_text'], 'string'],
            [['created_at'], 'safe'],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Posts::className(), 'targetAttribute' => ['post_id' => 'post_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
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
    public function getPost()
    {
        return $this->hasOne(Posts::className(), ['post_id' => 'post_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }
    public function fields():array {
        return [
            'comment_id',
            'parent_id',
            'post_id' ,
            'comment_text' ,
            'created_at' ,
            'rait',
            'user',
        ];
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
