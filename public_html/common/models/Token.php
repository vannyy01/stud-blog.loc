<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tokens".
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property int $expired_at
 *
 * @property Users $user
 */
class Token extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tokens';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'token'], 'required'],
            [['id', 'user_id', 'expired_at'], 'integer'],
            [['token'], 'string', 'max' => 255],
            [['token'], 'unique'],
            [['id', 'user_id'], 'unique', 'targetAttribute' => ['id', 'user_id']],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'token' => 'Token',
            'expired_at' => 'Expired At',
        ];
    }

    public function generateToken($expire)
    {
        $this->expired_at = $expire;
        $this->token = \Yii::$app->security->generateRandomString();
    }

    public function fields()
    {
        return [
            'token' => 'token',
            'expired' => function () {
                return date(DATE_RFC3339, $this->expired_at);
            },
        ];
    }


    /**
     * @inheritdoc
     * @return TokensQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TokensQuery(get_called_class());
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public static function getTokenByUserId($id)
    {
        return static::find()->where('user_id=' . $id)->one();
    }

}
