<?php
declare(strict_types=1);

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii2tech\ar\linkmany\LinkManyBehavior;

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
 * @property int $tagsIds
 * @property int categoryIds
 * @property Comments[] $comments
 * @property Blog $blog
 * @property PostsHasCategory[] $postsHasCategories
 * @property Category[] $categories
 * @property PostsHasTags[] $postsHasTags
 * @property Tags[] $tags
 */
class Post extends ActiveRecord
{
    use UserTrait;

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'posts';
    }

    /**
     * @return array
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
            [['blog_id', 'user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Blog::className(), 'targetAttribute' => ['blog_id' => 'blog_id', 'user_id' => 'user_id']],
            [['tagsIds', 'categoryIds'], 'safe']
        ];
    }

    /**
     * @return array
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
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'linkTags' => [
                'class' => LinkManyBehavior::className(),
                'relation' => 'tags', // relation, which will be handled
                'relationReferenceAttribute' => 'tagsIds', // virtual attribute, which is used for related records specification
            ],
            'linkCategoryIds' => [
                'class' => LinkManyBehavior::className(),
                'relation' => 'category', // relation, which will be handled
                'relationReferenceAttribute' => 'categoryIds', // virtual attribute, which is used for related records specification
            ],
        ];
    }

    /**
     * @param $id
     * @return bool
     */
    public static function IncrementComment($id): bool
    {
        $comment = static::findOne(["post_id" => $id]);
        if ($comment) {
            return $comment->updateCounters(['rait' => 1]);
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public static function DecrementComment($id): bool
    {
        $comment = static::findOne(["post_id" => $id]);
        if ($comment) {
            return $comment->updateCounters(['rait' => -1]);
        }

        return false;

    }

    /**
     * @return string
     */
    public function getPostText():string
    {
        return str_replace(array("\r\n", "\r", "\n"), "<br>", $this->post_text);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments(): ActiveQuery
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
    public function getCategory(): ActiveQuery
    {
        return $this->hasMany(Category::className(), ['id' => 'category_id'])->viaTable('posts_has_category', ['post_id' => 'post_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags(): ActiveQuery
    {
        return $this->hasMany(Tags::className(), ['id' => 'tags_id'])->viaTable('posts_has_tags', ['posts_post_id' => 'post_id']);
    }


    /**
     * @inheritdoc
     * @return PostQuery the active query used by this AR class.
     */
    public static function find(): PostQuery
    {
        return new PostQuery(get_called_class());
    }

    /**
     * @param $post_name
     * @return null|static
     */
    public static function findByPostName($post_name)
    {
        return static::findOne(['post_name' => $post_name]);
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
            'category',
            'rait',
            'avatar'
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
            'blog' => 'blog',
            'tags'
        ];
    }
}
