<?php

namespace BestLang\core\controller;

interface BLInterceptor
{
    public function before();

    public function after();
}