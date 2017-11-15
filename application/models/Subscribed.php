<?php
class Subscribed extends CI_Model {


	public function __construct()
	{
		parent::__construct();
		$this->load->helper('loger');
	}

	public function addSubscribed($s_account_id, $s_account_name, $s_account_avatar)
	{
		try{
		    return $this->db->insert('subscribed', array(
							's_account_id' => $s_account_id,
							's_account_name' => $s_account_name,
							's_account_avatar' => $s_account_avatar)
							);
		}catch(Exception $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	public function checkSubscribed($s_account_id){
		$query = $this->db->query('SELECT * FROM subscribed WHERE s_account_id= "'.$s_account_id.'"');
		foreach($query->result_array() as $row)
		{
			return false;
		}
		return true;
	}

	public function getAllSubscribed(){
		$array = null;
		$query = $this->db->query('SELECT * FROM subscribed');
			foreach($query->result_array() as $row)
		{
			$array[] = $row;
		}
		return $array;
	}
	
	public function updateSubscribed($account_id, $avatar, $name){
		$query = "UPDATE subscribed SET s_account_name = '$name', s_account_avatar = '$avatar' WHERE s_account_id = '$account_id'";
		$this->db->query($query);
	}
}
?>
