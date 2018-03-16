<?php
declare(strict_types=1);

namespace common\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "blogs".
 *
 * @property int $blog_id
 * @property int $user_id
 * @property string $blog_name
 * @property string $short_description
 * @property string $created_at
 *
 * @property User $user
 * @property Post[] $posts
 */
class Blog extends \yii\db\ActiveRecord
{
    use UserTrait;

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'blogs';
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['user_id', 'blog_name', 'short_description'], 'required'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['blog_name'], 'string', 'max' => 100],
            [['short_description'], 'string', 'max' => 255],
            [['blog_name'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
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
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts(): ActiveQuery
    {
        return $this->hasMany(Post::className(), ['blog_id' => 'blog_id', 'user_id' => 'user_id']);
    }

    /**
     * @param $name
     * @return null|static
     */
    public static function findByBlogName($name)
    {
        return static::findOne(['blog_name' => $name]);
    }

    /**
     * @param $id
     * @return static[]
     */
    public static function findBlogByUserID($id): array
    {
        return static::findAll(['user_id' => $id]);
    }

    /**
     * @return array
     */
    public function fields(): array
    {
        return [
            'id' => 'blog_id',
            'name' => 'blog_name'
        ];
    }

    /**
     * @return array
     */
    public function extraFields(): array
    {
        return [
            'short_description',
            'avatar',
            'author' => 'user',
        ];
    }

    /**
     *
     * @inheritdoc
     * @return BlogQuery the active query used by this AR class.
     */
    public static function find():BlogQuery
    {
        return new BlogQuery(get_called_class());
    }
}
