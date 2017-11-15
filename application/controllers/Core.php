<?php
defined('BASEPATH') OR exit('No direct script access allowed');

define("TOKEN", "4518fb349512ad77-46616d87c3236993-1d149c51b0672ad4");
define("GATE_SET_WEBHOOK", "https://chatapi.viber.com/pa/set_webhook");
define("GATE_SEND_MESSAGE", "https://chatapi.viber.com/pa/send_message");
define("GATE_ACCOUNT_INFO", "https://chatapi.viber.com/pa/get_account_info");
define("GATE_USER_DETAILS", "https://chatapi.viber.com/pa/get_user_details");
define("GATE_GET_ONLINE", "https://chatapi.viber.com/pa/get_online");
define("GATE_PA_POST", "https://chatapi.viber.com/pa/post");
define("LOCAL_GATE", "https://viberbot.ehost.tj/index.php/main/getImages/");

class Core extends CI_Controller {

	public function __construct()
    {
      parent::__construct();
		$this->load->helper('url');
		$this->load->helper('loger');
		$this->load->helper('jsons');
		$this->load->model('images');
		$this->load->model('messages');
		$this->load->model('subscribed');
		$this->load->helper('download');
		$this->load->helper('txt');
    }

	public function index()
	{

	}

	public function bot(){
		try{
			$post_body = $this->input->raw_input_stream;
			LogTo('gate.log',$post_body);

			if(empty($post_body)) {
				echo '{"status":"1","err_mess":"no post data"}';
				exit;
			}

			$json = json_decode($post_body);

			if(empty($json)) {
				echo '{"status":"1","err_mess":"no post data"}';
				exit;
			}
			
			$conf_json = json_decode(read_config('config/general.conf'));

			switch ($json->event){
				case "conversation_started":
					
					echo $this->welcome();

				break;
				case "message":
					if($json->message->type == "picture")
					{
						$this->send_post(GATE_SEND_MESSAGE,$this->picture_message($json));
						//$local_url = $this->download_img($json->message->media);
						$this->subscribed_add($json->sender);
						$remout_url = $json->message->media;
						$thumbnail_remout = $json->message->thumbnail;
						$owner_id = $json->sender->id;
						$owner_name = $json->sender->name;
						$time = $json->timestamp;
						$this->images->addImage($remout_url,$thumbnail_remout,$owner_id,$owner_name,$time);

					}elseif($json->message->type == "file")
					{
						$this->send_post(GATE_SEND_MESSAGE,$this->picture_message($json));
						$this->subscribed_add($json->sender);
						$owner_id = $json->sender->id;
						$owner_name = $json->sender->name;
						$time = $json->timestamp;
						$remout_url = $json->message->media;
						$file_name = $json->message->file_name;
						$this->images->addFile($remout_url,$file_name,$owner_id,$owner_name,$time);
					}elseif($json->message->type == "text"){
						if(substr(trim($json->message->text),0,2) == "/#"){
							$this->send_post(GATE_SEND_MESSAGE,$this->picture_message($json));
							$this->subscribed_add($json->sender);
							$owner_id = $json->sender->id;
							$owner_name = $json->sender->name;
							$time = $json->timestamp;
							$text = $json->message->text;
							$this->images->addText($text,$owner_id,$owner_name,$time);
						}elseif($json->message->text == "/admin"){
							$access_json = json_decode(read_config('config/access_list.conf'));
							if(in_array($json->sender_id, $access_json->access_list)){
								$send = "dmasd*ndf104(^$#%82349";
								srand(time());
								$rnd = rand();
								$hash = hash('sha512',$json->sender->id.$rnd.$send);
								$this->load->model('login');
								LogTo('error.log',"f");
								$this->login->addAuthToken($hash, time(), (time()+600));
								//$this->send_post(GATE_SEND_MESSAGE,$this->picture_message($json));
								$this->send_post(GATE_SEND_MESSAGE,$this->auth_message($json, $hash));
								//LogTo("error.log",$this->auth_message($json, $hash));
								//$this->subscribed_add($json->sender);
								//$owner_id = $json->sender->id;
								//$owner_name = $json->sender->name;
								//$time = $json->timestamp;
								//$text = $json->message->text;
								//$this->images->addText($text,$owner_id,$owner_name,$time);
							}
						}else{
							$reciver = "admin";
							$this->subscribed_add($json->sender);
							$sender = $json->sender->id;
							$name = $json->sender->name;
							$avatar = $json->sender->avatar;
							$text = $json->message->text;
							$this->messages->addMessage($reciver,$sender,$name,$avatar,$text);
						}
					}
				break;
				case "post" :
					$str = json_encode($json->body,JSON_UNESCAPED_SLASHES,JSON_UNESCAPED_UNICODE);
					echo $this->send_post(GATE_PA_POST,$str);
				break;
				case "get_info" :
					echo $this->send_post(GATE_ACCOUNT_INFO,'{}',TOKEN);
				break;
				case "notify":
					$str = json_encode($json->body,JSON_UNESCAPED_SLASHES,JSON_UNESCAPED_UNICODE);
					echo $this->send_post(GATE_SEND_MESSAGE,$str,TOKEN);
				break;
				case "chat":
					$str = json_encode($json->body,JSON_UNESCAPED_SLASHES,JSON_UNESCAPED_UNICODE);
					echo $this->send_post(GATE_SEND_MESSAGE,$str,TOKEN);
				break;
				case "subscribed":
					$this->subscribed_add($json->user);
				break;
			}
		}catch(Exception $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	private function picture_message($json){
		try{
			$conf_json = json_decode(read_config('config/general.conf'));
			$sender_id = $json->sender->id;
			$sender_name = $json->sender->name;
			$message_media = $json->message->media;
			$time = $json->timestamp;

			$mess["sender_id"] = $sender_id;
			$mess["name"] = $conf_json->bot_name;
			$mess["avatar"] = $conf_json->bot_avatar;
			$mess["text"] = "Благодарю тебя что поделился!";

			return picture_ansver($mess);
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}
	
	private function auth_message($json, $hash){
		try{
			$conf_json = json_decode(read_config('config/general.conf'));
			
			$sender_id = $json->sender->id;
			$mess["sender_id"] = $sender_id;
			$mess["name"] = $conf_json->bot_name;
			$mess["avatar"] = $conf_json->bot_avatar;
			$mess["url"] = 'https://viberbot.ehost.tj/index.php/auth/?hash='.$hash;
			$mess["text"] = "Амин панель";
			return auth_ansv1($mess);
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	private function echo_message($json){
		try{
			$conf_json = json_decode(read_config('config/general.conf'));
			$sender_id = $json->sender->id;
			$sender_name = $json->sender->name;
			$message_text = $json->message->text;
			$time = $json->timestamp;

			$mess["sender_id"] = $sender_id;
			$mess["name"] = $conf_json->bot_name;
			$mess["avatar"] = $conf_json->bot_avatar;
			$mess["text"] = $message_text;

			return picture_ansver($mess);
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	private function welcome(){
		try{
			$conf_json = json_decode(read_config('config/general.conf'));
			$mess["name"] = $conf_json->bot_name;
			$mess["avatar"] = $conf_json->bot_avatar;
			$mess['text'] = "Я жду от тебя мега прикола!!!";
			return welcome_message($mess);
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	private function send_post($gate, $json){
		try{
			if( $curl = curl_init() ) {
				curl_setopt($curl, CURLOPT_URL, $gate);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl,CURLOPT_HTTPHEADER, array(
					'X-Viber-Auth-Token: '.TOKEN
				));
				curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
				$out = curl_exec($curl);
				curl_close($curl);
				return $out;
			}
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	private function subscribed_add($json){
		try{
			if($this->subscribed->checkSubscribed($json->id)){
				$this->subscribed->addSubscribed($json->id, $json->name, $json->avatar);
				LogTo('subscribed.log','[ADD] -> '.$json->id.'   '.$json->name);
			}else{
				$this->subscribed->updateSubscribed($json->id,$json->avatar,$json->name);
			}
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	/*public function download_img($file_url)
	{	if(!empty($file_url)){
			$local_url = "./vidl";
			$name = md5(time()).'.jpg';
			$ch = curl_init($file_url);
			$fp = fopen($local_url.'/'.$name, 'wb');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			return $name;
		}else{
			return -1;
		}
	}*/

}
