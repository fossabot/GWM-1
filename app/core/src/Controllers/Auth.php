<?php

namespace GWM\Core\Controllers;

class Auth extends \GWM\Core\Controller
{
    public function index()
    {
        $schema = new \GWM\Core\Schema('test_app');

        $user_input = \filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $pass_input = \filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

        $model = new \GWM\Core\Models\User($schema);

        if (\filter_has_var(INPUT_POST, 'username')) {
            $model->setUserName($user_input);
        }

        if (\filter_has_var(INPUT_POST, 'password')) {
            $model->setPassword($pass_input);
        }

        return $this->register();
    }
    
    private function register()
    {
        echo <<<HTML

<!DOCTYPE html>
<html>

<head>
    <style>

body {
    background: lightgrey;
}

.login {
    translate: -50% -50%;
    width: 350px;
    height: 210px;
    background: grey;
    position: absolute;
    left: 50%;
    top: 50%;
    border-radius: 3px;
    padding: 6px;
    color: white;
}

    </style>
</head>

<body>

    <div class="login">
        <h1>Auth</h1>

        <form method="POST" action="/auth">
        Username:<br>
        <input type="text" name="username"><br>
        Password:<br>
        <input type="password" name="password">
        <br><br>
        <input type="submit" value="Login">
      </form> 

    </div>
</body>

</html>

HTML;
    }
}