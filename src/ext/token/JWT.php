<?php

namespace BestLang\ext\token;

use BestLang\core\BLConfig;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;

class JWT extends BLToken
{
    private function getSigner()
    {
        try {
            return (new \ReflectionClass(BLConfig::get('token', 'options', 'signer')))->newInstance();
        } catch (\Exception $e) {
            return new Sha256();
        }
    }

    function signInternal($payload, $expire, $options = [])
    {

        $builder = new Builder();
        $builder->setIssuedAt(time())
            ->setExpiration(time() + $expire)
            ->set('bl_data', $payload)
            ->sign($this->getSigner(), BLConfig::get('token', 'options', 'key'));
        return (string) $builder->getToken();
    }

    function unsignInternal($token)
    {
        try {
            $parsed = (new Parser())->parse($token);
            $vd = new ValidationData();
            $vd->setCurrentTime(time());
            if (!$parsed->verify($this->getSigner(), BLConfig::get('token', 'options', 'key'))
                || !$parsed->validate($vd)) {
                return false;
            };
            return $parsed->getClaim('bl_data', false);
        } catch (\Exception $e) {
            return false;
        }
    }
}