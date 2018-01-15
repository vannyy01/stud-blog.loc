<?php
declare(strict_types=1);

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\User;
use common\models\Blog;

/**
 * This is the model class for table "posts".
 *
 * @property int $post_id
 * @property int $blog_id
 * @property int $user_id
 * @property string $post_name
 * @property string $short_description
 * @property string $post_text
 * @property string $created_at
 * @property int $rait
 *
 * @property Comments[] $comments
 * @property Blogs $blog
 * @property PostsHasCategory[] $postsHasCategories
 * @property Category[] $categories
 * @property PostsHasTags[] $postsHasTags
 * @property Tags[] $tags
 */
class Post extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'posts';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['blog_id', 'user_id', 'post_name', 'short_description', 'post_text'], 'required'],
            [['blog_id', 'user_id', 'rait'], 'integer'],
            [['post_text'], 'string'],
            [['created_at'], 'safe'],
            [['post_name'], 'string', 'max' => 255],
            [['short_description'], 'string', 'max' => 200],
            [['blog_id', 'user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Blogs::className(), 'targetAttribute' => ['blog_id' => 'blog_id', 'user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'post_id' => 'Post ID',
            'blog_id' => 'Blog ID',
            'user_id' => 'User ID',
            'post_name' => 'Post Name',
            'short_description' => 'Short Description',
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
    public function getBlog(): ActiveQuery
    {
        return $this->hasOne(Blog::className(), ['blog_id' => 'blog_id', 'user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostsHasCategories()
    {
        return $this->hasMany(PostsHasCategory::className(), ['post_id' => 'post_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['id' => 'category_id'])->viaTable('posts_has_category', ['post_id' => 'post_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostsHasTags()
    {
        return $this->hasMany(PostsHasTags::className(), ['posts_post_id' => 'post_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tags::className(), ['id' => 'tags_id'])->viaTable('posts_has_tags', ['posts_post_id' => 'post_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return PostQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PostQuery(get_called_class());
    }

    /**
     * @return array with needing fields
     */
    public function fields(): array
    {
        return [
            'post_id',
            'post_name',
            'short_description',
            'created_at',
            'category' => 'categories',
            'rait'
        ];
    }
    /**
     * @return array || consist fields that exist in depended table
     */
    public function extraFields(): array
    {
        return [
            'text' => 'post_text',
            'author' => 'user',
            'blog' => 'blog'
        ];
    }
}
