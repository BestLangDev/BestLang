<?php

namespace app\controller;

use bestlang\core\controller\BLController;
use bestlang\core\util\BLRequest;

class ModelTest extends BLController
{
    public function testinsert()
    {
        $obj = new \app\model\Test();
        $obj->data([
            'strcol' => 'test',
            'intcol' => 2333,
            'notexist' => 'test'
        ]);
        $id = $obj->save();
        $obj->data([
            'intcol' => 6666,
            'dtcol' => '2017/01/01'
        ]);
        $obj->save();
        return $this->html('Success, id = ' . $id);
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