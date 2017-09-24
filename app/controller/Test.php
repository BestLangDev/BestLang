<?php

use bestlang\core\BLController;
use bestlang\core\BLCookie;
use bestlang\core\BLSession;

class Test extends BLController
{
    public function testcookie()
    {
        $cookie_key = 'test_cookie';
        $ret = 'Current value: ' . $this->cookie($cookie_key) . '<br>';
        $new_value = time();
        $this->cookie($cookie_key, $new_value);
        $ret .= 'Set to: ' . $new_value . '<br>';
        $ret .= 'Get again: ' . $this->cookie($cookie_key);
        return $this->html($ret);
    }

    public function testsession()
    {
        $session_key = 'test_session';
        $ret = 'Current value: ' . $this->session($session_key) . '<br>';
        $new_value = time();
        $this->session($session_key, $new_value);
        $ret .= 'Set to: ' . $new_value . '<br>';
        $ret .= 'Get again: ' . $this->session($session_key);
        return $this->html($ret);
    }

    public function testjson()
    {
        return $this->json([
            'a' => 1, 'b' => true, 'c' => 'test'
        ]);
    }
}