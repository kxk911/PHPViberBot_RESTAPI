<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('welcome_message'))
{
	function welcome_message($messArray)
	{
		return '{
				"sender": {
					"name": "'.$messArray['name'].'",
					"avatar": "'.$messArray['avatar'].'"
				},
				"tracking_data": "tracking data",
				"type": "text",
				"text": "'.$messArray['text'].'"
			}';
		}
	}
	function auth_ansv($messArray){
		return '{
			"receiver": "'.$messArray['sender_id'].'",
			"min_api_version": 1,
			"sender": {
				"name": "'.$messArray['name'].'",
				"avatar": "'.$messArray['avatar'].'"
			},
			"type": "url",
			"media": "'.$messArray['url'].'",
			"text":"'.$messArray['mess'].'"}';
	}
	
	function auth_ansv1($messArray){
		return '{
			"receiver": "'.$messArray['sender_id'].'",
			"min_api_version": "1",
			"sender": {
				"name": "'.$messArray['name'].'",
				"avatar": "'.$messArray['avatar'].'"
			},
			"tracking_data": "tracking data",
			"keyboard": {
				"Type": "keyboard",				
				"BgColor": "#FFFFFF",
				"Buttons": [{
					"Columns": 6,
					"Rows": 1,
					"BgColor": "#dedede",		
					"ActionType": "open-url",
					"ActionBody": "'.$messArray['url'].'",
					"Text": "Админ панель",
					"TextVAlign": "middle",
					"TextHAlign": "center",
					"TextOpacity": 60
				}]
			}
		}
	}';
	}

	function picture_ansver($messArray)
	{
		return '{
					"receiver": "'.$messArray['sender_id'].'",
					"min_api_version": 1,
					"sender": {
						"name": "'.$messArray['name'].'",
						"avatar": "'.$messArray['avatar'].'"
					},
					"tracking_data": "tracking data",
					"type": "text",
					"text": "'.$messArray['text'].'"
				}';
	}

	function send_pa_post_pic($messArray){
		return '{"from": "'.$messArray['sender_id'].'",
			"sender": {
				"name": "'.$messArray['name'].'",
				"avatar": "'.$messArray['avatar'].'"
			},
			"type": "picture",
			"media": "'.$messArray['pic'].'",
			"thumbnail" : "'.$messArray['thumbnail'].'",
			"text":"'.$messArray['mess'].'"}';
	}

	function send_pa_post_file($messArray){
		return '{"from": "'.$messArray['sender_id'].'",
			"sender": {
				"name": "'.$messArray['name'].'",
				"avatar": "'.$messArray['avatar'].'"
			},
			"type": "file",
			"media": "'.$messArray['file'].'",
			"size":"'.$messArray['size'].'",
			"file_name":"'.$messArray['file_name'].'"}';
	}
	


	function send_pa_post_url($messArray){
		return '{"from": "'.$messArray['sender_id'].'",
			"sender": {
				"name": "'.$messArray['name'].'",
				"avatar": "'.$messArray['avatar'].'"
			},
			"type": "url",
			"media": "'.$messArray['url'].'",
			"text":"'.$messArray['mess'].'"}';
	}

	function send_pa_post_text($messArray){
		return '{"from": "'.$messArray['sender_id'].'",
			"sender": {
				"name": "'.$messArray['name'].'",
				"avatar": "'.$messArray['avatar'].'"
			},
			"type": "text",
			"text": "'.$messArray['mess'].'\n\r'.$messArray['text'].'"}';
	}
