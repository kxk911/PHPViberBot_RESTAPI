<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

	public function __construct()
  {
    parent::__construct();
		$this->load->helper('url');
		$this->load->helper('loger');
		$this->load->helper('jsons');
		$this->load->model('images');
		$this->load->helper('download');
		$this->load->helper('txt');
		$this->load->model('messages');
  }


	public function index()
	{
		try{
			$data["base_url"] = base_url();
			$data['messCount'] = $this->messages->getMessCount()[0]['cnt'];
			$data["content"] = $this->creatList();
			$this->template($data);
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}
	


	public function posted(){
		try{
			$data["base_url"] = base_url();
			$data['messCount'] = $this->messages->getMessCount()[0]['cnt'];
			$data["content"] = $this->creatList_posted();
			$this->template($data);
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	public function rejected(){
		try{
			$data["base_url"] = base_url();
			$data['messCount'] = $this->messages->getMessCount()[0]['cnt'];
			$data["content"] = $this->creatList_rejected();
			$this->template($data);
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	public function member_list(){
		try{
			$members = $this->get_pa_info();
			$data["base_url"] = base_url();
			$data['messCount'] = $this->messages->getMessCount()[0]['cnt'];
			$data["content"] = $this->creat_member_list($members);
			$this->template($data);
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	public function full($id){
		$data["base_url"] = base_url();
		$data['messCount'] = $this->messages->getMessCount()[0]['cnt'];
		$image = $this->images->getImagesByID($id);
		$data['name'] = $image[0]['cont_owner_name'];
		$data['time'] = date('d/m/Y H:i:s',$image[0]['cont_added']);
		$data['cont_id'] = $image[0]['cont_id'];


		if(!empty($image[0]['cont_local_url'])){
			$data['img'] = $image[0]['cont_local_url'];
			$data['content'] = $this->parser->parse('parts/full',$data,true);
		}else{
			$string = strip_tags($image[0]["cont_remout_url"]);
			$data['text'] = substr($string, 2, strlen($string));
			$data['content'] = $this->parser->parse('parts/full_text',$data,true);
		}
		$this->template($data);
	}

	public function config_general(){
		try{
			$data["base_url"] = base_url();
			$data['messCount'] = $this->messages->getMessCount()[0]['cnt'];
			$data["conf_name"] = "General config";
			$data["config"] = read_config('config/general.conf');
			$data["content"] = $this->parser->parse('parts/config',$data,true);
			$this->template($data);
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	public function log($type){
		try{
			$log = null;
			$data = null;
			switch ($type){
				case "gate":
					$log = read_config('Logs/gate.log');
					$data['log_name'] = "Gate Log";
				break;
				case "ansv":
					$log = read_config('Logs/ansv.log');
					$data['log_name'] = "Ansver Log";
				break;
				case "dwn":
					$log = read_config('Logs/dwn.log');
					$data['log_name'] = "Downloads Log";
				break;
				case "err":
					$log = read_config('Logs/error.log');
					$data['log_name'] = "Errors Log";
				break;
				case "sub":
					$log = read_config('Logs/subscribed.log');
					$data['log_name'] = "Subscribed Log";
				break;
				default :
					$log = null;
				break;
			}
			$data["base_url"] = base_url();
			$data['messCount'] = $this->messages->getMessCount()[0]['cnt'];
			$data['log'] = $log;
			$data['content'] = $this->parser->parse('parts/logs',$data,true);
			$this->template($data);
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	public function changelog(){
		$log = read_config('config/changelog.log');
		$data['log_name'] = "Changelog";
		$data["base_url"] = base_url();
		$data['messCount'] = $this->messages->getMessCount()[0]['cnt'];
		$data['log'] = $log;
		$data['content'] = $this->parser->parse('parts/logs',$data,true);
		$this->template($data);
	}
	
	private function creat_member_list($members){
		try{
			$data["base_url"] = base_url();
			$data['messCount'] = $this->messages->getMessCount()[0]['cnt'];
			$ret = '<div class="row">';
			$ret = $this->parser->parse('parts/secs',array('suc'=>$members->subscribers_count),true);
			foreach($members->members as $member){
				$data["name"] = $member->name;
				$data["role"] = $member->role;
				$data["avatar"] = $member->avatar;
				$ret .= $this->parser->parse('parts/member1',$data, true);
			}
			return $ret."</div>";
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	private function get_pa_info(){
		try{
			$json = '{"event":"get_info"}';
			if( $curl = curl_init() ) {
				curl_setopt($curl, CURLOPT_URL, 'https://viberbot.ehost.tj/index.php/core/bot/');
				curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
				$out = curl_exec($curl);
				curl_close($curl);

				$members = json_decode($out);

				return $members;
			}
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	public function add_to_public_from_full(){
		try{
			$id = $this->input->get('id');
			$this->load->model('images');
			$conf = read_config('config/general.conf');
			$conf_json = json_decode($conf);
			echo $conf;
			$mess['sender_id'] = $conf_json->bot_id;//ID Османа
			$mess['name'] = $conf_json->bot_name;
			$mess['avatar'] = $conf_json->bot_avatar;

			//foreach($post["pic"] as $pic){
				$pics = $this->images->getImagesByID($id);
				if($pics[0]["cont_type"] == "picture"){
					$mess['pic'] = 'https://viberbot.ehost.tj/vidl/'.$pics[0]['cont_local_url'];
					$mess['mess'] = "От ".$pics[0]['cont_owner_name'];
					$mess['thumbnail'] = 'https://viberbot.ehost.tj/vidl/thumbnail/'.$pics[0]['cont_thumbnail_local'];
					$json = '{"event":"post",
						"body": '.send_pa_post_pic($mess).'}';
				}elseif($pics[0]["cont_type"] == "file"){
					if(get_ext($pics[0]['cont_file_name']) == "gif"){
						$mess['url'] = 'https://viberbot.ehost.tj/vidl/'.$pics[0]['cont_local_url'];
						$mess['mess'] = "От ".$pics[0]['cont_owner_name'];
						$json = '{"event":"post",
							"body": '.send_pa_post_url($mess).'}';
					}else{
						$mess['file'] = 'https://viberbot.ehost.tj/vidl/'.$pics[0]['cont_local_url'];
						$mess['size'] = filesize('vidl/'.$pics[0]['cont_local_url']);
						$mess['file_name'] = $pics[0]['cont_file_name'];
						$mess['mess'] = "От ".$pics[0]['cont_owner_name'];
						$json = '{"event":"post",
							"body": '.send_pa_post_file($mess).'}';
					}
				}elseif($pics[0]["cont_type"] == "text"){
					$mess['text'] = substr($pics[0]['cont_remout_url'], 2, strlen($pics[0]['cont_remout_url']));
					$mess['mess'] = "От ".$pics[0]['cont_owner_name'];
					$json = '{"event":"post",
						"body": '.send_pa_post_text($mess).'}';
				}

				$this->images->setStatus($pics[0]['cont_id'], 15);

				if( $curl = curl_init() ) {
					curl_setopt($curl, CURLOPT_URL, 'https://viberbot.ehost.tj/index.php/core/bot/');
					curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
					$out = curl_exec($curl);
					curl_close($curl);
					LogTo('ansv.log',$out);
				}

			//}
			redirect("https://viberbot.ehost.tj/");
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	public function add_to_public(){
		try{
			$post = $this->input->post();
			$this->load->model('images');
			$conf = read_config('config/general.conf');
			$conf_json = json_decode($conf);
			$mess['sender_id'] = $conf_json->bot_id;//ID Османа
			$mess['name'] = $conf_json->bot_name;
			$mess['avatar'] = $conf_json->bot_avatar;

			foreach($post["pic"] as $pic){
				$pics = $this->images->getImagesByID($pic);
				if($pics[0]["cont_type"] == "picture"){
					$mess['pic'] = 'https://viberbot.ehost.tj/vidl/'.$pics[0]['cont_local_url'];
					$mess['mess'] = "От ".$pics[0]['cont_owner_name'];
					$mess['thumbnail'] = 'https://viberbot.ehost.tj/vidl/thumbnail/'.$pics[0]['cont_thumbnail_local'];
					
					$json = '{"event":"post",
						"body": '.send_pa_post_pic($mess).'}';
						//echo $json;
				}elseif($pics[0]["cont_type"] == "file"){
					if(get_ext($pics[0]['cont_file_name']) == "gif"){
						$mess['url'] = 'https://viberbot.ehost.tj/vidl/'.$pics[0]['cont_local_url'];
						$mess['mess'] = "От ".$pics[0]['cont_owner_name'];
						$json = '{"event":"post",
							"body": '.send_pa_post_url($mess).'}';
					}else{
						$mess['file'] = 'https://viberbot.ehost.tj/vidl/'.$pics[0]['cont_local_url'];
						$mess['size'] = filesize('vidl/'.$pics[0]['cont_local_url']);
						$mess['file_name'] = $pics[0]['cont_file_name'];
						$mess['mess'] = "От ".$pics[0]['cont_owner_name'];
						$json = '{"event":"post",
							"body": '.send_pa_post_file($mess).'}';
					}
				}elseif($pics[0]["cont_type"] == "text"){
					$mess['text'] = substr($pics[0]['cont_remout_url'], 2, strlen($pics[0]['cont_remout_url']));
					$mess['mess'] = "От ".$pics[0]['cont_owner_name'];
					$json = '{"event":"post",
						"body": '.send_pa_post_text($mess).'}';
				}

				$this->images->setStatus($pics[0]['cont_id'], 15);

				if( $curl = curl_init() ) {
					curl_setopt($curl, CURLOPT_URL, 'https://viberbot.ehost.tj/index.php/core/bot/');
					curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
					$out = curl_exec($curl);
					curl_close($curl);
					LogTo('ansv.log',$out);
				}

			}
			redirect("https://viberbot.ehost.tj/");
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	public function rej($cont_id){
		try{
			$this->images->setStatus($cont_id,11);
			redirect('/main');
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}


	}

	private function creatList(){
		try{
			$ret = '<form action=/index.php/main/add_to_public method=POST><input type="submit" value="Запостить"class="btn btn-block btn-success"><br><div class="row" >';
			$this->load->model('images');
			$list = $this->images->getLocalImages();
			if(empty($list)) return null;
			foreach($list as $item){
				if($item['cont_type'] == 'picture'){
					$imgBloc = '<center><a href="https://viberbot.ehost.tj/index.php/main/full/'.$item['cont_id'].'"><img style="height:200px; max-width:200px;" src="/vidl/'.$item["cont_local_url"].'" alt="..." class="margin"></a></center>';
				}elseif($item['cont_type'] == 'text'){
					$string = strip_tags($item["cont_remout_url"]);
					$string = substr($string, 2, 270);
					$text = rtrim($string, "!,.-");
					//$text = substr($string, 0, strrpos($string, ' '));
					$imgBloc = '<a href="https://viberbot.ehost.tj/index.php/main/full/'.$item['cont_id'].'"><div style="height:220px; max-width:200px;pading:auto;">'.$text.'</div></a>';
				}else{
					if(strtolower(get_ext($item['cont_local_url'])) == 'gif'){
						$imgBloc = '<center><a href="https://viberbot.ehost.tj/index.php/main/full/'.$item['cont_id'].'"><img style="height:200px; max-width:200px;" src="/vidl/'.$item["cont_local_url"].'" alt="..." class="margin"></a></center>';
					}else{
						$imgBloc = '<center><div style="height:220px; max-width:200px;pading:auto;">Файл: '.$item["cont_file_name"].'<br>Разширение: '.get_ext($item["cont_file_name"]).'<br>Размер: '.ceil(filesize("vidl/".$item["cont_local_url"])/1024).' kb </div></center>';
					}
			}
				$ret.='<div class="col-md-3 col-sm-6 col-xs-12">
						  <div class="info-box">
							<span class="info-box" >
								'.$imgBloc.'
							</span>

							<div style="margin:7px;">
							<span class="info-box-number"></span>
							  <span class="info-box-text">'.$item["cont_owner_name"].' <div style="float:right"><input name="pic[]" value="'.$item["cont_id"].'" type="checkbox" class="flat-red"></div></span>
							  <span class="info-box-number">'.date('d/m/Y H:i:s', $item["cont_added"]/1000).'<a href="/index.php/main/rej/'.$item["cont_id"].'"><button type="button" class="btn btn-block btn-danger">Отвергнуть</button></a><br><a href="'.base_url().'index.php/chat/index/?oid='.base64_encode($item["cont_owner_id"]).'"><button type="button" class="btn btn-block btn-info">Чат</button></a><br></span>

							</div>
							<!-- /.info-box-content -->
						  </div>
						  <!-- /.info-box -->
						</div>';
			}
			$ret .= '</form></div>';
			return $ret;
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	private function creatList_posted(){
		try{
			$ret = '<form action=/index.php/main/add_to_public method=POST><input type="submit" value="Запостить"class="btn btn-block btn-success"><br><div class="row" >';
			$this->load->model('images');
			$list = $this->images->getLocalImagesPosted();
			if(empty($list)) return null;
			foreach($list as $item){
				if($item['cont_type'] == 'picture'){
					$imgBloc = '<center><a href="https://viberbot.ehost.tj/index.php/main/full/'.$item['cont_id'].'"><img style="height:200px; max-width:200px;" src="/vidl/'.$item["cont_local_url"].'" alt="..." class="margin"></a></center>';
				}elseif($item['cont_type'] == 'text'){
					$string = strip_tags($item["cont_remout_url"]);
					$string = substr($string, 2, 270);
					$text = rtrim($string, "!,.-");
					//$text = substr($string, 0, strrpos($string, ' '));
					$imgBloc = '<a href="https://viberbot.ehost.tj/index.php/main/full/'.$item['cont_id'].'"><div style="height:220px; max-width:200px;pading:auto;">'.$text.'</div></a>';
				}else{
					if(strtolower(get_ext($item['cont_local_url'])) == 'gif'){
						$imgBloc = '<center><a href="https://viberbot.ehost.tj/index.php/main/full/'.$item['cont_id'].'"><img style="height:200px; max-width:200px;" src="/vidl/'.$item["cont_local_url"].'" alt="..." class="margin"></a></center>';
					}else{
						$imgBloc = '<center><div style="height:220px; max-width:200px;pading:auto;">Файл: '.$item["cont_file_name"].'<br>Разширение: '.get_ext($item["cont_file_name"]).'<br>Размер: '.ceil(filesize("vidl/".$item["cont_local_url"])/1024).' kb </div></center>';
					}
			}
				$ret.='<div class="col-md-3 col-sm-6 col-xs-12">
						  <div class="info-box">
							<span class="info-box" >
								'.$imgBloc.'
							</span>

							<div style="margin:7px;">
							<span class="info-box-number"></span>
							  <span class="info-box-text">'.$item["cont_owner_name"].' <div style="float:right"><input name="pic[]" value="'.$item["cont_id"].'" type="checkbox" class="flat-red"></div></span>
							  <span class="info-box-number">'.date('d/m/Y H:i:s', $item["cont_added"]/1000).'</span>
								<span class="info-box-number" style="color:#90ff09;">'.date('d/m/Y H:i:s', $item["cont_posted"]).'<a href="'.base_url().'index.php/chat/index/?oid='.base64_encode($item["cont_owner_id"]).'"><button type="button" class="btn btn-block btn-info">Чат</button></a><br></span>
							</div>
							<!-- /.info-box-content -->
						  </div>
						  <!-- /.info-box -->
						</div>';
			}
			$ret .= '</form></div>';
			return $ret;
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}

	private function creatList_rejected(){
		try{
			$ret = '<form action=/index.php/main/add_to_public method=POST><input type="submit" value="Запостить"class="btn btn-block btn-success"><br><div class="row" >';
			$this->load->model('images');
			$list = $this->images->getLocalImagesRejected();
			if(empty($list)) return null;
			foreach($list as $item){
				if($item['cont_type'] == 'picture'){
					$imgBloc = '<center><a href="https://viberbot.ehost.tj/index.php/main/full/'.$item['cont_id'].'"><img style="height:200px; max-width:200px;" src="/vidl/'.$item["cont_local_url"].'" alt="..." class="margin"></a></center>';
				}elseif($item['cont_type'] == 'text'){
					$string = strip_tags($item["cont_remout_url"]);
					$string = substr($string, 2, 270);
					$text = rtrim($string, "!,.-");
					//$text = substr($string, 0, strrpos($string, ' '));
					$imgBloc = '<a href="https://viberbot.ehost.tj/index.php/main/full/'.$item['cont_id'].'"><div style="height:220px; max-width:200px;pading:auto;">'.$text.'</div></a>';
				}else{
					if(strtolower(get_ext($item['cont_local_url'])) == 'gif'){
						$imgBloc = '<center><a href="https://viberbot.ehost.tj/index.php/main/full/'.$item['cont_id'].'"><img style="height:200px; max-width:200px;" src="/vidl/'.$item["cont_local_url"].'" alt="..." class="margin"></a></center>';
					}else{
						$imgBloc = '<center><div style="height:220px; max-width:200px;pading:auto;">Файл: '.$item["cont_file_name"].'<br>Разширение: '.get_ext($item["cont_file_name"]).'<br>Размер: '.ceil(filesize("vidl/".$item["cont_local_url"])/1024).' kb </div></center>';
					}
			}
				$ret.='<div class="col-md-3 col-sm-6 col-xs-12">
						  <div class="info-box">
							<span class="info-box" >
								'.$imgBloc.'
							</span>
							<div style="margin:7px;">
							<span class="info-box-number"></span>
							  <span class="info-box-text">'.$item["cont_owner_name"].' <div style="float:right"><input name="pic[]" value="'.$item["cont_id"].'" type="checkbox" class="flat-red"></div></span>
							  <span class="info-box-number">'.date('d/m/Y H:i:s', $item["cont_added"]/1000).'</span>
								<span class="info-box-number" style="color:#ff9009;">'.date('d/m/Y H:i:s', $item["cont_posted"]).'<a href="'.base_url().'index.php/chat/index/?oid='.base64_encode($item["cont_owner_id"]).'"><button type="button" class="btn btn-block btn-info">Чат</button></a><br></span>
							</div>
							<!-- /.info-box-content -->
						  </div>
						  <!-- /.info-box -->
						</div>';
			}
			$ret .= '</form></div>';
			return $ret;
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
