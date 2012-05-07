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

// CORS will send an OPTIONS request. Make sure we send back what the browser is expecting to allow the CORS request to work.
$app->before(function(Request $request) {
	if ($request->getMethod() == 'OPTIONS') {
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Headers: content-type, origin, accept');
		header('Content-Length: 0');
		header('Content-Type: text/plain');
		exit;
		// Would prefer to do this:
		/*return new Response('', 201, array(
			'Access-Control-Allow-Origin: *',
			'Access-Control-Allow-Methods: GET, POST, OPTIONS',
			'Access-Control-Allow-Headers: content-type, origin, accept',
			'Access-Control-Max-Age: 1728000',
			'Content-Length: 0',
			'Content-Type: text/plain',
		));*/
	}
});

$app->after(function(Request $request, Response $response) {
	// Add *some* support for JSONP, but we can't toggle services as we have no POST support
	if ($request->getMethod() === 'GET' && $request->get('callback') !== null) {
		$response->setContent($request->get('callback') . '(' . $response->getContent() .')');
	} else {
		// Let our CORS requests work with this app
		$response->headers->set('Access-Control-Allow-Origin', '*');
	}
});

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
