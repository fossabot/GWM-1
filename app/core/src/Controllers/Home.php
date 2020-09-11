<?php

namespace GWM\Core\Controllers;

class Home
{
    public function index()
    {
        $schema = new \GWM\Core\Schema('test_app');

        $model = new \GWM\Core\Models\Article($schema);
        $articles = $model->Select($schema);

        echo '<pre>';
        \var_dump($articles);
        echo '</pre>';
    }
}