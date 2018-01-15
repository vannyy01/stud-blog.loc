<?php

namespace app\models\post;

use Yii;

/**
 * This is the model class for table "posts".
 *
 * @property int $post_id
 * @property int $blog_id
 * @property int $user_id
 * @property string $post_name
 * @property int $category_id
 * @property string $post_text
 * @property string $created_at
 * @property int $rait
 *
 * @property Comments[] $comments
 * @property Blogs $blog
 */
class Post extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'posts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['blog_id', 'user_id', 'post_name', 'category_id', 'post_text'], 'required'],
            [['blog_id', 'user_id', 'category_id', 'rait'], 'integer'],
            [['post_text'], 'string'],
            [['created_at'], 'safe'],
            [['post_name'], 'string', 'max' => 255],
            [['blog_id', 'user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Blogs::className(), 'targetAttribute' => ['blog_id' => 'blog_id', 'user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'post_id' => 'Post ID',
            'blog_id' => 'Blog ID',
            'user_id' => 'User ID',
            'post_name' => 'Post Name',
            'category_id' => 'Category ID',
            'post_text' => 'Post Text',
            'created_at' => 'Created At',
            'rait' => 'Rait',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comments::className(), ['posts_post_id' => 'post_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBlog()
    {
        return $this->hasOne(Blogs::className(), ['blog_id' => 'blog_id', 'user_id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\post\PostQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\post\PostQuery(get_called_class());
    }
}
