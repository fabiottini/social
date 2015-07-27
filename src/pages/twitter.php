<?
require_once("twitterApiExchange.php");

class Twitter{

	private $tag = null;
	private $user = null;


	function __construct($settings, $tag, $user=null){
		$this->tag = $this->getTagTweet($tag,$settings);
		$this->user = $this->getUserTweet($user,$settings);
	}

	function getVar(){
		return array($this->tag,$this->user);
	}

	private function getUserTweet($user, $settings){
		$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";


		$requestMethod = 'GET';
		$getfield = '?screen_name='.$user;

		// Perform the request
		$twitter = new TwitterAPIExchange($settings);
		$response = $twitter->setGetfield($getfield)
		             ->buildOauth($url, $requestMethod)
		             ->performRequest();

		$resp = json_decode($response,true);

		if(count($resp)<=0) return null;

		$arr = array();
		foreach ($resp as $value) {
			$time = strtotime($value["created_at"]);

			$arrTMP = array(
				"id"		=> htmlentities($value["id"]),
				"text" 		=> htmlentities($value["text"]),
				"user" 		=> htmlentities($value["user"]['screen_name']),
				"userId" 	=> htmlentities($value["user"]['id']),
				"userName" 	=> htmlentities($value["user"]['name'])
			);

			if(  isset($value["entities"]["media"]) && count($value["entities"]["media"]) > 0 ){
				$media 					= $value["entities"]["media"][0];
				$arrTMP["type"] 		= htmlentities($media["type"]);
				$arrTMP["standard"]		= htmlentities($media["media_url"]);
				$arrTMP["link"]			= htmlentities($media["url"]);
			}else{
				$arrTMP["type"]		= "text";
			}

			$arrTMP["social"] = "twitter";

			$arr[$time][] = $arrTMP;
		}

		return $arr;

	}


	private function getTagTweet($tag, $settings){

		$url = "https://api.twitter.com/1.1/search/tweets.json";
		$requestMethod = 'GET';
		$getfield = '?q='.htmlspecialchars($tag)."&result_type=mixed&count=50";

		// Perform the request
		$twitter = new TwitterAPIExchange($settings);
		$response = $twitter->setGetfield($getfield)
		             ->buildOauth($url, $requestMethod)
		             ->performRequest();

		$resp = json_decode($response,true);

		if(count($resp['statuses'])<=0) return null;

		$arr = array();
		foreach ($resp['statuses'] as $value) {
			$time = strtotime($value["created_at"]);

			$arrTMP = array(
				"id"		=> htmlentities($value["id"]),
				"text" 		=> htmlentities($value["text"]),
				"user" 		=> htmlentities($value["user"]['screen_name']),
				"userId" 	=> htmlentities($value["user"]['id']),
				"userName" 	=> htmlentities($value["user"]['name'])
			);

			if(  isset($value["entities"]["media"]) && count($value["entities"]["media"]) > 0 ){
				$media 				= $value["entities"]["media"][0];
				$arrTMP["type"] 	= htmlentities($media["type"]);
				$arrTMP["standard"] = htmlentities($media["media_url"]);
				$arrTMP["link"]		= htmlentities($media["url"]);
			}else{
				$arrTMP["type"]		= "text";
			}

			$arrTMP["social"] = "twitter";

			$arr[$time][] = $arrTMP;
		}
		return $arr;
	}

}


?>
