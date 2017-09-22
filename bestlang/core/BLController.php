<?php

namespace bestlang\core;


class BLController
{
    protected function request()
    {
        return new BLRequest();
    }
}