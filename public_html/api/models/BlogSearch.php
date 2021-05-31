<?php
declare(strict_types=1);

namespace api\models;

use common\models\Blog;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class BlogSearch extends Blog
{
    public function rules(): array
    {
        return [
            [['blog_name'], 'safe'],
            [['blog_name'], 'string', 'max' => 100],
            [['blog_id'], 'safe'],
            [['blog_id'], 'integer'],
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
        $query = Blog::find();

        $dataProvider = new ActiveDataProvider([
            'pagination' => [
                'pageSize' => 10,
            ],
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'blog_name', $this->blog_name]);
        $query->andFilterWhere(['blog_id' => $this->blog_id]);
        return $dataProvider;
    }

    /**
     * @return string
     */
    public function formName():string
    {
        return 's';
    }

}
