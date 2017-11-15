<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('get_ext'))
{
	function get_ext($file_name)
	{
		$str = trim(strtolower($file_name));
		$str_len = strlen($str);
		$ret = null;
		for($i = $str_len; $i > 0; $i--){
			if(substr($str,$i,1) == "."){
				$ret = substr($str,$i+1,$str_len-$i);
				return $ret;
			}else{
				continue;
			}
		}

	}
}

if ( ! function_exists('read_config'))
{
	function read_config($file_name)
	{
		$line = null;
		$file_handle = fopen($file_name, "r");
		while (!feof($file_handle)) {
		   $line .= fgets($file_handle);
		}
		fclose($file_handle);
		return $line;
	}
}

if ( ! function_exists('write_config'))
{
	function write_config($file_name,$text)
	{
		$fd = fopen($file_name, 'w');
		fwrite($fd, $text);
		fclose($fd);
	}
}
