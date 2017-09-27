<?php

namespace app\controller;

use bestlang\core\controller\BLController;

class ModelTest extends BLController
{
    public function index()
    {
        var_dump(\app\model\Test::test());
        return '';
    }
}