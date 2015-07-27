<?

$LINKBASE = "http://www.facebook.com";

class Facebook{

	private $tag = null;
    private $user = null;

	function toTimestamp($str){
		$data = explode(" ", $str);
		$ora = explode(":", $data[1]);
		$data = explode("/", $data[0]);

		return mktime($ora[0],$ora[1],$ora[2],$data[1],$data[0],$data[2]);
	}

	function getVar(){
        return array($this->tag,$this->user);
    }

	function cleanDate($str){
		$arrMesi = [ 
					"gennaio" 	=> 1,
					"febbraio" 	=> 2,
					"marzo" 	=> 3,
					"aprile" 	=> 4,
					"maggio" 	=> 5,
					"giugno" 	=> 6,
					"luglio" 	=> 7,
					"agosto" 	=> 8,
					"settembre" => 9,
					"ottobre" 	=> 10,
					"novembre" 	=> 11,
					"dicembre" 	=> 12
				];
		$strClean="";
		$regex = '/(\d{1,2}) (\w*)\s\w*\s\w*\s(\d{1,2}).(\d{1,2})/';

		preg_match($regex, $str, $matches);
		if(count($matches) != 0 ){
			/**
			 * 1 => Giorno
			 * 2 => mese
			 * 3 => ore
			 * 4 => minuti
			 */ 
			$mese = $arrMesi[$matches[2]];
			if( $mese < 10 ) $mese = "0".$mese;
			$strClean = $matches[1]."/".$mese."/".date("Y")." ".$matches[3].":".$matches[4].":00";
		}else{
			$regex = '/(\d{1,2})\s(ore|minuti)/';
			preg_match($regex, $str, $matches);
			if(count($matches) > 0){
				$quanto = $matches[1];
				$unita = ($matches[2]== "ore")?"hour":"minutes";

				$date = date('d/m/Y H:i:s', strtotime("-$quanto $unita"));
				$strClean = $date;
			}
		}
		return $strClean;
	}


	function getLink( $obj ){
		global $LINKBASE;
		// $arr = array();
		// $r_link = $obj->getElementsByTagName('a');
		// foreach ($r_link as $r) {
		// 	$arr[] = $LINKBASE.($r->attributes->getNamedItem("href")->value);	
		// }

		$arr ="";
		$r_link = $obj->getElementsByTagName('a');
		foreach ($r_link as $r) {
			$arr = $LINKBASE.($r->attributes->getNamedItem("href")->value);	
			if($arr != "" && $arr != null)
				break;
		}
		return $arr;
	}

	function checkUserId( $str ){
		if($str == null || $str == "") return null;

		$arr = array(
			0 => '/(^profile\.php*)/'
			);
		for($i =0; $i<count($arr);$i++){
			preg_match($arr[0], $str, $matches);
			if(count($matches) != 0 ){
				return null;
			}
		}
		return $str;
	}

	function getUserId( $obj ){
		$arr = "";

		$r_link = $obj->getElementsByTagName('a');

		foreach ($r_link as $r) {
			$string = $r->attributes->getNamedItem("href")->value;

			if($string != "" && $string != null){
				$arr = substr($string, 0, strrpos( $string, '?') );	
				$string = $arr;
				if($arr[0] == '/'){
					$arr = substr($string, 1);
				}
				if( !is_null($this->checkUserId($arr)) ){
					break;
				}else{
					$arr = "";
				}
			}
		}
		return $arr;
	}

	function convertURLIMG( $url ){
		if( ($strPOS=strrpos($url, "?")) > 0){
			$strNEW = substr($url,$strPOS,strlen($url));
	        $strNEW = str_replace('?','///',$strNEW);
	        $strNEW = str_replace('&','//',$strNEW);
	        return substr($url,0,$strPOS).$strNEW;
	    }else{
	    	return $url;
	    }
	}


	function getImg( $obj ){
		
		$r_img = $obj->getElementsByTagName('img');
		// $arr = array();
		// foreach ($r_img as $r_img_IN) {
		// 	$arr[] = ($r_img_IN->attributes->getNamedItem("src")->value);
		// }
		$arr = "";
		foreach ($r_img as $r_img_IN) {
		 	$arr = $this->convertURLIMG(($r_img_IN->attributes->getNamedItem("src")->value));
		 	break;
		}
		return $arr;
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

	function __construct($settings, $hashtag){
		$login_email = $settings['login_email'];
		$login_pass = $settings['login_pass'];

		$arr = array();
		$cookie = '';
		$html = new DOMDocument();


		$a = $this->cURL("https://login.facebook.com/login.php?login_attempt=1",true,null,"email=$login_email&pass=$login_pass");
		preg_match('%Set-Cookie: ([^;]+);%',$a,$b);
		$c = $this->cURL("https://login.facebook.com/login.php?login_attempt=1",true,$b[1],"email=$login_email&pass=$login_pass");
		preg_match_all('%Set-Cookie: ([^;]+);%',$c,$d);

		for($i=0;$i<count($d[0]);$i++)
			$cookie.=$d[1][$i].";";

		$page = $this->cURL("https://m.facebook.com/hashtag/$hashtag",null,$cookie,null);
		//$page = str_replace('class=\\"','"',$page);
		//$page = str_replace('id=\\"','"',$page);
		//$page = str_replace('\u003C','<',$page);
		$page = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
		    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
		}, $page);

		$page=stripslashes($page);

		//$html = $html->loadHTML($page);
		//$html->saveHTMLFile("FB.html");
		
		$html->loadHTML($page);

		$root = $html->getElementsByTagName('div');

		$cerca_post = 0;
		$arr = array();
		$arrTMP = array();
		/**
		 * CAMPO textContent
		 */
		$cosaLeggo=0;

		if( $root->length <= 0 ){
			die();
		}

		foreach ($root as $r) {
			$idName = "";
			if(count($r->attributes->getNamedItem("id")) != 0){
				$idName = $r->attributes->getNamedItem("id")->value;
			}
			if($idName == "root"){
				$root = $r;
				$cerca_post = 1;
				$idName = $r->attributes->getNamedItem("id")->value;
			}

			if($cerca_post == 1 && $idName != "" && $idName != "root"){
				$r = $r->getElementsByTagName('div');
				$length = $r->length;

				foreach ($r as $rIN) {
					$text = $rIN->textContent;
					//DEBUG echo $i.") ".$text."<br>";
					
					switch ($i) {
						case 0:
							$arrTMP[ "standard"] = ($this->getImg($rIN));
							if(count($arrTMP[ "standard"]) == 0){
								$arrTMP["type"] = "text";
							}else{
								$arrTMP["type"] = "image";
							}
							break;
						case 1:
							$arrTMP["userName"] = $text;
							$arrTMP["user"] = $arrTMP["userId"] = $this->getUserId($rIN);
							$arrTMP["user"] = $arrTMP["userId"] = ($arrTMP["userId"]=="")?$arrTMP["userName"]:$arrTMP["userId"];
							break;	
						case 2:
							$arrTMP["text"] = $text;
							break;	
					}
					if($length == 8 && $i>2){
						switch ($i) {
							case 6: //DATA
								$arrTMP["dateTime"] = $this->cleanDate($text);
								break;	
							case 5: //commenti notizia
								$arrTMP["link"] = $this->getLink($rIN);
								break;

						}
						
					}else if($length > 8 && $i>2){
						switch ($i) {
							case 5: //url
								$arrTMP["text"] .= $text;					
								break;	
							case 7: //DATA
								$arrTMP["text"] .= $this->cleanDate($text);
								break;	
							case 8: //commenti notizia
								$arrTMP["link"] = $this->getLink($rIN);
								break;
						}
					}

					$i++;
				}
				$arrTMP["social"] = "facebook";
				$arr[$this->toTimestamp($arrTMP["dateTime"])][] = $arrTMP;

				$i =0;
				$arrTMP = array();
			}
			unset($arr[0]);
		}

		$this->tag = $arr;
	}

}


