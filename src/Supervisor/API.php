<?php
/**
 * @copyright (c) 2012, Luxbet Pty Ltd. All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause
 */
namespace Supervisor;

require_once(__DIR__.'/IXR_Library.php');

/**
 * @method public mixed getIdentification()
 * @method public mixed getSupervisorVersion()
 * @method public mixed getPID()
 * @method public mixed getState()
 */
class API {
	const STATE_STOPPED = 0;
	const STATE_STARTING = 10;
	const STATE_RUNNING = 20;
	const STATE_BACKOFF = 30;
	const STATE_STOPPING = 40;
	const STATE_EXITED = 100;
	const STATE_FATAL = 200;
	const STATE_UNKNOWN = 1000;

	private $methods;

	private $debug;

	public function __construct() {

	}

	public function __call($action, $arguments) {
		@list($server, $option) = $arguments;

		if ($action == 'multicall') {
			$client = new \IXR_ClientMulticall("http://$server:9001/RPC2/");
			$client->debug = true;
			foreach ((array)$option as $method) {
				$client->addCall($this->getNamespace($server, $action).'.'.$method);
			}

			if (!$client->query()) {
				return array('error' => array(
					'code' => $client->getErrorCode(),
					'msg' => $client->getErrorMessage()
				));
			}
		} else {
			$args = array(
				$this->getNamespace($server, $action).'.'.$action,
			);

			if ($option) {
				$args[] = $option;
			}

			$client = new \IXR_Client("http://$server:9001/RPC2/");
			//$client->debug = true;
			if (!call_user_func_array(array($client, 'query'), $args)) {
				return array('error' => array(
					'code' => $client->getErrorCode(),
					'msg' => $client->getErrorMessage()
				));
			}
		}

		return $client->getResponse();
	}

	private function getNamespace($server, $check_method) {
		if (empty($this->methods)) {
			$client = new \IXR_Client("http://$server:9001/RPC2/");
		//	$client->debug = true;
			if (!$client->query('system.listMethods')) {
				return false;
			} else {
				$methods = $client->getResponse();

				foreach ($methods as $method) {
					$parts = explode('.', $method);
					$this->methods[$parts[1]] = $parts[0];
				}
			}
		}

		return $this->methods[$check_method];
	}

}