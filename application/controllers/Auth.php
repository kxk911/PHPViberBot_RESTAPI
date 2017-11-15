<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	public function __construct()
		{
			parent::__construct();
			$this->load->model('login');
		}

	public function index()
	{
		try{
		$hash = $this->input->get('hesh');
		$res = $this->login->getAuthToken($hash);
		if($res[0]['auth_time_elapsed'] < time()){
			//redirect("https://viberbot.ehost.tj/");
		}
		}catch(Exeption $e){
			LogTo('error.log',$e->getMessage());
		}
	}
}
