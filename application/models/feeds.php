<?php

class Feeds extends CI_Model {

    public function insert_artist($params) {

        $query = $this->db->get_where('feeds', $params);

        if ($query->num_rows === 0) {

            $this->db->insert('feeds', $params);
            
            return 'success'; 
            
        } else {
            
            return 'fail'; 
            
        }
        
    }
    
    public function delete_artist($params) {
        
        $query = $this->db->delete('feeds' , $params);
        
        
        
    }
    
    public function get_feed($params) { 
         
        $this->db->select('screen_name'); 
        
        $query = $this->db->get_where('feeds' ,  $params , 100);
        
        return $query; 
        
    }

}

?>
