<?php
class Instagram{

    private $tag = null;
    private $user = null;

    function __construct($settings, $tag){
          $userid       =$settings['userid'];
          $client_id    =$settings['client_id'];
          $access_token =$settings['access_token'];

        $this->tag  = $this->getTagPhoto($tag, $client_id);
        if( $access_token != null )
            $this->user = $this->getUserPhoto($tag, $userid, $access_token);
    }

    function getVar(){
        return array($this->tag,$this->user);
    }

    function getTagPhoto($tag, $client_id){
        $url = 'https://api.instagram.com/v1/tags/'.htmlspecialchars($tag).'/media/recent?client_id='.$client_id;
        $inst_stream = $this->callInstagram($url);
        $results = json_decode($inst_stream, true);

        if(is_null($results)) return null;

        $arr = array();

        foreach($results['data'] as $item){

            $createTime = $item['created_time'];

            $arr[$createTime][] = array(
                    "id"        => $item['id'],
                    "standard"  => $item['images']['standard_resolution']['url'],
                    "link"      => $item['link'],
                    "user"      => $item['user']['username'],
                    "userId"    => $item['user']['id'],
                    "userName"  => $item['user']['full_name'],
                    "text"      => htmlentities($item['caption']['text']),
                    "type"      => $item['type'],
                    "social"    => "instagram"
                );
        }
        return $arr;
    }

    function getUserPhoto($tag, $userid, $access_token){
        $url = "https://api.instagram.com/v1/users/".$userid.'/media/recent?access_token='.$access_token;
        $inst_stream = $this->callInstagram($url);
        $results = json_decode($inst_stream, true);

        if(is_null($results)) return null;

        $arr = array();

        foreach($results['data'] as $item){

            $createTime = $item['created_time'];

            $arr[$createTime][] = array(
                    "standard" => $item['images']['standard_resolution']['url'],
                    "link" => $item['link'],
                    "user" => $item['user']['username'],
                    "userId" => $item['user']['id'],
                    "userName" => $item['user']['full_name'],
                    "text" => $item['caption']['text'],
                    "type" => $item['type'],
                    "social"    => "instagram"
                );
        }
        return $arr;
    }

    private function callInstagram($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2
        ));

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
?>
