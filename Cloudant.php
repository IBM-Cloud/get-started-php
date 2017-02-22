<?php
/*
 * Copyright IBM Corp. 2017
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
require_once('./sag/Sag.php');

/**
 * Class to handle performing basic CRUD operations on a Couch DB.
 * This class uses the Sag library to talk to the Couch DB.
 */
final class Cloudant {
	private static $inst = null;
    private $sag;
		private $db_exists = false;

    public static function Instance() {
        if (self::$inst === null) {
            self::$inst = new Cloudant();
        }
        return self::$inst;
    }

		public function isConnected() {
			return $this->db_exists;
		}


    private function __construct() {
		#If running locally enter your own host, port, username and password


		$host = getenv('CLOUDANT_HOST');
		$port = '443';
		$username = getenv('CLOUDANT_USERNAME');
		$password = getenv('CLOUDANT_PASSWORD');
		if($vcapStr = getenv('VCAP_SERVICES')) {
			$vcap = json_decode($vcapStr, true);
			foreach ($vcap as $serviceTypes) {
				foreach ($serviceTypes as $service) {
					if($service['label'] == 'cloudantNoSQLDB') {
						$credentials = $service['credentials'];
						$username = $credentials['username'];
						$password = $credentials['password'];
						$parsedUrl = parse_url($credentials['url']);
						$host = $parsedUrl['host'];
						$port = isset($parsedUrl['port']) ?
						$parsedUrl['port'] : $parsedUrl['scheme'] == 'http' ?
						'80' : '443';
						break;
					}
				}
			}
		}
		$this->sag = new Sag($host, $port);
		$this->sag->useSSL(true);
		$dbsession = $this->sag->login($username, $password);
		try {
			$this->sag->setDatabase('mydb', true);
			$this->createView();
			$this->db_exists = true;
		} catch (Exception $e) {
			$this->db_exists = false;
		}
    }

    /**
	 * Transforms the Visitor JSON from the DB to the JSON
	 * the client will expect.
	 */
    private function toClientVisitor($couchVisitor) {
		$clientVisitor = array('id' => $couchVisitor->id);
		$clientVisitor['name'] = $couchVisitor->value->name;
		return $clientVisitor;
	}

	/**
	 * Creates a view to use in the DB if one does not already exist.
	 */
	private function createView() {
		try {
			$view = $this->sag->get('_design/visitors');
		} catch(SagCouchException $e) {
			$allvisitors = array('reduce' => '_count',
				'map' => 'function(doc){if(doc.name != null){emit(doc.order,{name: doc.name})}}');
			$views = array('allvisitors' => $allvisitors);
			$designDoc = array('views' => $views);
			$this->sag->put('_design/visitors', $designDoc);
		}
	}

	/**
	 * Gets all visitors from the DB.
	 */
	public function get() {
		$visitors = array();
		$docs = $this->sag->get('_design/visitors/_view/allvisitors?reduce=false')->body;
		foreach ($docs->rows as $row) {
			$visitors[] = $row->value->name;;
		}
		return $visitors;
	}

	/**
	 * Creates a new Visitor in the DB.
	 */
	public function post($visitor) {
		$resp = $this->sag->post($visitor);
		$visitor['id'] = $resp->body->id;
		return $visitor;
	}

	/**
	 * Updates a Visitor in the DB.
	 */
	public function put($id, $visitor) {
		$couchTodo = $this->sag->get($id)->body;
    	$couchTodo->name = $visitor['name'];
    	$this->sag->put($id, $couchTodo);
    	$couchTodo->id = $id;
    	unset($couchTodo->_id);
    	unset($couchTodo->_rev);
    	return $couchTodo;
	}

	/**
	 * Deletes a Visitor from the DB.
	 */
	public function delete($id) {
		$rev = $this->sag->get($id)->body->_rev;
		$this->sag->delete($id, $rev);
	}
}
