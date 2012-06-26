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
               
               $this->user = $this->session->userdata('twitter_screen_name');
          
               
          }  else {

// Unknown user
                    
               $error = array("error" => "You have to log in before editing anything!!!");

               echo json_encode($error);

               return false;
          
               
          }
          
          
     }

     public function artist($id = NULL) {
          
          $this->load->spark('cache/2.0.0');

          ///// HANDLE REQUEST MEDHOD

          if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {

               $screen_name = $this->get_post_screen_name();
          
               //$screen_name = $id; 
               
               $artist_twitter_name = $this->get_screen_name($screen_name);
               
               if ( $artist_twitter_name->response->status->code === 0 ) {
                   
                    $artist_twitter_name =  $artist_twitter_name->response->artist->twitter;
                    
               } else {
                    
                    $message = $artist_twitter_name->response->status->message;
                    
                    $error = array("error" => $message );

                    echo json_encode($error);
                    
                    return false;
                    
               }
               
               
               $params = array('slug' => $this->user . "-tweebop" , "owner_screen_name" => $this->user , "screen_name" => $artist_twitter_name); 
               
               $create = $this->twitteroauth->post('lists/members/create' , $params);
               
               //var_dump($create); 
               
               if (!empty($create->error) ) {
                     
                    $error = array("error" => $create->error );

                    echo json_encode($error);
                    
                    return false; 
                    
               } 
               
               
                    
               
               
          }

          if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

               //$screen_name = $this->get_post_screen_name();
               
               $params = array("screen_name" => $id, 'twitter_handle' => $user);

               $stored = $this->feeds->delete_artist($params);  
               
               $this->cache->delete("feed_" . $user);
               
          }
          
          
          
          //$lookup = array('slug' => $this->user . "-tweebop" , "owner_screen_name" => $this->user);

          //$list = $this->twitteroauth->get('lists/members' , $lookup);
               
          //echo json_encode($list->users);
          
     }

     public function get_post_screen_name() {

          $post_data = file_get_contents("php://input");

          $post_data = json_decode($post_data, true);

          $screen_name = $post_data['screen_name'];
          
          return $screen_name; 

     }

     public function fetch() {
          
          $lookup = array('slug' => $this->user . "-tweebop" , "owner_screen_name" => $this->user);
          
          $list = $this->twitteroauth->get('lists/members' , $lookup);
          
          if ( ! empty($list->errors) ) {
               
               $create_list = array( 'name' => $user . "-tweebop" , 'mode' => 'public' , 'description' => 'A collection of my favorite artists that I created on TweeBop!'); 
               
               $auth = $this->twitteroauth->post('lists/create' , $create_list);
               
               $list = $this->twitteroauth->get('lists/members' , $lookup);
               
               echo json_encode($list->users);
               
          } else { 
               
               echo json_encode($list->users);
               
          }

               
     }

     public function get_screen_name($screen_name) {
          
          $this->config->load('echo_nest'); 
          
          $key = $this->config->item('echo_nest_key'); 
          
          //echo $key; 

          $this->load->spark('restclient/2.1.0');

          $this->load->library('rest');

          $this->load->spark('cache/2.0.0');
          
          //url : 'http://developer.echonest.com/api/v4/artist/twitter' ,

          $this->rest->initialize(array('server' => 'http://developer.echonest.com/'));

          $params = array("name" => $screen_name , 'api_key' => $this->config->item('echo_nest_key') , 'format' => 'json'); 

          $artist = $this->cache->library('rest', 'get', array('api/v4/artist/twitter', $params) , 1 );
          
          //var_dump($artist);

          return $artist;   
          
     }
     

}