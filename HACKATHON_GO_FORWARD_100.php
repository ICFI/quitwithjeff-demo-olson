<?php

// Note this code was thrown together for a hackathon :)

$body = file_get_contents('php://input');
$body_obj = json_decode($body);
$send_id = $body_obj->entry[0]->messaging[0]->sender->id;
$access_token = '[ACCESS_TOKEN]';
$messenger_url = 'https://graph.facebook.com/v2.6/me/messages/?access_token=';
$graph_url = 'https://graph.facebook.com/v2.6/?access_token=';

try {


// Get user's first name based on userid name
function get_user_fn($graph_url, $send_id, $access_token)
{
	$ch = curl_init("$graph_url" . "$send_id" . "$access_token");
	curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
		),
	));
// Send the request
$response = curl_exec($ch);
$json = json_decode($response);
$user_fn = $json->first_name;
	
// Check for errors
if($response === FALSE){
	die(curl_error($ch));
}
curl_close($ch);
return $user_fn;
}

// Creates a valid json response for facebook messenger api
function create_txt_message($send_id = '', $msg = '')
{
	$post_msg  ='{"recipient":{"id":"'.$send_id.'"},"message":{"text":"'.$msg.'"}}';
	return $post_msg;
} 

function create_img_message($send_id = '', $img_url = '')
{
	$post_msg='{"recipient":{"id":"'.$send_id.'"},"message":{"attachment":{"type":"image","payload":{"url":"'.$img_url.'"}}}}';
	return $post_msg;
}

function create_button_message($send_id = '', $msg = '')
{
	$post_msg  ='{"recipient":{"id":"'.$send_id.'"},"message":{"text":"'.$msg.'"}}';
	return $post_msg;
} 

// Posts a text message
function send_message($messenger_url='', $access_token='', $msg='')
{
	// Setup cURL
	$ch = curl_init("$messenger_url" . "$access_token");
	curl_setopt_array($ch, array(
		CURLOPT_POST => TRUE,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Content-Length: '. strlen($msg)
		),
		CURLOPT_POSTFIELDS => $msg
	));

	// Send the request
	$response = curl_exec($ch);
	// Check for errors
	if($response === FALSE){
		die(curl_error($ch));
	}
	curl_close($ch);
	
}


// Check if request contains message
function has_message_or_postback($body)
{
	$hasMessage = false;
	// Check if there was a message posted, ignore deliverys
	if((!empty($body->entry[0]->messaging[0]->message) || !empty($body->entry[0]->messaging[0]->postback->payload)) && empty($body->entry[0]->messaging[0]->delivery)){
		$hasMessage = true;
	}
	return $hasMessage;
}

// Get message
function get_message($body)
{
	$msg = $body->entry[0]->messaging[0]->message->text;
	return $msg;
}

function seq_hows_it_going($messenger_url, $access_token, $send_id)
{
	send_message($messenger_url, $access_token, '{"recipient":{"id":"'.$send_id.'"},"message":{"attachment":{"type":"template","payload":{"template_type":"button","text":"So, how is it going?","buttons":[{"type":"postback","title":"Staying strong!","payload":"key_staying_strong"},{"type":"postback","title":"I want to smoke.","payload":"key_craving"},{"type":"postback","title":"Smoked today.","payload":"key_smoked"}]}}}}');
}

function seq_exercise($messenger_url, $access_token, $send_id)
{
	send_message($messenger_url, $access_token, '{"recipient":{"id":"'.$send_id.'"},"message":{"attachment":{"type":"template","payload":{"template_type":"generic","elements":[{"image_url":"http://s32.postimg.org/8nlirwi85/exercise.png","title":"Try getting moving, even just for a few minutes, to get through a craving.","buttons":[{"type":"postback","payload":"key_send_tip_exercise_successor","title":"Show another tip."},{"type":"postback","payload":"key_good","title":"I\'m good for now."},{"type":"web_url","url":"https://smokefree.gov","title":"Help manage cravings."}]}]}}}}');
}

function seq_congrats($messenger_url, $access_token, $send_id)
{
	send_message($messenger_url, $access_token, create_txt_message($send_id, 'Congrats! \\uD83D\\uDC4F')); // unicode for clapping emoji
	send_message($messenger_url, $access_token, '{"recipient":{"id":"'.$send_id.'"},"message":{"attachment":{"type":"template","payload":{"template_type":"generic","elements":[{"image_url":"http://s32.postimg.org/g163mla2t/been_smokefree_since.png","title":"Be sure to share your success with your friends on Facebook.","buttons":[{"type":"postback","payload":"key_send_tip_congrats_successor","title":"Show me some tips."},{"type":"postback","payload":"key_good","title":"I\'m good for now."}]}]}}}}');
}

function seq_exercise_options($messenger_url, $access_token, $send_id)
{
	send_message($messenger_url, $access_token, '{"recipient":{"id":"'.$send_id.'"},"message":{"attachment":{"type":"template","payload":{"template_type":"button","text":"Options","buttons":[{"type":"postback","title":"Show another tip.","payload":"key_send_tip_exercise_successor"},{"type":"postback","title":"I\'m good for now.","payload":"key_good"},{"type":"web_url","url":"https://smokefree.gov","title":"Help manage cravings."}]}}}}');
}

function seq_congrats_options($messenger_url, $access_token, $send_id)
{
	send_message($messenger_url, $access_token, '{"recipient":{"id":"'.$send_id.'"},"message":{"attachment":{"type":"template","payload":{"template_type":"button","text":"Options","buttons":[{"type":"postback","title":"Show me some tips.","payload":"key_send_tip_congrats_successor"},{"type":"postback","title":"I\'m good for now.","payload":"key_good"}]}}}}');
}

function get_random_tip()
{
	$ch = curl_init('https://smokefree-stage.icfwebservices.com/quitwithjeff');
	curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
		),
	));
	// Send the request
	$response = curl_exec($ch);
	
	// Check for errors
	if($response === FALSE){
		die(curl_error($ch));
	}
	
	$json = json_decode($response);
	$tip_count = count($json->nodes);
	$rand_index = rand(0, $tip_count-1);
	
	curl_close($ch);
	return $json->nodes[$rand_index]->node->tip;
}

if (has_message_or_postback($body_obj))
{
	// button callbacks
	if (!empty($body_obj->entry[0]->messaging[0]->postback->payload))
	{
		$postback = $body_obj->entry[0]->messaging[0]->postback->payload;
		if(strcmp($postback,'key_usage_help') == 0)
		{
			send_message($messenger_url, $access_token, create_txt_message($send_id, 'I use tips from Smokefree.gov to help you quit smoking.'));
			send_message($messenger_url, $access_token, create_txt_message($send_id, 'I can help you when you choose from the options I send you.'));
			send_message($messenger_url, $access_token, create_txt_message($send_id, 'Or you can type \\"Crave\\", \\"Tip\\" or \\"Help\\" for more.'));
			seq_hows_it_going($messenger_url, $access_token, $send_id);
		}
		else if(strcmp($postback,'key_help_quit') == 0)
		{
			seq_hows_it_going($messenger_url, $access_token, $send_id);
		}
		else if(strcmp($postback,'key_craving') == 0)
		{
			seq_exercise($messenger_url, $access_token, $send_id);
		}
		else if(strcmp($postback,'key_smoked') == 0)
		{
			send_message($messenger_url, $access_token, create_img_message($send_id, 'https://smokefree-stage.icfwebservices.com/sites/default/files/include_images/car-tire-flat.jpg'));
			send_message($messenger_url, $access_token, create_txt_message($send_id, 'Ditching your quit because of a slip is like slashing your other three tires because you got a flat.'));
			seq_hows_it_going($messenger_url, $access_token, $send_id);
		}
		else if(strcmp($postback,'key_staying_strong') == 0)
		{
			seq_congrats($messenger_url, $access_token, $send_id);
		}
		else if(strcmp($postback,'key_send_tip_exercise_successor') == 0)
		{
			send_message($messenger_url, $access_token, create_txt_message($send_id, get_random_tip()));
			seq_exercise_options($messenger_url, $access_token, $send_id);
		}
		else if(strcmp($postback,'key_send_tip_congrats_successor') == 0)
		{
			send_message($messenger_url, $access_token, create_txt_message($send_id, get_random_tip()));
			seq_congrats_options($messenger_url, $access_token, $send_id);
		}
		else if(strcmp($postback,'key_good') == 0)
		{
			send_message($messenger_url, $access_token, create_txt_message($send_id, 'Right on. I\'m here for you, '.get_user_fn($graph_url, $send_id, $access_token).'. Hit me up again the next time you\'re tempted.'));
			send_message($messenger_url, $access_token, create_img_message($send_id, 'http://s32.postimg.org/kmuqgan5x/dancing_jeff.gif'));
		}
		else
		{
			watchdog('jeff_button_callback_error', 'Unknown key '.$postback);
		}
	}
	else 
	{
		send_message($messenger_url, $access_token, create_txt_message($send_id, 'Hi, '.get_user_fn($graph_url, $send_id, $access_token).'! Use me as a quit buddy to help you get through cigarette cravings and stop smoking.'));
		send_message($messenger_url, $access_token, create_txt_message($send_id, 'You may remember me from my time working with John Oliver'));
		send_message($messenger_url, $access_token, create_img_message($send_id, 'http://s32.postimg.org/3rlr8nw1x/Marlboro_Mascot_Parody_by_John_Oliver_Jeff_the.png'));
		send_message($messenger_url, $access_token, '{"recipient":{"id":"'.$send_id.'"},"message":{"attachment":{"type":"template","payload":{"template_type":"button","text":"I hate that picture. I quit smoking and I would like to help you quit, too.","buttons":[{"type":"postback","title":"Sounds good!","payload":"key_help_quit"},{"type":"postback","title":"Instructions.","payload":"key_usage_help"}]}}}}');
	}
}

} catch (Exception $e) {
	watchdog('jeff_error', $e);
}

drupal_exit();

?>