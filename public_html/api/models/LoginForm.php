<?php

namespace api\models;

use common\models\Token;
use common\models\User;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;

    private $_user;

    const EXPIRED_TIME = 3600 * 24 ;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * @return Token|null
     */
    public function auth()
    {
        Token::deleteAll('expired_at < ' . time());
        if ($this->validate()) {
            $token = new Token();
            $token->user_id = $this->getUser()->user_id;
            $token->generateToken(time() + static::EXPIRED_TIME);
            $oldToken = Token::getTokenByUserId($token->user_id);
            if (isset($oldToken->token)) {
                return $oldToken->updateAttributes([
                    'token' => $token->token,
                    'expired_at' => time() + static::EXPIRED_TIME
                ]) ? $oldToken : null;
            }
                return $token->save() ? $token : null;
        } else {
            return null;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
            if(!$this->_user){
                $this->_user = User::findByEmail($this->username);
            }
        }

        return $this->_user;
    }
}
