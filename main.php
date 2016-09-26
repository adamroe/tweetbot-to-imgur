<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/secrets.php';

## imgur settings
$imgur = new \adamroe\Imgur\API($imgur_api_key, $imgur_api_secret);

## bit.ly settings
$use_bitly = true;
$bitly = new \Hpatoio\Bitly\Client($bitly_api_key);


if (isset($_GET['method'])) {
	# we are doing something
	switch ($_GET['method']) {
		case 'getToken':
			showTokens();
			break;
			
		case 'shorten':
			shortenLink();
			break;
		
		case 'upload':
			if (isset($_GET['code'])) {
				$tokens = $imgur->authorize(false, $_GET['code']);
			} else {
				die("No code provided");
			}
		/*	if (!isset($_POST['message']))
				die "No message provided!";
			if (!isset($_POST['source']))
				die "No source provided!";
			if (!isset($_FILES['media']))
				die "No media provided!"; */
			uploadPicture();
			break;
	}
} else {
	if (isset($_GET['code'])) {
		global $imgur;
		// user is authorised, so lets instantiate a connection to Imgur
		$tokens = $imgur->authorize(false, $_GET['code']);
		showTokens($tokens);
	} else {
		echo "<br/><br/><br/><center><h1>";
	    // GET parameter doesn't exist, so we will have to ask user to allow access for our application
	    $imgur->authorize();
	}
}

function showTokens($t) {
	$script_url = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
	$script_var = "?" . "code=" . $_GET['code'];
	$script_var .= "&" . "method=upload";
	
	echo "<h1>Congratulations, you are connected to Imgur</h1>";
	echo "<p>To add this uploader to Tweetbot and upload pictures as " . $t['account_username'] . ", use the following URL in settings:<p>";
	echo "<p><code>" . $script_url . $script_var . "</code></p>";
}

function shortenLink($l) {
	global $bitly, $use_bitly;
	$output = $l;
	
	if ($use_bitly) {
		$post['longUrl'] = $l;
		$shortened = $bitly->Shorten($post);
		$output = $shortened['url'];
	}
	
	return $output;
}

function uploadPicture() {
	global $imgur;
	$post = "";
	
	if (isset($_POST['message'])) {
		$post['description'] = $_POST['message'];
	}
	
	if (isset($_FILES['media']['name'])) {
		$post['name'] = $_FILES['media']['name'];
	}
	
	$uploaded = $imgur->upload()->file($_FILES['media']['tmp_name'], $post);
	
	$image_link = $uploaded['data']['link'];
	$shortened_link = shortenLink($image_link);
	
	echo json_encode(array("url" => $shortened_link));
}