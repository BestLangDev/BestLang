<?php

use bestlang\core\BLController;

class Main extends BLController
{
    public function index()
    {
        return 'Hello BestLang! ' . $this->request()->get('test');
    }
}