<?php
/*
 * Copyright IBM Corp. 2016-2017
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

 /**
  * This PHP file uses the Slim Framework to construct a REST API.
  * See Cloudant.php for the database functionality
  */
require 'vendor/autoload.php';
require_once('./Cloudant.php');
require_once('./AntiXSS.php');
$app = new \Slim\Slim();
$dotenv = new Dotenv\Dotenv(__DIR__);
try {
    $dotenv->load();
} catch (Exception $e) {
    error_log("No .env file found");
}
$app->get('/', function () {
    global $app;
    $app->render('index.html');
});

$app->get('/api/visitors', function () {
    global $app;
    $app->contentType('application/json');
    $visitors = array();
    if(Cloudant::Instance()->isConnected()) {
      $visitors = Cloudant::Instance()->get();
      foreach($visitors as $key => $value) {
          $visitors[$key] = AntiXSS::sanitizeInput($value);
      }
    }
    echo json_encode($visitors);
});

$app->post('/api/visitors', function() {
    global $app;
    $visitor = json_decode($app->request()->getBody(), true);
    $sanitizedName = AntiXSS::sanitizeInput($visitor['name']);
    if(Cloudant::Instance()->isConnected()) {
      Cloudant::Instance()->post($visitor);
      echo sprintf("Hello %s, I've added you to the database!", $sanitizedName);
    } else {
      echo sprintf("Hello %s!", $sanitizedName);
    }
});

$app->delete('/api/visitors/:id', function($id) {
    global $app;
    Cloudant::Instance()->delete($id);
    $app->response()->status(204);
});

$app->put('/api/visitors/:id', function($id) {
    global $app;
    $visitor = json_decode($app->request()->getBody(), true);
    $result = Cloudant::Instance()->put($id, $visitor);
    echo json_encode(AntiXSS::sanitizeInput($result));
});

$app->run();
