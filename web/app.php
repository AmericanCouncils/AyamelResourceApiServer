<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ClassLoader\ApcClassLoader;

$__start = microtime(true);

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

$apcLoader = new ApcClassLoader('ayamel.autoload', $loader);
$loader->unregister();
$apcLoader->register(true);

require_once __DIR__.'/../app/AppKernel.php';
require_once __DIR__.'/../app/AppCache.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request)->prepare($request);
$response->send();
$kernel->terminate($request, $response);

//die( ((microtime(true)-$__start) * 1000)."<br />".(memory_get_peak_usage() / 1024)."<br />".count(get_included_files()) );
