<?php

namespace api\models;

use Codeception\Lib\Generator\PageObject;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Post;

class PostSearch extends Post
{
    public function rules():array
    {
        return [
            [['post_name'], 'safe'],
            [['post_name'], 'string'],
            [['post_id'], 'safe'],
            [['post_id'], 'integer'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
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

        $query->andFilterWhere(['like', 'post_name', $this->post_name]);
        $query->andFilterWhere(['like', 'post_id', $this->post_id]);
        return $dataProvider;
    }

    public function formName()
    {
        return 's';
    }

}
