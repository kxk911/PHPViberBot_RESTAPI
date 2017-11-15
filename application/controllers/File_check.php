<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class File_check extends CI_Controller {
  public function __construct()
    {
      parent::__construct();
        $this->load->model('images');
		$this->load->helper('txt');
    }

    public function index()
  	{

  	}

  /*  public function delete_old(){
      $dir = "vidl/";

      $handle = opendir ($dir);
      while($file = readdir($handle))
      {
        if ($file != '.' && $file != '..')
        {
          if($this->check_date(filemtime($dir.$file))){
            $this->images->deleteByName($file);
          }
          //echo $file." ".(filemtime($dir.$file)+5*3600)." ".$this->check_date(filemtime($dir.$file))."<br>";
        }
      }
    }*/
	
	public function delete_old(){
		$this->delete_old_rej();
		$this->delete_old_posted();
		$this->delete_old_onmod();
	}

    public function delete_old_onmod(){
      try{
        $dir = "vidl/";
		$dir_t= "'thumbnail/'.";
		$conf_json = json_decode(read_config('config/general.conf'));
        $rows = $this->images->get_by_status(10);
        foreach($rows as $row){
			if(!empty($row['cont_local_url'])){
			  if($this->check_date($row['cont_added']/1000, $conf_json->autodelete->delete_old_onmoderation)){
				unlink($dir.$row['cont_local_url']);
				unlink($dir.$dir_t.$row['cont_thumbnail_local']);
				$this->images->deleteById($row['cont_id']);
			  }
			}else{
			  if($this->check_date($row['cont_added']/1000, $conf_json->autodelete->delete_old_rejected)){
				//unlink($dir.$row['cont_local_url']);
				$this->images->deleteById($row['cont_id']);
			  }
			}
		}
      }catch(Exeption $e){
  			LogTo('error.log',$e->getMessage());
  		}
    }
	
	public function delete_old_rej(){
      try{
        $dir = "vidl/";
		$dir= "'thumbnail/'.";
		$conf_json = json_decode(read_config('config/general.conf'));
        $rows = $this->images->get_by_status(11);
        foreach($rows as $row){
		  if(!empty($row['cont_local_url'])){
			  if($this->check_date($row['cont_added']/1000, $conf_json->autodelete->delete_old_rejected)){
				unlink($dir.$row['cont_local_url']);
				unlink($dir.$dir_t.$row['cont_thumbnail_local']);
				$this->images->deleteById($row['cont_id']);
			  }
		  }else{
			  if($this->check_date($row['cont_added']/1000, $conf_json->autodelete->delete_old_rejected)){
				//unlink($dir.$row['cont_local_url']);
				$this->images->deleteById($row['cont_id']);
			  }
		  }
        }
      }catch(Exeption $e){
  			LogTo('error.log',$e->getMessage());
  		}
    }
	
	public function delete_old_posted(){
      try{
        $dir = "vidl/";
		$dir= "'thumbnail/'.";
		$conf_json = json_decode(read_config('config/general.conf'));
        $rows = $this->images->get_by_status(15);
        foreach($rows as $row){
			if(!empty($row['cont_local_url'])){
			  if($this->check_date($row['cont_added']/1000, $conf_json->autodelete->delete_old_posted)){
				unlink($dir.$row['cont_local_url']);
				unlink($dir.$dir_t.$row['cont_thumbnail_local']);
				$this->images->deleteById($row['cont_id']);
			  }else{
				  if($this->check_date($row['cont_added']/1000, $conf_json->autodelete->delete_old_posted)){
					//unlink($dir.$row['cont_local_url']);
					$this->images->deleteById($row['cont_id']);
				  }
				}
			}
		}
      }catch(Exeption $e){
  			LogTo('error.log',$e->getMessage());
  		}
    }

    private function check_date($date, $limit){
      try{
        //$date += 5*3600;
        if(($this->get_curent_time() - $date) > $limit){
          return 1;
        }else{
          return 0;
        }
      }catch(Exeption $e){
        LogTo('error.log',$e->getMessage());
      }
    }

    private function get_curent_time(){
      try{
        $time = time();
        //$time += 5*3600;
        return $time;
      }catch(Exeption $e){
        LogTo('error.log',$e->getMessage());
      }
    }
}
