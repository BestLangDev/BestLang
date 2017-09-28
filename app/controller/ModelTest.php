<?php

namespace app\controller;

use bestlang\core\controller\BLController;
use bestlang\core\util\BLRequest;

class ModelTest extends BLController
{
    public function testinsert()
    {
        \app\model\Test::insert([
            'strcol' => 'test',
            'intcol' => 2333,
            'notexist' => 'test'
        ]);
        return $this->html('Success');
    }

    public function testdelete()
    {
        if (\app\model\Test::delete(BLRequest::get('id')) !== false) {
            return $this->html('Success');
        } else {
            return $this->html('Error');
        }
    }
}