<?php

namespace common\models;

use Yii;
use yii\web\IdentityInterface;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "users".
 *
 * @property int $user_id
 * @property string $user_name
 * @property string $email
 * @property string $pass_hash
 * @property int $role
 * @property string $created_at
 * @property string $token
 *
 * @property Accounts $accounts
 * @property Blogs[] $blogs
 * @property Comments[] $comments
 * @property InterestsHasUsers[] $interestsHasUsers
 * @property Interests[] $interests
 * @property NewTable[] $newTables
 * @property UserInfo $userInfo
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 10;
    const STATUS_NULL = 0;
    const STATUS_ADMIN = 20;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_name', 'email', 'pass_hash'], 'required'],
            [['role'], 'integer'],
            [['created_at'], 'safe'],
            [['user_name', 'pass_hash'], 'string', 'max' => 45],
            [['email'], 'string', 'max' => 80],
            [['user_name'], 'unique'],
            [['email'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'email' => 'Email',
            'pass_hash' => 'Pass Hash',
            'role' => 'Role',
            'created_at' => 'Created At',
        ];
    }


    public function getId()
    {
        return $this->user_id;
    }

    public function getAuthKey()
    {

    }

    public function validateAuthKey($authKey)
    {
    }

    public function validatePassword($password)
    {
        return $password === $this->getPassword();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToken()
    {
        return $this->hasOne(Token::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->pass_hash;
    }

    public function setPassword($password){
        $this->pass_hash = sha1(sha1($password));
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccounts()
    {
        return $this->hasOne(Accounts::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBlogs()
    {
        return $this->hasMany(Blogs::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comments::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInterestsHasUsers()
    {
        return $this->hasMany(InterestsHasUsers::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInterests()
    {
        return $this->hasMany(Interests::className(), ['id' => 'interests_id'])->viaTable('interests_has_users', ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'user_id']);
    }

    public function fields()
    {
        return [
            'id' => 'user_id',
            'name' => 'user_name',
        ];
    }
    public function extraFields(): array
    {
        return [
            'email' => 'email',
            'info' => 'userInfo'
        ];
    }

    public static function findIdentity($id)
    {
    }

    /**
     * @param $token
     * @param null $type
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        Token::deleteAll('expired_at < ' . time());
        return static::find()
            ->joinWith('token t')
            ->andWhere(['t.token' => $token])
            ->andWhere(['>', 't.expired_at', time()])
            ->one();
    }

    public function loginByAccessToken($token, $type = null)
    {
        $identity = static::findIdentityByAccessToken($token, $type);
        if ($identity && $this->login($identity)) {

            return $identity;

        }
        return null;

    }

    /**
     * @param $email
     * @return null|static
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'role' => self::STATUS_ACTIVE]);
    }

    /**
     * @param $user_name
     * @return null|static
     */
    public static function findByUserName($user_name)
    {
        return static::findOne(['user_name' => $user_name, 'role' => self::STATUS_ACTIVE]);
    }

    /**
     * @return UserQuery|\yii\db\ActiveQuery
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }
}
