<?php
/**
 * @copyright (c) 2012, Luxbet Pty Ltd. All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause
 */
namespace Supervisor;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;

class ServerControllerProvider implements ControllerProviderInterface {

	public function connect(Application $app) {
		$controllers = new ControllerCollection();

		$supervisor = new API;

		$servers = include(__DIR__.'/../../config.php');

		foreach (array_keys($servers) as $server_id) {
			$servers[$server_id]['id'] = $server_id;
		}

		$controllers->get('/list.{_format}', function ($_format) use ($supervisor, $app, $servers) {
			if ($_format == 'json') {
				return $app->json($servers);
			} else {
				// use a closure to avoid leaking any vars into the template that we don't explicitly want
				return call_user_func(function() use ($app) {
					$url_root = $app['url_generator']->generate('home');
					ob_start();
					ob_implicit_flush(false);
					include(__DIR__.'/../../views/supervisorui.html.php');
					return ob_get_clean();
				});
			}
		})
			->bind('server_list')
			->value('_format', 'html');

		$controllers->get('/details/{server_id}', function ($server_id) use ($supervisor, $app, $servers) {
			$server_ip = $servers[$server_id]['ip'];

			$details = array_merge(array(
				'version' => $supervisor->getSupervisorVersion('127.0.0.1'),
				'pid' => $supervisor->getPID('127.0.0.1'),
				), $supervisor->getState('127.0.0.1'),
				$servers[$server_id]
			);
			return $app->json($details);
		});

		return $controllers;
	}
}