<?php

if (!defined('BASEPATH'))
     exit('No direct script access allowed');

class Store extends CI_Controller {

     public function __construct() {

          parent::__construct();

          // Loading TwitterOauth library. Delete this line if you choose autoload method.

          $this->load->library('twitteroauth');

          // Loading twitter configuration.

          $this->config->load('twitter');

          if ($this->session->userdata('access_token') && $this->session->userdata('access_token_secret')) {

               // If user already logged in

               $this->connection = $this->twitteroauth->create($this->config->item('twitter_consumer_token'), $this->config->item('twitter_consumer_secret'), $this->session->userdata('access_token'), $this->session->userdata('access_token_secret'));
          } elseif ($this->session->userdata('request_token') && $this->session->userdata('request_token_secret')) {

               // If user in process of authentication

               $this->connection = $this->twitteroauth->create($this->config->item('twitter_consumer_token'), $this->config->item('twitter_consumer_secret'), $this->session->userdata('request_token'), $this->session->userdata('request_token_secret'));
          } else {

// Unknown user

               $this->connection = $this->twitteroauth->create($this->config->item('twitter_consumer_token'), $this->config->item('twitter_consumer_secret'));
          }
     }

     public function artist($id = NULL) {

          if ($this->session->userdata('access_token') && $this->session->userdata('access_token_secret')) {

               $user = $this->session->userdata('twitter_screen_name');
          } else {

               $error = array("error" => "You have to log in before editing anything!!!");

               echo json_encode($error);

               return false;
          }

          $this->load->model('feeds');
          
          $this->load->spark('cache/2.0.0');

          ///// HANDLE REQUEST MEDHOD

          if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {

               $screen_name = $this->get_post_screen_name();
               
               $params = array("screen_name" => $screen_name, 'twitter_handle' => $user);

               $stored = $this->feeds->insert_artist($params);

               if ($stored === 'success') {

                    $this->cache->delete("feed_" . $user);

                    $tweets = $this->get_screen_name($screen_name);

                    echo json_encode($tweets[0]);
                    
               } elseif ($stored === 'fail') {

                    $error = array("error" => "You've already added this artist to your feeds.");

                    echo json_encode($error);
                    
               }
               
               
          }

          if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

               //$screen_name = $this->get_post_screen_name();
               
               $params = array("screen_name" => $id, 'twitter_handle' => $user);

               $stored = $this->feeds->delete_artist($params);  
               
               $this->cache->delete("feed_" . $user);
               
          }
     }

     public function get_post_screen_name() {

          $post_data = file_get_contents("php://input");

          $post_data = json_decode($post_data, true);

          $screen_name = $post_data['screen_name'];
          
          return $screen_name; 

     }

     public function fetch() {

          if ($this->session->userdata('access_token') && $this->session->userdata('access_token_secret')) {

               $user = $this->session->userdata('twitter_screen_name');
          } else {

               $error = array("error" => "You have to log in before saving anything!!!");

               echo json_encode($error);

               return false;
          }

          $this->load->model('feeds');

          $params = array("twitter_handle" => $user);

          $query = $this->feeds->get_feed($params);

          $screen_names = array();

          foreach ($query->result() as $q) {

               $name = $q->screen_name;

               array_push($screen_names , $name); 

          }

          $feed  = $this->get_screen_name(implode(',' , $screen_names)); 

          echo json_encode($feed);
               
     }

     public function get_screen_name($screen_name) {

          $this->load->spark('restclient/2.1.0');

          $this->load->library('rest');

          $this->load->spark('cache/2.0.0');

          $this->rest->initialize(array('server' => 'https://api.twitter.com/'));

          $params = "screen_name=" . $screen_name;

          $tweets = $this->cache->library('rest', 'get', array('1/users/lookup.json', $params) , 4320 );

          return $tweets;
     }

}