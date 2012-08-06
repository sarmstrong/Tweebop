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

          if ($this->get_rate_limit() == true) {

               echo $this->rate_limit_error_msg;

               exit();
          }

          $this->load->spark('cache/2.0.0');

          ///// HANDLE REQUEST MEDHOD
          //if ($_SERVER['REQUEST_METHOD'] === "GET" ) {

          if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {

               $screen_name = $this->get_post_screen_name();

               //$screen_name = $id; 

               $artist_lookup = $this->get_screen_name($screen_name);




               if ($artist_lookup['success'] === false) {

                    echo json_encode($artist_lookup);

                    return false;
               } else {

                    $artist_twitter_name = $artist_lookup['screen_name'];
               }




               $params = array('slug' => $this->user . "-tweebop", "owner_screen_name" => $this->user, "screen_name" => $artist_twitter_name);

               $create = $this->twitteroauth->post('lists/members/create', $params);



               //var_dump($create); 

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

                         echo json_encode($profile[0]);
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

          if ($this->get_rate_limit() == true) {

               echo $this->rate_limit_error_msg;

               exit();
          }

          if (!empty($_GET['cursor'])) {

               $cursor = $_GET['cursor'];
          } else {

               $cursor = -1;
          }

          $lookup = array('slug' => $this->user . "-tweebop", "owner_screen_name" => $this->user, "cursor" => $cursor);

          $list = $this->twitteroauth->get('lists/members', $lookup);

          if (!empty($list->errors)) {

               $create_list = array('name' => $this->user . "-tweebop", 'mode' => 'public', 'description' => 'A collection of my favorite artists that I created on TweeBop!');

               $auth = $this->twitteroauth->post('lists/create', $create_list);

               $list = $this->twitteroauth->get('lists/members', $lookup);

               if ($method == 'echo') {

                    echo json_encode($list);
               } else {

                    return json_encode($list);
               }
          } else {

               if ($method == 'echo') {

                    echo json_encode($list);
               } else {

                    return json_encode($list);
               }
          }
     }

     public function fetch_timeline() {

          if ($this->get_rate_limit() == true) {

               echo $this->rate_limit_error_msg;

               exit();
          }



          if ($_GET['type'] == 'tweets') {

               $lookup = array('include_entities' => 1, 'slug' => $_GET['slug'], "per-page" => 20, "owner_screen_name" => $_GET['owner_screen_name']);

               if (!empty($_GET['max_id']) != 0) {

                    $lookup["max_id"] = intval($_GET['max_id']) - 1;

                    //echo $lookup["max_id"] ; 
                    //echo "<br /> 229687334071828480 <br />"; 
               }

               $timeline = $this->twitteroauth->get('lists/statuses', $lookup);
          } else if ($_GET['type'] == 'artist') {

               $lookup = array('include_entities' => 1, 'screen_name' => $_GET['screen_name'], "per-page" => 20);

               if (!empty($_GET['max_id']) != 0) {

                    $lookup["max_id"] = intval($_GET['max_id']) - 1;
               }

               $timeline = $this->twitteroauth->get('statuses/user_timeline', $lookup);
          }

          //echo $timeline; 

          echo json_encode($timeline);
     }

     public function get_screen_name($screen_name) {

          $this->config->load('echo_nest');

          $key = $this->config->item('echo_nest_key');

          $this->load->spark('restclient/2.1.0');

          $this->load->library('rest');

          $this->load->spark('cache/2.0.0');

          $this->rest->initialize(array('server' => 'http://developer.echonest.com/'));

          $params = array("name" => urldecode($screen_name), 'api_key' => $this->config->item('echo_nest_key'), 'format' => 'json');

          $artist = $this->cache->library('rest', 'get', array('api/v4/artist/twitter', $params), 100000);


          if ($artist->response->status->code === 0) {

               if (empty($artist->response->artist->twitter)) {

                    $response = array("success" => false, "error" => 'We can\'t find that twitter handle, sorry!');

                    return $response;
               } else {

                    $response = array("success" => true, "screen_name" => $artist->response->artist->twitter);

                    return $response;
               }
          } else {

               $response = array("success" => false, "error" => $artist->response->status->message);

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

          ///$this->benchmark->mark('start');

          $this->load->helper('url');

          $this->load->helper('xml');

          if (empty($_GET['qqfile'])) {

               $error = array("error" => "There was an error in uploading this file or you are accessing this page in an unconventional way. ");

               return json_encode($error);
          };



          $path = file_get_contents("php://input", "r");


          //$path = file_get_contents(base_url() . '/docs/library.xml' , "SimpleXMLElement"); 


          $parse = $this->parseItunes($path);


          $flattened = array_keys($parse['to_add']);

          $result['twitter_error'] = '';

          for ($i = 0; $i < count($flattened); $i += 100) {

               $batch = implode(',', array_slice($flattened, $i, 100));

               $params = array('slug' => $this->user . "-tweebop", "owner_screen_name" => $this->user, "screen_name" => $batch);

               $create = $this->twitteroauth->post('lists/members/create_all', $params);

               if (!empty($create->errors)) {

                    $result['twitter_error'] .= "Twitter error: " . $create->errors[0]->message . " <br />";
               }
          }

          $result['twitter_error'] === '' ? $result['twitter_success'] = true : $result['twitter_success'] = false;

          $result['total'] = count($parse['to_add']);

          $result['success'] = true;

          $result['found'] = $parse['found'];

          $result['not_found'] = $parse['not_found'];

          echo json_encode($result);
     }

     private function parseItunes($path) {

          $current_length = count(json_decode($this->fetch('return')));

          $errors = '';

          $found = '';

          $to_add = array();

          /// Track artists to prevent duplicates          

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

                              /// Twitter only all allows lists to be 500 members long

                              if ($this->get_rate_limit() == true) {

                                   $errors .= "<li>" . $v . " We Exceeded Twitters stupid rate limit. Sorry!</li>";
                              } else if (count($to_add) + count($current_length) < 500) {



                                   $artist_lookup = $this->get_screen_name($v);

                                   if ($artist_lookup['success'] === false) {

                                        $errors .= "<li><strong>" . $v . "</strong>: " . $artist_lookup['error'] . "</li>";
                                   } else {

                                        /// Some librarys may have artists listed multiple ways, 
                                        /// Such as "The Roots, featuring Mos Def"
                                        /// Echo nest will return a valid result for these entriess

                                        if (!isset($to_add[$artist_lookup['screen_name']])) {

                                             $to_add[$artist_lookup['screen_name']] = true;

                                             $found .= "<li><strong>$v</strong> @" . $artist_lookup['screen_name'] . "</li>";
                                        }
                                   }
                              } else {

                                   $errors .= "<li>" . $v . " not added. Exceeded 500 member list limit.</li>";
                              }
                         }
                    }
               }
          }

          $result = array();

          $result['found'] = $found;

          $result['not_found'] = $errors;

          $result['to_add'] = $to_add;

          return $result;
     }

     public function get_rate_limit() {

          $status = $this->twitteroauth->get('account/rate_limit_status');

          if ($status->remaining_hits <= 1) {

               return true;
          } else {

               return false;
          }
     }

     public function cacheTop($offset) {

          /// Called by CRON

          $this->config->load('echo_nest');

          $key = $this->config->item('echo_nest_key');

          $this->load->spark('restclient/2.1.0');

          $this->load->library('rest');

          $this->rest->initialize(array('server' => 'http://developer.echonest.com/'));

          $params = array("results" => '90', 'api_key' => $this->config->item('echo_nest_key'), 'format' => 'json', 'start' => $offset);

          $hot = $this->rest->get("api/v4/artist/top_hottt", $params);

          foreach ($hot->response->artists as $artist) {

               $this->get_screen_name($artist->name);
          }
     }

}