<?php

$__start = microtime(true);

require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';
require_once __DIR__.'/../app/AppCache.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request)->prepare($request);
$response->send();
$kernel->terminate($request, $response);

//die( ((microtime(true)-$__start) * 1000)."<br />".(memory_get_peak_usage() / 1024)."<br />".count(get_included_files()) );
