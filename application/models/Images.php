<?php

/*
статусы
0 - добавлен, ожидает скачивания
10 - скачан, на расмотрении
11 - отвергнут
15 - опубликован
*/
class Images extends CI_Model {

	public function addImage($img_remout_url,$img_thumbnail_remout,$img_owner_id,$img_owner_name,$img_added)
	{
		return $this->db->insert('images', array(
							'cont_type' => 'picture',
							'cont_remout_url' => $img_remout_url,
							'cont_thumbnail_remout' => $img_thumbnail_remout,
							'cont_owner_id' => $img_owner_id,
							'cont_owner_name' => $img_owner_name,
							'cont_added' => $img_added)
							);
	}

	public function addFile($remout_url,$local_url,$owner_id,$owner_name,$time)
	{
		return $this->db->insert('images', array(
							'cont_type' => 'file',
							'cont_remout_url' => $remout_url,
							'cont_file_name' => $local_url,
							'cont_owner_id' => $owner_id,
							'cont_owner_name' => $owner_name,
							'cont_added' => $time)
							);
	}

	public function addText($text,$owner_id,$owner_name,$time)
	{
		return $this->db->insert('images', array(
							'cont_type' => 'text',
							'cont_remout_url' => $text,
							'cont_owner_id' => $owner_id,
							'cont_owner_name' => $owner_name,
							'cont_added' => $time,
							'cont_status' => 10)
							);
	}


	public function get_all_rows(){
		$array = null;
		$query = $this->db->query('SELECT * FROM images');
		foreach($query->result_array() as $row)
        {
            $array[] = $row;
        }
        return $array;
	}

	public function get_by_status($status){
		$array = null;
		$query = $this->db->query('SELECT * FROM images WHERE cont_status='.$status);
		foreach($query->result_array() as $row)
        {
            $array[] = $row;
        }
        return $array;
	}

	public function getRemoatImages(){
		$array = null;
		$query = $this->db->query('SELECT * FROM images WHERE cont_type="picture" AND cont_status=0');
		foreach($query->result_array() as $row)
        {
            $array[] = $row;
        }
        return $array;
	}

	public function getRemoatFiles(){
		$array = null;
		$query = $this->db->query('SELECT * FROM images WHERE cont_type="file" AND cont_status=0');
		foreach($query->result_array() as $row)
				{
						$array[] = $row;
				}
				return $array;
	}

	public function getImagesByID($img_id){
		$array = null;
		$query = $this->db->query('SELECT * FROM images WHERE cont_id ='.$img_id);
		foreach($query->result_array() as $row)
        {
            $array[] = $row;
        }
        return $array;
	}

	public function deleteById($cont_id)
	{
		$query = $this->db->query('DELETE FROM images WHERE cont_id = '.$cont_id);
	}

	public function getLocalImages(){
		$array = null;
		$query = $this->db->query('SELECT * FROM images WHERE cont_status = 10 ORDER BY cont_id DESC');
		foreach($query->result_array() as $row)
    {
        $array[] = $row;
    }
    return $array;
	}

	public function getLocalImagesPosted(){
		$array = null;
		$query = $this->db->query('SELECT * FROM images WHERE cont_status = 15 ORDER BY cont_posted DESC');
		foreach($query->result_array() as $row)
		{
				$array[] = $row;
		}
		return $array;
	}

	public function getLocalImagesRejected(){
		$array = null;
		$query = $this->db->query('SELECT * FROM images WHERE cont_status = 11 ORDER BY cont_posted DESC');
		foreach($query->result_array() as $row)
		{
				$array[] = $row;
		}
		return $array;
	}

	public function setLocalUrl($img_id, $img_local_url){
		$query = "UPDATE images SET cont_local_url = '$img_local_url', cont_status = 10 WHERE cont_id = $img_id";
		$this->db->query($query);
	}
	
	public function setLocalThumbnail($img_id, $cont_thumbnail_local){
		$query = "UPDATE images SET cont_thumbnail_local = '$cont_thumbnail_local', cont_status = 10 WHERE cont_id = $img_id";
		$this->db->query($query);
	}

	public function getStatus($cont_id){
		$query = $this->db->query('SELECT cont_status FROM images WHERE cont_id='.$cont_id);
		foreach($query->result_array() as $row)
    {
        $array[] = $row;
    }
    return $array;
	}

	public function setStatus($cont_id, $status){
		$time = time();
		$query = "UPDATE images SET cont_status = '$status', cont_posted = '$time' WHERE cont_id = $cont_id";
		$this->db->query($query);
	}

}
?>
