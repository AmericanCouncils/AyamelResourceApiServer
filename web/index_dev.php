<?php

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
/*
if (!in_array(@$_SERVER['REMOTE_ADDR'], array(
    '127.0.0.1',
    '::1',
))) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}
*/

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ClassLoader\ApcClassLoader;

ini_set('display_errors', 'on');
$__start = microtime(true);

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
$apcLoader = new ApcClassLoader('ayamel.autoload', $loader);
$loader->unregister();
$apcLoader->register(true);

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request)->prepare($request);
$response->send();
$kernel->terminate($request, $response);

//die( ((microtime(true)-$__start) * 1000)."<br />".(memory_get_peak_usage() / 1024)."<br />".count(get_included_files()) );
