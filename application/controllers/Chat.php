<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends CI_Controller {

	public function __construct()
		{
			parent::__construct();
			$this->load->model('messages');
			$this->load->model('images');
			$this->load->helper('txt');
			$this->load->helper('loger');
			$this->load->helper('jsons');
			$this->load->model('subscribed');
		}

	public function index()
	{
		try{
		  $id = base64_decode($this->input->get('oid'));
		  $data["base_url"] = base_url();
		  $data['messCount'] = $this->messages->getMessCount()[0]['cnt'];
		  $data['reciver_id'] = $id;
		  $data['mess_list'] = $this->creatChatList($id);
		  $data['content'] = $this->parser->parse('parts/chat_speak',$data,true);
		  $this->messages->setMessageStatus($id,1);
		  $this->template($data);
			}catch(Exeption $e){
				LogTo('error.log',$e->getMessage());
			}
	}

	public function chats(){
		$data["base_url"] = base_url();
		$data['messCount'] = $this->messages->getMessCount()[0]['cnt'];
		$data['list'] = $this->creatChats($this->messages->getNewMessages());
		$data['content'] = $this->parser->parse('parts/chats',$data,true);
		$this->template($data);
	}

	private function creatChats($new_mess){
		$ret = null;
		$all = $this->subscribed->getAllSubscribed();
		$col = 0;
		$ar = null;
		$counter = 0;
		foreach($all as $row){
			$col = 0;
			$b = 0;
			foreach($new_mess as $new){
				if($new['m_owner_id'] == $row['s_account_id']){
					$col = $new['cnt'];
				}
				
			}
			
			$r = '<tr>
				<td><img width="50px" src="'.((!empty($row['s_account_avatar']))? $row['s_account_avatar']:"https://viberbot.ehost.tj/dist/img/dava.png").'"></td>
				<td>'.$row['s_account_name'].'</td>
				<td>'.$row['s_account_id'].'</td>
				<td>'.(($col > 0)? '<span data-toggle="tooltip" title="" class="badge bg-red" data-original-title="'.$col.' New Messages">'.$col.'</span>' :'').'</td>
				<td><a href="https://viberbot.ehost.tj/index.php/chat/index/?oid='.base64_encode($row['s_account_id']).'" ><button type="button" class="btn btn-block btn-info">Чат</button></a></td>
				</tr>';
			$ar[$counter]['cont'] = $r;
			$ar[$counter]['col'] = $col;
			$counter++;
		}	
		
		$mesAr = $this->sortMes($ar);
		
		foreach($mesAr as $mes){
			$ret .= $mes['cont'];
		}
		return $ret;
	}
	
	private function SortMes($array){
		for($i=0;$i<count($array);$i++){
			for($j=0;$j<count($array)-1;$j++){
				if($array[$j]['col'] < $array[$j+1]['col']){
					
					$tmp = $array[$j]['col'];
					$tmp1 = $array[$j]['cont'];
					
					$array[$j]['col'] = $array[$j+1]['col'];
					$array[$j]['cont'] = $array[$j+1]['cont'];
					
					$array[$j+1]['col'] = $tmp;
					$array[$j+1]['cont'] = $tmp1;
				}
			}
		}
		return $array;
	}

  private function creatChatList($reciver){
    $list = $this->messages->getMessages($reciver);
    $data['base_url'] = base_url();
    if(empty($list)) return null;
    $ret = null;
    foreach($list as $mess){
      $data['name'] = $mess['m_owner_name'];
      if(!empty($mess['m_owner_avatar'])){
        $data['avatar'] = $mess['m_owner_avatar'];
      }else{
          $data['avatar'] = "https://viberbot.ehost.tj/dist/img/dava.png";
      }
      $data['text'] = $mess['m_message'];
      $data['time'] = date('d/m/Y H:i:s',$mess['m_time']);
      if($mess['m_reciver'] == "admin"){
        $ret .= $this->parser->parse('parts/chat_list',$data,true);
      }else{
        $ret .= $this->parser->parse('parts/chat_list_right',$data,true);
      }
    }
    return $ret;
  }

  public function send(){
    $reciver = $this->input->post('reciver');
    $text = $this->input->post('message');
    $this->SendTo($reciver,$text);
    redirect("https://viberbot.ehost.tj/index.php/chat/index/?oid=".base64_encode($reciver));
  }

  public function SendTo($reciver, $text){
    try{
      $conf = read_config('config/general.conf');
      $conf_json = json_decode($conf);
			$mess["sender_id"] = $reciver;
			$mess["name"] = $conf_json->bot_name;
			$mess["avatar"] = $conf_json->bot_avatar;
			$mess["text"] = $text;
      $json = '{"event":"chat",
        "body": '.picture_ansver($mess).'}';

      if( $curl = curl_init() ) {
        curl_setopt($curl, CURLOPT_URL, 'https://viberbot.ehost.tj/index.php/core/bot/');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        $out = curl_exec($curl);
        curl_close($curl);
        LogTo('ansv.log',$out);
      }

      $reciver = $reciver;
      $sender = "admin";
      $name = $conf_json->bot_name;
      $avatar = $conf_json->bot_avatar;
      $text = $text;
      $this->messages->addMessage($reciver,$sender,$name,$avatar,$text);
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}


  private function template($data)
  {
    try{
      $this->parser->parse('main', $data);
    }catch(Exeption $e){
      LogTo('error.log',$e->getMessage());
    }
  }

}
