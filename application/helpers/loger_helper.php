<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('LogTo'))
{
    function LogTo($file_name, $text)
    {
		try{
			$str = date('[d/m/Y H:i:s] ', time());
			$str .= $text;
$str .=
'

';
			$fd = fopen('Logs/'.$file_name, 'a+');
			fwrite($fd, $str);
			fclose($fd);
			return true;
		}catch(Exception $e){
			return false;
		}
    }
}
