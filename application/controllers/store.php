<?php

if (!defined('BASEPATH'))
     exit('No direct script access allowed');

class Store extends CI_Controller {

     public function __construct() {

          parent::__construct();

          $this->rate_limit_error_msg = json_encode(array("error" => "We're over Twitter's silly 'rate' limit. Try back in an hour :("));


          // Loading TwitterOauth library. Delete this line if you choose autoload method.

          $this->load->library('twitteroauth');


          // Loading twitter configuration.

          $this->config->load('twitter');

          $public_methods = array("cacheTop" => true);

          //echo $this->uri->segment(2);

          if (!isset($public_methods[$this->uri->segment(2)])) {


               if ($this->session->userdata('access_token') && $this->session->userdata('access_token_secret')) {

                    // If user already logged in

                    $this->connection = $this->twitteroauth->create($this->config->item('twitter_consumer_token'), $this->config->item('twitter_consumer_secret'), $this->session->userdata('access_token'), $this->session->userdata('access_token_secret'));

                    $this->user = $this->session->userdata('twitter_screen_name');
                    
               } else {

// Unknown user

                    $error = array("error" => "You have to log in before editing anything!!!");

                    echo json_encode($error);

                    exit();
               }
          }
     }

     public function artist($id = NULL) {


          $this->load->spark('cache/2.0.0');

          ///// HANDLE REQUEST MEDHOD
          //if ($_SERVER['REQUEST_METHOD'] === "GET" ) {

          if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {

               $screen_name = $this->get_post_screen_name();

               //$screen_name = $id; 

               $artist_lookup = $this->get_screen_name(trim($screen_name));

               if ($artist_lookup['success'] === false) {

                    echo json_encode($artist_lookup);

                    return false;
                    
               } else {

                    $artist_twitter_name = $artist_lookup['screen_name'];
               }
               


               $params = array('slug' => $this->user . "-tweebop", "owner_screen_name" => $this->user, "screen_name" => $artist_twitter_name);

               $create = $this->twitteroauth->post('lists/members/create', $params);

               if (!empty($create->error)) {

                    $error = array("error" => $create->error);

                    echo json_encode($error);

                    return false;
                    
               } else {

                    $profile = $this->get_twitter_user($artist_twitter_name);

                    if (!empty($profile->error)) {

                         $error = array("error" => $profile->error);

                         echo json_encode($error);
                    
                    } else {

                         echo json_encode($profile);
                    }
               }
          }

          if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

               $screen_name = $id;

               $params = array('slug' => $this->user . "-tweebop", "owner_screen_name" => $this->user, "screen_name" => $screen_name);

               $destroy = $this->twitteroauth->post('lists/members/destroy', $params);

               //var_dump($destroy);
          }
     }

     public function get_post_screen_name() {

          $post_data = file_get_contents("php://input");

          $post_data = json_decode($post_data, true);

          $screen_name = $post_data['screen_name'];

          return $screen_name;
     }

     public function fetch($method = 'echo') {
          
         

          if (!empty($_GET['cursor'])) {

               $cursor = $_GET['cursor'];
          } else {

               $cursor = -1;
          }

          $lookup = array('slug' => $this->user . "-tweebop", "owner_screen_name" => $this->user, "cursor" => $cursor);
          
          if ($this->get_rate_limit('/lists/members' , 'list') == true) {

               echo $this->rate_limit_error_msg;

               exit();
          };

          $list = $this->twitteroauth->get('lists/members', $lookup);

          if (!empty($list->errors)) {

               $create_list = array('name' => $this->user . "-tweebop", 'mode' => 'public', 'description' => 'A collection of my favorite artists that I created on TweeBop! http://tweebop.pagodabox.com/');

               $auth = $this->twitteroauth->post('lists/create', $create_list);

               $list = $this->twitteroauth->get('lists/members', $lookup);

               if ($method == 'echo') {

                    echo json_encode($list);
                    
               } else {

                    return json_encode($list);
               }
               
          } else {

               if ($method === 'echo') {

                    echo json_encode($list);
                    
               } else {

                    return json_encode($list);
               }
          }
     }

     public function fetch_timeline() {

          if ($_GET['type'] == 'tweets') {

               $lookup = array('include_entities' => 1, 'slug' => $_GET['slug'], "per-page" => 20, "owner_screen_name" => $_GET['owner_screen_name']);

               if (!empty($_GET['max_id']) != 0) {

                    $lookup["max_id"] = intval($_GET['max_id']) - 1; 
               }
               
               if ($this->get_rate_limit('/lists/statuses' , 'list') == true) {

                    echo $this->rate_limit_error_msg;

                    exit();
               }

               $timeline = $this->twitteroauth->get('lists/statuses', $lookup);
               
          } else if ($_GET['type'] == 'artist') {

               $lookup = array('include_entities' => 1, 'screen_name' => $_GET['screen_name'], "per-page" => 20);
               
               

               if (!empty($_GET['max_id']) != 0) {

                    $lookup["max_id"] = intval($_GET['max_id']) - 1;
               }
               
               if ($this->get_rate_limit('/statuses/user_timeline', 'statuses')) {
                    
                    echo $this->rate_limit_error_msg;

                    exit();    
                    
               };

               $timeline = $this->twitteroauth->get('statuses/user_timeline', $lookup);
          }

          //echo $timeline; 

          echo json_encode($timeline);
     }

     public function echo_nest_lookup() {

          if ($_SERVER['REQUEST_METHOD'] === 'POST') {

               $response = $this->get_screen_name($_POST['artist']);

               $response['artist_lookup'] = $_POST['artist'];

               echo json_encode($response);
          }
     }

     private function get_screen_name($screen_name) {

          $this->config->load('echo_nest');

          $key = $this->config->item('echo_nest_key');

          $this->load->spark('restclient/2.1.0');

          $this->load->library('rest');

          $this->load->spark('cache/2.0.0');

          $this->rest->initialize(array('server' => 'http://developer.echonest.com/'));

          $params = array("name" => urldecode($screen_name), 'api_key' => $this->config->item('echo_nest_key'), 'format' => 'json');

          $artist = $this->cache->library('rest', 'get', array('api/v4/artist/twitter', $params), 2629740);


          if ($artist->response->status->code === 0) {

               if (empty($artist->response->artist->twitter)) {

                    $response = array("success" => false, "error" => 'We can\'t find that twitter handle, sorry!');

                    return $response;
               } else {

                    $response = array("success" => true, "screen_name" => $artist->response->artist->twitter);

                    return $response;
               }
          } else {

               $response = array("success" => false, "error" => $artist->response->status->message, "error_code" => $artist->response->status->code);

               if ($artist->response->status->code === 3) {

                    $this->cache->library('rest', 'get', array('api/v4/artist/twitter', $params), -1);
               }

               return $response;
          }

          //return $artist;   
     }

     public function get_twitter_user($screen_name) {

          $this->load->spark('restclient/2.1.0');

          $this->load->library('rest');

          $this->load->spark('cache/2.0.0');

          $this->rest->initialize(array('server' => 'https://api.twitter.com/'));

          $params = "screen_name=" . $screen_name;

          $tweets = $this->cache->library('rest', 'get', array('1/users/lookup.json', $params), 43200);

          if (!empty($tweets->error)) {

               $this->cache->library('rest', 'get', array('1/users/lookup.json', $params), -1);
          }

          return $tweets;
     }

     public function library() {

          $this->load->spark('cache/2.0.0');

          if ($this->cache->get($this->user) === 'FILE-UPLOAD-SUCCESS') {

               $error = array("error" => "You can only upload your iTunes library once per hour." , 'success' => false);

               echo json_encode($error);
               
               return false;
          }

          $this->load->helper('url');

          $this->load->helper('xml');

          //$path = file_get_contents("http://localhost/~stevearmstrong/tweebop/docs/library.xml", 'r');

          if (empty($_GET['qqfile'])) {

               $error = array("error" => "There was an error in uploading this file or you are accessing this page in an unconventional way. "  , 'success' => false);

               echo json_encode($error);
               
               return false;
          };

          $path = file_get_contents("php://input", "r");
          
          $artists = $this->parseItunes($path);

          $response['artists'] = array_keys($artists);

          $response['success'] = 'true';

          echo json_encode($response);
     }

     private function parseItunes($path) {

          $current_length = count(json_decode($this->fetch('return')));

          $artists = array();

          $l = simplexml_load_string($path);

          foreach ($l->dict->dict->dict as $track) {

               preg_match_all("/<key>Artist<\/key><.*>(.*)<\/.*>/", $track->asXML(), $match);

               foreach ($match[1] as $k => $v) {

                    /// Verify that the Artist field is defined

                    if (!empty($v)) {

                         /// Verify that the Artist has not been added already

                         if (!isset($artists[trim($v)])) {

                              $artists[trim($v)] = true;
                         }
                    }
               }
          }


          return $artists;
     }

     public function cacheTest() {
          
     }

     public function batch_twitter_list_add() {

          $this->load->spark('cache/2.0.0');

          $result['twitter_error'] = '';

          $list = explode(',', $_POST['list']);


          for ($i = 0; $i < count($list); $i += 100) {

               $batch = implode(',', array_slice($list, $i, 100));

               $params = array('slug' => $this->user . "-tweebop", "owner_screen_name" => $this->user, "screen_name" => $batch);

               $create = $this->twitteroauth->post('lists/members/create_all', $params);

               if (!empty($create->errors)) {

                    $result['twitter_error'] .= "Twitter error: " . $create->errors[0]->message . " <br />";
               } else {

                    /// Everything was successful, user must wait an hour before uploading another library

                    $this->cache->write('FILE-UPLOAD-SUCCESS', $this->user, 3600);
               }
          }

          $result['twitter_error'] === '' ? $result['twitter_success'] = true : $result['twitter_success'] = false;

          echo json_encode($result);
     }

     public function get_rate_limit($resource , $type) {

          $status = $this->connection->get('application/rate_limit_status');
          
          switch ($type) {
               
               case "list" :
                    
                    $resources = $status->resources->lists;
                    
                    break;
               
               case "statuses" :
                    
                    $resources = $status->resources->statuses;
                    
                    break;
               
          }; 
          
          if (!empty($resources->$resource->remaining)) {
               
               $remaining = $resources->$resource->remaining; 
               
          }
          

          if ($remaining <= 1) {

               return true;
               
          } else {

               return false;
          }
     }

     public function cacheTop($encryption_key) {

          /// Called by CRON

          $this->config->load('echo_nest');


          if ($encryption_key != $this->config->item('cron_key'))
               die('Access Denied');

          $key = $this->config->item('echo_nest_key');

          $this->load->spark('restclient/2.1.0');

          $this->load->library('rest');

          $this->rest->initialize(array('server' => 'http://developer.echonest.com/'));

          $params = array("results" => '29', 'api_key' => $this->config->item('echo_nest_key'), 'format' => 'json', 'start' => date('j') * 29);

          $hot = $this->rest->get("api/v4/artist/top_hottt", $params);
          
          //var_dump($hot);

          foreach ($hot->response->artists as $artist) {

               $this->get_screen_name($artist->name);
          }
     }

}