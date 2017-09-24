<?php

use bestlang\core\BLController;
use bestlang\core\BLCookie;
use bestlang\core\BLSession;

class Test extends BLController
{
    public function cookie()
    {
        $cookie_key = 'test_cookie';
        $ret = 'Current value: ' . BLCookie::get($cookie_key) . '<br>';
        $new_value = time();
        BLCookie::set($cookie_key, $new_value);
        $ret .= 'Set to: ' . $new_value . '<br>';
        $ret .= 'Get again: ' . BLCookie::get($cookie_key);
        return $ret;
    }

    public function session()
    {
        $session_key = 'test_session';
        $ret = 'Current value: ' . BLSession::get($session_key) . '<br>';
        $new_value = time();
        BLSession::set($session_key, $new_value);
        $ret .= 'Set to: ' . $new_value . '<br>';
        $ret .= 'Get again: ' . BLSession::get($session_key);
        return $ret;
    }
}