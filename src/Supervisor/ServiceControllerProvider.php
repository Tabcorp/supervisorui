<?php
/**
 * @copyright (c) 2012, Luxbet Pty Ltd. All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause
 */
namespace Supervisor;

use Silex\Application,
	Silex\ControllerProviderInterface,
	Silex\ControllerCollection,
	Symfony\Component\HttpFoundation\Request;

class ServiceControllerProvider implements ControllerProviderInterface {

	public function connect(Application $app) {
		$controllers = new ControllerCollection();

		$supervisor = new API;

		$servers = require_once(__DIR__.'/../../config.php');

		$controllers->get('/{server}', function($server) use ($supervisor, $app) {
			//$server_ip = $servers[$server]['ip'];
			$services = $supervisor->getAllProcessInfo('127.0.0.1');
			return $app->json($services);
		});

		$controllers->post('/{server}/{service}', function (Request $request, $server, $service) use ($supervisor, $servers, $app) {
			$server_ip = $servers[$server]['ip'];

			if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
				$data = json_decode($request->getContent(), true);
			} else {
				return false;
			}

			$result = false;
			// Get the current state of the service
			$current_service = $supervisor->getProcessInfo('127.0.0.1', $service);
			if (isset($current_service['error'])) {
				$result = $current_service;
			} else {
				if (!(bool)$data['running'] && $current_service['state'] == $supervisor::STATE_RUNNING) {
					$result = $supervisor->stopProcess('127.0.0.1', $service);
				} else if ((bool)$data['running'] && $current_service['state'] != $supervisor::STATE_RUNNING) {
					$result = $supervisor->startProcess('127.0.0.1', $service);
				}
			}

			if (!$result) {
				$result = array('error' => array(
					'code' => '',
					'msg' => 'Error getting details for '.$service.' from '.$server_ip,
				));
			}

			return $app->json($result);
		});

		$controllers->get('/{server}/{service}', function($server, $service) use ($supervisor, $servers, $app) {
			$server_ip = $servers[$server]['ip'];

			return $app->json($supervisor->getProcessInfo('127.0.0.1', $service));
		});

		return $controllers;
	}


}