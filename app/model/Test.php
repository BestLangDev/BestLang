<?php

namespace app\model;

use bestlang\core\model\BLModel;

class Test extends BLModel
{
    public static function test()
    {
        return self::fields();
    }
}