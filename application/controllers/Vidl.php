<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vidl extends CI_Controller {

	public function __construct()
		{
			parent::__construct();
			$this->load->model('images');
			$this->load->helper('txt');
			$this->load->helper('loger');
			$this->load->helper('jsons');
		}

	private function dwn(){
		try{
			LogTo('dwn.log', "GET FILE LIST");
			$remout_url = $this->images->getRemoatImages();
			if(!empty($remout_url)) {
				foreach($remout_url as $url){
					if($this->images->getStatus($url["cont_id"])[0]['cont_status'] == 0){
						$this->images->setStatus($url["cont_id"], 1);
						LogTo('dwn.log',$url['cont_remout_url']);
						$tmp_name = $this->download_img("./vidl",$url['cont_remout_url'],'jpg');
						$this->images->setLocalUrl($url["cont_id"], $tmp_name);
						
						$tmp_thumbnail = $this->download_img("./vidl/thumbnail",$url['cont_thumbnail_remout'],'jpg');
						$this->images->setLocalThumbnail($url["cont_id"], $tmp_thumbnail);
					}
				}
			}

			$remout_files = $this->images->getRemoatFiles();
			if(!empty($remout_files)){
				foreach($remout_files as $file){
					if($this->images->getStatus($file["cont_id"])[0]['cont_status'] == 0){
						$this->images->setStatus($file["cont_id"], 1);
						LogTo('dwn.log',$file['cont_remout_url']);
						$tmp_name = $this->download_img("./vidl",$file['cont_remout_url'],get_ext($file["cont_file_name"]));
						$this->images->setLocalUrl($file["cont_id"], $tmp_name);
					}
				}
			}
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	private function notify(){
		try{
			$last = read_config('config/last_notify');
			$conf = read_config('config/general.conf');
			$conf_json = json_decode($conf);
			if((time() - $last) > $conf_json->notification->moderation_notification_delay){
				write_config('config/last_notify',time());
				$conts = $this->images->get_by_status(10);
				$massArray['sender_id'] = $conf_json->notification->notification_reciver;
				$massArray['name'] = $conf_json->bot_name;
				$massArray['avatar'] = $conf_json->bot_avatar;
				$massArray['text'] = 'У вас '.count($conts).' непросмотренных публикаций!';
				$cont = picture_ansver($massArray);
				$json = '{"event":"notify",
				"body": '.$cont.' }';
				if( $curl = curl_init() ) {
					curl_setopt($curl, CURLOPT_URL, 'https://viberbot.ehost.tj/index.php/core/bot/');
					curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
					$out = curl_exec($curl);
					curl_close($curl);
				}
			}
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	public function index()
	{
		try{
			$this->dwn();
			$this->notify();
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	public function download_img($dir, $file_url, $ext)
	{
		try{
			if(!empty($file_url)){
				$local_url = $dir;//"./vidl";
				$ch = curl_init($file_url);
				$name = md5(time()).'.'.$ext;
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
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

}
