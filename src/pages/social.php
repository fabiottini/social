<?
require_once("pages/config.php");

require_once("pages/instagram.php");
require_once("pages/twitter.php");
require_once("pages/facebook.php");


$tw  = new Twitter($settingsTWITTER, "#$HASHTAG","<USER>");
$in  = new Instagram($settingsINSTAGRAM, "$HASHTAG");
$fb  = new Facebook($settingsFACEBOOK, "$HASHTAG");

$num 		= 0;

function convertHTMLutf8($str){
	return html_entity_decode($str, ENT_QUOTES, "utf-8");
}

function printQuadrato($arr, $tipo, $numPerPag){
	$contaPagine = 0;
	$quadrato = file_get_contents("./pages/quadrato.html");
	$ret = "";

	global $num;

	foreach ($arr as $arrElem) {
		foreach ($arrElem as $value) {
			$id			= (isset($value["id"]))?convertHTMLutf8($value["id"]):null;
			$text 		= convertHTMLutf8($value["text"]);
			$user 		= convertHTMLutf8($value["user"]); $user = "@".$user;
			$userId 	= convertHTMLutf8($value["userId"]);
			$userName 	= convertHTMLutf8($value["userName"]);
			$social 	= convertHTMLutf8($value["social"]);

			if($social == "twitter"){
				$socialLogo = '<img class="padding0_twitter" src="img/twitter_48.png" width="32" height="32" />';
				$url = "https://twitter.com/$user/status/$id";
			}else if($social == "instagram"){
				$socialLogo = '<img class="padding0_istagram" src="img/instagram_50.png" width="32" height="32" />';
				$url =	convertHTMLutf8($value["link"]);
			}else if($social == "facebook"){
				$socialLogo = '<img class="padding0_istagram" src="img/facebook.jpg" width="32" height="32" />';
				$url = $value["link"];//"https://facebook.com/$userId";
			}else{
				$socialLogo = "";
				$url = "#";
			}

			if($value["type"] == "photo" || $value["type"] == "image"){
				$image = "<img class='padding0' src='pages/imgSocial.php?w=137&h=187&url=".($value['standard'])."' />";
			}else{
				$image = "";
			}
			if( strlen($text) > 0 ){
				$subMsg = (strlen($text) > 30)?substr($text,0,30)." ...":$text;
				$title = $user." - ".$subMsg;
			}else{
				$title = $user;
			}


			$pagina = intval($contaPagine/$numPerPag)+1;
			$nomeDiv = $tipo.$pagina;
			if($contaPagine == 0){
				$ret .= "<div id='$nomeDiv'>";
			}else if($contaPagine%$numPerPag==0){
				$ret .= "</div><div id='$nomeDiv' style='display:none;'>";
			}
			$ret .= sprintf($quadrato,$url,$title,$image,$num,$num,$num,$text,$user,$socialLogo);


			$num ++;
			$contaPagine++;
		}
	}
	$ret .= "</div>";
	return $ret;
}


function creaMenuPagine($num,$numPerPag,$label){
	//echo $num." ".$numPerPag."<br>";
	if($num <= $numPerPag){
		return "";
	}

	$numPag = 0;
	$ret = "<div id='menu_$label' class='menuPagine'>";

	for($i=0;$i<intval($num/$numPerPag);$i++){
		$numPag = ($i+1);
		$ret .= "<span class='pulsantePagina' onclick='scorriPagina($numPag,\"$label\")'>$numPag</span>";
		$ret .= " - ";
		//echo $i."<".intval($num/$numPerPag)."<br>";
	}

	if($num%$numPerPag != 0){
		$numPag++;
		$ret .= "<span href='#' class='pulsantePagina'  onclick='scorriPagina($numPag,\"$label\")'>$numPag</span>";
	}else{
		$ret = substr($ret,0,strlen($ret)-3);
	}
	$ret .= "</div>";
	return $ret;
}


$twitter 	= $tw->getVar();
$instagram 	= $in->getVar();
$facebook   = $fb->getVar();


$instaUser = $instagram[1]; 
$instaTag  = $instagram[0];

$tweetUser = $twitter[1];
$tweetTag  = $twitter[0];

$facebookTag = $facebook[0];
$facebookUser = null;

/** MERGE TWITTER AND INSTAGRAM OF THE USER */
if( $tweetUser != null || $instaUser != null ){
	$user = $tweetUser + $instaUser; + $facebookUser ;
	ksort($user);
}

/** MERGE TWITTER AND INSTAGRAM OF THE HASHTAG */
if( $tweetTag != null || $instaTag != null ){
	$tag = $tweetTag + $instaTag + $facebookTag;
	ksort($tag);
}



/** GENERATE THE INTERFACE */
$ret = printQuadrato($user,'userPage_', $numPerPag);
$ret .= creaMenuPagine($num,$numPerPag,'userPage_');

$ret .="<hr>";

$tmpNum = $num;
$ret .= printQuadrato($tag,'tagPage_', $numPerPag);
$tmpNum = $num - $tmpNum ;
$ret .= creaMenuPagine($tmpNum,$numPerPag,'tagPage_');

echo "<div class='areaSocial'>".$ret."</div>";

?>
