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
          
               $screen_name = $id; 
               
               $artist_twitter_name = $this->get_screen_name($screen_name);
               
               //var_dump($artist_twitter_name); 
               
               if ( $artist_twitter_name->response->status->code === 0 ) {
                    
                    if (empty($artist_twitter_name->response->artist->twitter)) {
                         
                         
                         $error = array("error" => 'We can\'t find that twitter handle, sorry!' );  
                         
                         echo json_encode($error);
                    
                         return false;
                         
                         
                    } else {
                         
                         $artist_twitter_name =  $artist_twitter_name->response->artist->twitter;
                         
                    }
                    
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
                    
               } else {
                    
                    $profile = $this->get_twitter_user($artist_twitter_name); 
                    
                    echo json_encode($profile[0]); 
                    
               }
               
               
                    
               
               
          }

          if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

               $screen_name = $id;
               
               $params = array('slug' => $this->user . "-tweebop" , "owner_screen_name" => $this->user , "screen_name" => $screen_name); 
               
               $destroy = $this->twitteroauth->post('lists/members/destroy' , $params);

          //var_dump($destroy);
               
               
          }
          
          
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
          
          //var_dump(urldecode($screen_name)); 
          
          //url : 'http://developer.echonest.com/api/v4/artist/twitter' ,

          $this->rest->initialize(array('server' => 'http://developer.echonest.com/'));

          $params = array("name" => urldecode($screen_name) , 'api_key' => $this->config->item('echo_nest_key') , 'format' => 'json'); 

          $artist = $this->cache->library('rest', 'get', array('api/v4/artist/twitter', $params) , 1 );
          
          //var_dump($artist);

          return $artist;   
          
     }
     
     public function get_twitter_user($screen_name) {

          $this->load->spark('restclient/2.1.0');

          $this->load->library('rest');

          $this->load->spark('cache/2.0.0');

          $this->rest->initialize(array('server' => 'https://api.twitter.com/'));

          $params = "screen_name=" . $screen_name;

          $tweets = $this->cache->library('rest', 'get', array('1/users/lookup.json', $params) , 4320 );

          return $tweets;
     }
     
     public function library() { 
          
          $this->load->helper('url'); 
          
          $this->load->helper('xml'); 
          
          //$path = file_get_contents(base_url() . '/docs/library.xml' , "SimpleXMLElement"); 
          
          if (empty($_GET['qqfile'])) {
               
               $error = array("error" => "There was an error in uploading this file or you are accessing this page in an unconventional way. "); 
               
               return json_encode($error); 
               
          } ; 
          
          
          
          $path = file_get_contents("php://input", "r");
          
          $l = simplexml_load_string($path);
          
          $artists = array(); 
          
          
          
          foreach ($l->dict->dict->dict as $track){
               
               preg_match_all("/<key>Artist<\/key><.*>(.*)<\/.*>/", $track->asXML(), $match);
               
               
               foreach ($match[1] as $k => $v ) {
                    
                    
                    if (!empty($v)) {
                         
                         if (!isset($artists['artists'][$v])) {
                              
                              $artists['artists'][$v] = true;
                              
                         }
                         
                            
                         
                    }
                    
                    
               }
               
                        
          }
          
          
          $artists['total'] = count($artists['artists']);
          
          $artists['success'] = true; 
          
          echo json_encode($artists);
          
          
          
     }
     

}