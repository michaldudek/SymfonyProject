<?php
namespace MD\Flavour\Bootstrap;

class Blocker
{

    protected $allowedIps = [
        '127.0.0.1',
        'fe80::1',
        '::1'
    ];

    public function __construct(array $allowedIps = [])
    {
        $this->allowedIps = array_merge($this->allowedIps, $allowedIps);
    }

    public function checkAccess(array $env)
    {
        // cli server is only run for dev
        if (php_sapi_name() === 'cli-server') {
            return true;
        }

        // check if IP is allowed
        $clientIp = isset($env['REMOTE_ADDR']) ? $env['REMOTE_ADDR'] : 0;
        if (in_array($clientIp, $this->allowedIps)) {
            return true;
        }

        // check if dev domain
        $domain = isset($env['HTTP_HOST']) ? $env['HTTP_HOST'] : '';
        if (preg_match('/\.dev$/', $domain)) {
            return true;
        }

        header('HTTP/1.0 403 Forbidden');
        exit('You are not allowed to access this file.');
    }

    public static function check(array $env, array $allowedIps = [])
    {
        $blocker = new static($allowedIps);
        $blocker->checkAccess($env);
    }
}
