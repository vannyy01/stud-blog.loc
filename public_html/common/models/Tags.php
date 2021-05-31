<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tags".
 *
 * @property int $id
 * @property string $tag
 *
 * @property PostsHasTags[] $postsHasTags
 * @property Posts[] $postsPosts
 */
class Tags extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tags';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag'], 'required'],
            [['tag'], 'string', 'max' => 80],
            [['tag'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tag' => 'Tag',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostsHasTags()
    {
        return $this->hasMany(PostsHasTags::className(), ['tags_id' => 'id']);
    }

    public static function getTagId($tag){
        return static::find()->select('id')->from('tags')->where(["tag" => $tag])->one();
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostsPosts()
    {
        return $this->hasMany(Posts::className(), ['post_id' => 'posts_post_id'])->viaTable('posts_has_tags', ['tags_id' => 'id']);
    }

    /**
     * @param $tag
     * @return array|Tags[]
     */
    public static function findByTag($tag):array
    {
        return static::find()->where("tag LIKE '%$tag%'")->limit(5)->all();
    }

    /**
     * @inheritdoc
     * @return TagsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TagsQuery(get_called_class());
    }
}
