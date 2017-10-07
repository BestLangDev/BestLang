<?php

namespace app\controller;

use bestlang\core\controller\BLController;

class Main extends BLController
{
    public function index()
    {
        return 'Hello BestLang!';
    }

    public function phpinfo()
    {
        phpinfo();
        return true;
    }
}