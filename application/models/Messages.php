<?php

/*
статусы
0 - добавлен, ожидает скачивания
10 - скачан, на расмотрении
11 - отвергнут
15 - опубликован
*/
class Messages extends CI_Model {


	public function __construct()
	{
		parent::__construct();
		$this->load->model('images');
		$this->load->helper('txt');
		$this->load->helper('loger');
		$this->load->helper('jsons');
	}

	public function addMessage($m_reciver, $m_owner_id, $m_owner_name, $img_owner_avatar, $m_message)
	{
    try{
		    return $this->db->insert('messages', array(
							'm_reciver' => $m_reciver,
							'm_owner_id' => $m_owner_id,
							'm_owner_name' => $m_owner_name,
							'm_owner_avatar' => $img_owner_avatar,
							'm_message' => $m_message,
							'm_time' => time())
							);
    }catch(Exception $e){
			LogTo('error.log',$e->getMessage());
		}
	}

  public function getMessages($a_id){
    $array = null;
    $query = $this->db->query('SELECT * FROM messages WHERE m_owner_id="'.$a_id.'" OR m_reciver ="'.$a_id.'" ORDER BY m_time ASC');
		foreach($query->result_array() as $row)
        {
            $array[] = $row;
        }
        return $array;
  }

  /*
  0 - Новое непрочитанное
  1 - Прочитанное
  */
  public function setMessageStatus($sender_id, $status){
	$query = 'UPDATE messages SET m_status = '.$status.' WHERE m_owner_id = "'.$sender_id.'"';
	$this->db->query($query);
  }
  
  public function getNewMessages(){
	$array = null;
    $query = $this->db->query('SELECT count(m_id) AS cnt, m_reciver, m_owner_id FROM messages WHERE m_status ="0" GROUP BY m_owner_id ORDER BY cnt DESC');
		foreach($query->result_array() as $row)
        {
            $array[] = $row;
        }
        return $array;
  }
  
	public function getMessCount(){
		$array = null;
		$query = $this->db->query('SELECT count(m_id) AS cnt FROM messages WHERE m_status ="0" AND NOT m_owner_id = "admin"');
		foreach($query->result_array() as $row)
        {
            $array[] = $row;
        }
        return $array;
	}
}
?>
