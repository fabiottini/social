<?php

class Image {

    protected $img;
    protected $imgT;
    protected $thumb;

    public function fromString($string, $mimetype) {
        $this->image_type = $mimetype;
        $this->image      = imagecreatefromstring($string);
    }

    public function out($imgT = IMAGETYPE_JPEG, $compression = 75) {
        if ($imgT == IMAGETYPE_JPEG) {
            imagejpeg($this->image, NULL, $compression);
        } elseif ($imgT == IMAGETYPE_GIF) {
            imagegif($this->image);
        } elseif ($imgT == IMAGETYPE_PNG) {
            imagepng($this->image, NULL, $compression);
        }
   }

    public function outBuffer($imgT = IMAGETYPE_JPEG, $compression = 75) {
        ob_start();
        if ($imgT == IMAGETYPE_JPEG) {
            header('Content-Type: image/jpeg');
            imagejpeg($this->image, NULL, $compression);
        } elseif ($imgT == IMAGETYPE_GIF) {
            header('Content-Type: image/gif');
            imagegif($this->image);
        } elseif ($imgT == IMAGETYPE_PNG) {
            header('Content-Type: image/png');
            imagepng($this->image, NULL, $compression);
        }
        return ob_get_contents();
    }

    public function getWidth() {
        return imagesx($this->image);
    }

    public function getHeight() {
        return imagesy($this->image);
    }

    public function resizeAndCrop($w, $h) {
        $ratio          = $this->getWidth() / $this->getHeight();
        // lo calcolo in base alla larghezza
        $nuovaAltezza   = intval($w / $ratio);
        // lo calcolo in base all'altezza
        $nuovaLarghezza = intval($h * $ratio);
        // die($nuovaAltezza."   ".$h."   LARG  ".$nuovaLarghezza."   ".$w);
        if ($nuovaAltezza >= $h) {
            //$this->resize($w, $nuovaAltezza);
            $this->thumb = imagecreatetruecolor($w, $h);
            imagecopyresampled($this->thumb, $this->image, 0, 0, 0, 0, $w, $nuovaAltezza, $this->getWidth(), $this->getHeight());
            $this->image = $this->thumb;
        } else if ($nuovaLarghezza >= $w) {
            //$this->resize($nuovaLarghezza, $h);
            $this->thumb = imagecreatetruecolor($w, $h);
            imagecopyresampled($this->thumb, $this->image, 0, 0, 0, 0, $nuovaLarghezza, $h, $this->getWidth(), $this->getHeight());
            $this->image = $this->thumb;
        }
    }

    public function resizeSfondo($w, $h) {
        $ratio        = $this->getWidth() / $this->getHeight();
        // lo calcolo in base alla larghezza
        $nuovaAltezza = $w / $ratio;
        if ($nuovaAltezza >= $h) {
            //$this->resize($w, $nuovaAltezza);
            $this->thumb    = imagecreatetruecolor($w, $nuovaAltezza);
            imagecopyresized($this->thumb, $this->image, 0, 0, 0, 0, $w, $nuovaAltezza, $this->getWidth(), $this->getHeight());
            $this->image    = $this->thumb;
            return;
        }
        // lo calcolo in base all'altezza
        $nuovaLarghezza = $h * $ratio;
        if ($nuovaLarghezza >= $w) {
            //$this->resize($nuovaLarghezza, $h);
            $this->thumb = imagecreatetruecolor($nuovaLarghezza, $h);
            imagecopyresized($this->thumb, $this->image, 0, 0, 0, 0, $nuovaLarghezza, $h, $this->getWidth(), $this->getHeight());
            $this->image = $this->thumb;
            return;
        }
    }

    public function resizeSfondoFabio($w, $h) {
        $ratio        = $this->getWidth() / $this->getHeight();
        // lo calcolo in base alla larghezza
        $nuovaAltezza = $w / $ratio;
        //$this->resize($w, $nuovaAltezza);
        $this->thumb  = imagecreatetruecolor($w, $nuovaAltezza);
        imagecopyresized($this->thumb, $this->image, 0, 0, 0, 0, $w, $nuovaAltezza, $this->getWidth(), $this->getHeight());
        $this->image  = $this->thumb;
        return;
    }

    public function resizeCutFa($w, $h) {

        if( $this->getHeight() > $this->getWidth() ){
            $ratio        = $this->getHeight()/$this->getWidth();
            $nuovaLarghezza = $w ;
            $nuovaAltezza = $h * $ratio;
            $sW = 0;
            if( $nuovaAltezza-$h >= $h ){
                $sH = $nuovaAltezza-$h*2;
            }else{
                $sH = $nuovaAltezza-$h/2;
            }
        }else if( $this->getHeight() == $this->getWidth() ){
            $ratio        = $this->getWidth()/$this->getHeight();
            $nuovaAltezza = $h ;
            $nuovaLarghezza = $h;

            if( $nuovaLarghezza-$w >= $w ){
                $sW = $nuovaLarghezza-$w*2;
            }else{
                $sW = $nuovaLarghezza-$w/2;
            }

            $sH = 0;
        }else{
            $ratio        = $this->getWidth()/$this->getHeight();
            $nuovaAltezza = $h ;
            $nuovaLarghezza = $w * $ratio;

            if( $nuovaLarghezza-$w >= $w ){
                $sW = $nuovaLarghezza-$w*2;
            }else{
                $sW = $nuovaLarghezza-$w/2;
            }
            $sH = 0;
        }

        $this->thumb  = imagecreatetruecolor($w, $h);
        imagecopyresampled($this->thumb, $this->image, 0, 0, $sW, $sH, $nuovaLarghezza, $nuovaAltezza, $this->getWidth(), $this->getHeight());
        $this->image  = $this->thumb;
        return;
    }
}
function caricaNoImage($w,$h){
    $im = imagecreatefromjpeg("../img/immagineNonDisponibile.jpg");
    ob_start();
    imagejpeg($im);
    $imSTR = ob_get_clean();
    $img = new Image();
    $img->fromString($imSTR, IMAGETYPE_JPEG);
    if($w != null && $h != null){
        $img->resizeSfondoFabio($w, $h);
    }
    return $img->outBuffer(IMAGETYPE_JPEG);
}

function caricaImageUrl($url,$w,$h){
    $im = imagecreatefromjpeg($url);
    ob_start();
    imagejpeg($im);
    $imSTR = ob_get_clean();
    $img = new Image();
    $img->fromString($imSTR, IMAGETYPE_JPEG);
    if($w != null && $h != null){
        $img->resizeCutFa($w, $h);
    }
    return $img->outBuffer(IMAGETYPE_JPEG);
}


$url      = $_GET['url'];
//$effetto  = $_GET['e'];
$w        = $_GET['w'];
$h        = $_GET['h'];

if(!is_numeric($w)) $w=100;
if(!is_numeric($h)) $h=100;

$img = caricaImageUrl($url,$w,$h);


?>
