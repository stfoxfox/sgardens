<?php
namespace common\models;
use Yii;
use yii\base\Model;
use common\components\MyExtensions\MyHelper;
/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    private $_user;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [[ 'password'], 'required','message'=>"Укажите пароль"],
            [['username'], 'required','message'=>"Укажите номер вашего телефона в верном формате"],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword', 'message' => 'Неправильный логин или пароль'],
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
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
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
            $phone = MyHelper::preparePhone($this->username);
            $this->_user = User::findByUsername($phone);
        }
        return $this->_user;
    }
}