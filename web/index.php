<?php
/**
 * @copyright (c) 2012, Luxbet Pty Ltd. All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause
 */
require_once __DIR__ . '/../silex.phar';

use Symfony\Component\ClassLoader\UniversalClassLoader,
	Symfony\Component\HttpFoundation\Request,
	Symfony\Component\HttpFoundation\Response;

$loader = new UniversalClassLoader();

$loader->registerNamespace('Supervisor', __DIR__.'/../src');
$loader->register();

$app = new Silex\Application();

$debug = false;
$app['debug'] = $debug;

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->mount('/service', new Supervisor\ServiceControllerProvider());
$app->mount('/server', new Supervisor\ServerControllerProvider());

$app->get('/', function () use ($app) {
    return $app->redirect($app['url_generator']->generate('server_list'));
})->bind('home');

if (!$debug) {
	$app->error(function (\Exception $e, $code) {
		switch ($code) {
			case 404:
				$message = 'The requested page could not be found.';
				break;
			default:
				$message = 'We are sorry, but something went terribly wrong.';
		}

		return new Response($message, $code);
	});
}

$app->run();
