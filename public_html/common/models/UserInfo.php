<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_info".
 *
 * @property int $user_id
 * @property string $male
 * @property string $birth
 * @property string $favour_desc
 *
 * @property User $user
 */
class UserInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['birth'], 'safe'],
            [['male'], 'string', 'max' => 45],
            [['favour_desc'], 'string', 'max' => 255],
            [['user_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'male' => 'Male',
            'birth' => 'Birth',
            'favour_desc' => 'Favour Desc',
        ];
    }

    public function fields()
    {
        return [
            'male',
            'birth',
            'favour_desc'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return UserInfoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserInfoQuery(get_called_class());
    }
}
