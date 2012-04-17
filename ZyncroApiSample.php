<?php

	include_once "lib/oauth/OAuthStore.php";
	include_once "lib/oauth/OAuthRequester.php";
	include_once "lib/oauth/OAuthUtil.php";

	define("API_KEY", "ApiKey"); // 
	define("API_SECRET", "ApiSecret"); // 

	define("ZYNCRO_URL", "https://my.sandbox.zyncro.com");
	define("REQUEST_TOKEN_URL", ZYNCRO_URL . "/tokenservice/oauth/v1/get_request_token");
	define("AUTHORIZE_URL", ZYNCRO_URL . "/tokenservice/oauth/v1/NoBrowserAuthorization");
	define("ACCESS_TOKEN_URL", ZYNCRO_URL . "/tokenservice/oauth/v1/get_access_token");

	define('OAUTH_TMP_DIR', function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : realpath($_ENV["TMP"]));

	//  Init the OAuthStore
	$options = array(
		'consumer_key' => API_KEY, 
		'consumer_secret' => API_SECRET,
		'server_uri' => ZYNCRO_URL,
		'request_token_uri' => REQUEST_TOKEN_URL,
		'authorize_uri' => AUTHORIZE_URL,
		'access_token_uri' => ACCESS_TOKEN_URL
	);
	
	// Note: do not use "Session" storage in production. 
	// Prefer a database storage, such as MySQL.
	OAuthStore::instance("Session", $options);


	function authorizeToken($username, $password, $request_token) 
	{		
		try 
		{
			$data = "username=".$username."&password=".$password."&request_token=".$request_token;
			$result = OAuthUtil::do_post_request(AUTHORIZE_URL, $data);
			return true;
		}
		catch (Exception $e)
		{			
		    return false;
		}		
	}

	function authorizeOAuth($username, $password)
	{
		try 
		{
			$token = OAuthRequester::requestRequestToken(API_KEY, 0, array());

			if (authorizeToken($username, $password, $token['token'])) 
			{
		    		OAuthRequester::requestAccessToken(API_KEY, $token['token'], 0, 'POST', NULL);
		    	} 
		    	else
		    	{
		    		throw new Exception("Error authorizing OAuth");
		    	} 
		}
		catch (OAuthException2 $e)
		{			
		    throw new Exception("Error authorizing OAuth");
		}
	}

	function getMainFeed()
	{
		$request = new OAuthRequester(ZYNCRO_URL."/api/v1/rest/wall", 'GET');
		$result = $request->doRequest(0);
		if ($result['code'] == 200) 
		{
			return $result['body'];
		}
		return null;
	}
	
	function publishOnPersonalFeed($comment)	
	{
		$request = new OAuthRequester(ZYNCRO_URL."/api/v1/rest/wall/personalfeed", 'POST');
		
		$param = array(
			'comment' => utf8_encode($comment)		
		);
		$request->setBody(OAuthUtil::build_http_body($param));
		
		$result = $request->doRequest(0);
		if ($result['code'] == 200) 
		{
			return $result['body'];
		}
		
		return null;
	}

	$username = 'Email';
	$password = 'Password';

	// Configure the OAuthStore with a valid an Access token for a user
	authorizeOAuth($username, $password);
	
	// Get the main Microblogging for a user
	echo 'Main feed: ' . getMainFeed();

	// Publish a new message in User's Personal feed
	echo 'New Event published: ' . publishOnPersonalFeed('Hello world, Zyncro!');

?>