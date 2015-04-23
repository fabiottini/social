<?
require_once("simple_html_dom.php");

$login_email = "";
$login_pass = "";
$hashtag = "";

function debug($ele){
	echo "<pre>";print_r($ele);echo "</pre>";
}


function cURL($url, $header=NULL, $cookie=NULL, $p=NULL){
	 $ch = curl_init();
	 curl_setopt($ch, CURLOPT_HEADER, $header);
	 curl_setopt($ch, CURLOPT_NOBODY, $header);
	 curl_setopt($ch, CURLOPT_URL, $url);
	 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	 curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	 curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
	 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

	 if ($p) {
	 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	 curl_setopt($ch, CURLOPT_POST, 1);
	 curl_setopt($ch, CURLOPT_POSTFIELDS, $p);
	 }


	 $result = curl_exec($ch);
	 curl_close($ch);

	 return $result;
}

$arr = array();
$cookie = '';


$a = cURL("https://login.facebook.com/login.php?login_attempt=1",true,null,"email=$login_email&pass=$login_pass");
preg_match('%Set-Cookie: ([^;]+);%',$a,$b);
$c = cURL("https://login.facebook.com/login.php?login_attempt=1",true,$b[1],"email=$login_email&pass=$login_pass");
preg_match_all('%Set-Cookie: ([^;]+);%',$c,$d);

for($i=0;$i<count($d[0]);$i++)
 $cookie.=$d[1][$i].";";




$page = cURL("https://m.facebook.com/hashtag/$hashtag",null,$cookie,null);
//$page = str_replace('class=\\"','"',$page);
//$page = str_replace('id=\\"','"',$page);
//$page = str_replace('\u003C','<',$page);
$page = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
}, $page);

$page=stripslashes($page);

$html = str_get_html($page);
$html->save("FB.html");


$html = new simple_html_dom();
$html->load(file_get_contents("FB.html"));
$id=0;
$root = $html->find('div[class=dn do dp]')[0];
$items = $root->find('div');
foreach($items as $article) {
	//recupero user
	foreach($article->find('strong') as $chiArticle){
		$chi = $chiArticle->plaintext;
	}
	//recupero msg
	foreach($article->find('span') as $msgArticle){
		$msg = $msgArticle->plaintext;
	}
	//controllo
	$msgTest = str_replace(' ', '', $msg);
	$chiTest = str_replace(' ', '', $chi);
	if($msgTest != null && $msgTest != "" && strlen($msgTest) > 2 && $msgTest!="Pubblica" && $msgTest!="Personalizzata" ){
		$arr[$id]["msg"] = $msg;
		$arr[$id]["chi"] = $chi;
		$id++;
	}
}

debug($arr);
