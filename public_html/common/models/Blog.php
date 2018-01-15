<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "blogs".
 *
 * @property int $blog_id
 * @property int $user_id
 * @property string $blog_name
 * @property string $short_description
 * @property string $created_at
 *
 * @property Users $user
 * @property Posts[] $posts
 */
class Blog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'blogs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'blog_name', 'short_description'], 'required'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['blog_name'], 'string', 'max' => 100],
            [['short_description'], 'string', 'max' => 255],
            [['blog_name'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'blog_id' => 'Blog ID',
            'user_id' => 'User ID',
            'blog_name' => 'Blog Name',
            'short_description' => 'Short Description',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::className(), ['blog_id' => 'blog_id', 'user_id' => 'user_id']);
    }
    public function fields():array
    {
       return [
           'id' => 'blog_id',
           'name' => 'blog_name',
           //'s_desc' => 'short_description',
       ];
    }
}
