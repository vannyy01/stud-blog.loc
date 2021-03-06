<?php
declare(strict_types=1);

namespace api\models;

use Codeception\Lib\Generator\PageObject;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Post;

class PostSearch extends Post
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['post_name'], 'safe'],
            [['post_name'], 'string', 'max' => 255],
            [['post_id', 'blog_id'], 'safe'],
            [['post_id', 'blog_id'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios(): array
    {
        return Model::scenarios();
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params): ActiveDataProvider
    {
        $query = Post::find();

        $dataProvider = new ActiveDataProvider([
            'pagination' => [
                'pageSize' => 5,
            ],
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['blog_id' => $this->blog_id]);
        $query->andFilterWhere(['like', 'post_name', $this->post_name]);
        $query->andFilterWhere(['post_id' => $this->post_id]);
        return $dataProvider;
    }

    public function formName(): string
    {
        return 's';
    }

}
