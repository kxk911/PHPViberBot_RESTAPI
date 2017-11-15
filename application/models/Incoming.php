<?php
class Incoming extends CI_Model {
	
	public function addImage($img_remout_url,$img_local_url,$img_owner_id,$img_owner_name)
	{
		$img_added = time();
		
		$this->db->insert('images', array(
							'img_remout_url' => $img_remout_url, 
							'img_local_url' => $img_local_url, 
							'img_owner_id' => $img_owner_id,
							'img_owner_name' => $img_owner_name,
							'img_added' => $img_added)
							);
	}
	
	public function getRemoatImages(){
		$array = null;
		$query = $this->db->query('SELECT * FROM images WHERE img_local_url IS NULL');
		foreach($query->result_array() as $row)
        {
            $array[] = $row;
        }
        return $array;
	}
	
	public function getImagesByID($img_id){
		$array = null;
		$query = $this->db->query('SELECT * FROM images WHERE img_id ='.$img_id);
		foreach($query->result_array() as $row)
        {
            $array[] = $row;
        }
        return $array;
	}
	
		public function getLocalImages(){
		$array = null;
		$query = $this->db->query('SELECT * FROM images WHERE img_local_url IS NOT NULL ORDER BY img_id DESC');
		foreach($query->result_array() as $row)
        {
            $array[] = $row;
        }
        return $array;
	}
	
	public function setLocalUrl($img_id, $img_local_url){
		
		$query = "UPDATE images SET img_local_url = '$img_local_url' WHERE img_id = $img_id";
		$this->db->query($query);
	}
}
?>