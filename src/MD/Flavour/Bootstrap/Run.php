<?php
namespace MD\Flavour\Bootstrap;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

class Run
{

    public static function web($kernelClass, $env = 'prod', $debug = false)
    {
        if ($debug) {
            Debug::enable();
        }

        $kernel = new $kernelClass($env, $debug);
        $kernel->loadClassCache();

        $request = Request::createFromGlobals();
        $response = $kernel->handle($request);
        $response->send();
        $kernel->terminate($request, $response);
    }

    public static function console($kernelClass)
    {
        set_time_limit(0);

        $input = new ArgvInput();

        // decide env and debug info based on cli params
        $env = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: 'dev');
        $debug = getenv('SYMFONY_DEBUG') !== '0'
            && !$input->hasParameterOption(['--no-debug', ''])
            && $env !== 'prod';

        if ($debug) {
            Debug::enable();
        }

        $kernel = new $kernelClass($env, $debug);

        $application = new Application($kernel);
        $application->run($input);
    }
}
