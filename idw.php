<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Idw2 extends CI_Controller {

    function __construct()
    {
         parent::__construct();

    }
    public function index()
    {
      set_time_limit(1200);
      $_xs = array();
      $_ys = array();
      $_f = array();

      $_xs[0]=434;
      $_ys[0]=63;
      $_xs[1]=485;
      $_ys[1]=89;
      $_xs[2]=526;
      $_ys[2]=113;
      $_xs[3]=569;
      $_ys[3]=142;
      $_xs[4]=618;
      $_ys[4]=172;
      $_xs[5]=660;
      $_ys[5]=196;
      $_xs[6]=648;
      $_ys[6]=305;
      $_xs[7]=599;
      $_ys[7]=277;
      $_xs[8]=549;
      $_ys[8]=248;
      $_xs[9]=495;
      $_ys[9]=213;
      $_xs[10]=443;
      $_ys[10]=183;
      $_xs[11]=395;
      $_ys[11]=151;
      $_xs[12]=653;
      $_ys[12]=154;
      $_xs[13]=633;
      $_ys[13]=89;


      $_f[0] = 0.142857142857142;
      $_f[1] = 0.38095238095238;
      $_f[2] = 0;
      $_f[3] = 0.571428571428571;
      $_f[4] = 0.666666666666667;
      $_f[5] = 0.809523809523809;
      $_f[6] = 0.714285714285714;
      $_f[7] = 0.761904761904762;
      $_f[8] = 0.476190476190476;
      $_f[9] = 0.0952380952380949;
      $_f[10] = 0.238095238095238;
      $_f[11] = 0.333333333333333;
      $_f[12] = 1;
      $_f[13] = 0.857142857142857;

      $pixels = array($_xs,$_ys);

      $this->createImage(721,451,$pixels,$_f);

    }

    private function createImage($x,$y,$pixels,$value){

        $img = imagecreatetruecolor($x, $y);
        for($i=1;$i<=$x;$i += 5){
            for($j=0;$j<$y;++$j){
                $_hue = $this->getInterpValue($i,$j, $pixels[0], $pixels[1], $value);

                $rgb_t = $this->colormap_hsv_to_rgb(255-(255*$_hue), 255, 255);

                $colorI = imagecolorallocate($img, $rgb_t->r, $rgb_t->g, $rgb_t->b);

                imagesetpixel($img, $i, $j, $colorI);
                imagesetpixel($img, $i+1, $j, $colorI);
                imagesetpixel($img, $i+2, $j, $colorI);
                imagesetpixel($img, $i+3, $j, $colorI);
                imagesetpixel($img, $i+4, $j, $colorI);
                //imagesetpixel($img, $i+5, $j, $colorI);
                //imagesetpixel($img, $i+6, $j, $colorI);

            }
        }
        //exit();
        // Dump the image to the browser
        header('Content-Type: image/png');
        imagepng($img);

        // Clean up after ourselves
        //imagedestroy($img);
    }

    private function distPoints($x1,$y1,$x2,$y2){
        $v = pow(($x2 - $x1),2) + pow(($y2 - $y1),2);
        $v = abs($v);
        $d = sqrt($v);
        return $d;
    }

    //ITERATES THROUGH ALL THE DATA POINTS AND FINDS THE FURTHERS ONE
    private function getMaxDistanceFromPoint($x,$y,$xs,$ys) {
      $maxDistance=0;
      //get disance between this and each pther point
      for ($i=0;$i<sizeof($xs);$i++) {
        $thisDist = $this->distPoints($x, $y, $xs[$i], $ys[$i]);
        // echo "x: ".$x." y: ".$y."<br />";
        // echo "xs: ".$xs[$i]." ys: ".$ys[$i]."<br />";
        // echo "thisDist: ".$thisDist."<br /><br />";
        //if this distance is greater than previous distances, this is the new max
        if ($thisDist>$maxDistance) {
          $maxDistance = $thisDist;
        }
      }
      return $maxDistance;
    }

    //RETURNS AN ARRAY OF THE DISTANCE BETWEEN THIS PIXEL AND ALL DATA POINTS
    private function getAllDistancesFromPoint($x,$y,$xs,$ys) {
      $allDistances = array();
      for ($i=0;$i<sizeof($xs);$i++) {
        $allDistances[$i] = $this->distPoints($x, $y, $xs[$i], $ys[$i]);
      }

      return $allDistances;
    }

    //RETURNS THE ACTUAL WEIGHTED VALUE FOR THIS PIXEL
    private function getInterpValue($x,$y,$xs,$ys,$f) {
      $interpValue=0;
      //echo "x: ".$x." y: ".$y."<br />";
      $maxDist = $this->getMaxDistanceFromPoint($x,$y,$xs,$ys);
      $allDistances = $this->getAllDistancesFromPoint($x,$y,$xs,$ys);
      for ($i=0;$i<sizeof($xs);$i++) {
        $thisDistance = $this->distPoints($x,$y,$xs[$i],$ys[$i]);

          $interpValue += $f[$i]*$this->getWeight($maxDist,$thisDistance,$allDistances);

        // echo "maxDist: ".$maxDist."<br /><br />";
        // echo "allDistances:<br /> <pre>";
        // var_dump($allDistances);
        // echo "</pre><br />";
        // echo "thisDistance: ".$thisDistance."<br />";
        // echo "<br />interpValue: ".$interpValue;
        // echo "<br /><br /><br /><br /><br />";
      }
      //exit();
      return $interpValue;
    }

    //THE WEIGHT IS THE VALUE COEFFICIENT (? RIGHT TERM) BY WHICH WE WILL MULTIPLY EACH VALUE TO GET THE CORRECT WEIGHTING
    private function getWeight($maxDistance,$thisDistance,$allDistances) {
      $weight = 0;
      if(($maxDistance * $thisDistance) == 0){
            $value = 1;

      }
      else
        $value = ($maxDistance - $thisDistance)/($maxDistance * $thisDistance);
      $firstTerm = pow($value,2);
      $secondTerm=0;
      for ($i=0;$i<sizeof($allDistances);$i++) {
        if(($maxDistance * $allDistances[$i]) == 0){
            $value = 1;
        }
        else
            $value = ($maxDistance - $allDistances[$i])/($maxDistance * $allDistances[$i]);
        $secondTerm += pow($value, 2);
      }
      $weight = $firstTerm/$secondTerm;
      // echo "maxDist: ".$maxDistance."<br /><br />";
      // echo "thisDistance: ".$thisDistance."<br />";
      // echo "weight: ".$weight."<br /><br />";
      return $weight;
    }

    private function hsv2rgb($c) { 
         list($h,$s,$v)=$c; 
         if ($s==0) 
          return array($v,$v,$v); 
         else { 
          $h=($h%=360)/60; 
          $i=floor($h); 
          $f=$h-$i; 
          $q[0]=$q[1]=$v*(1-$s); 
          $q[2]=$v*(1-$s*(1-$f)); 
          $q[3]=$q[4]=$v; 
          $q[5]=$v*(1-$s*$f); 
          //return(array($q[($i+4)%5],$q[($i+2)%5],$q[$i%5])); 
          return(array($q[($i+4)%6],$q[($i+2)%6],$q[$i%6])); //[1] 
         }
    } 

    private function &colormap_hsv_to_rgb($h, $s, $v) 
  { 
    $ret = new stdClass(); 

    if($s == 0) 
    { 
      $ret->r = $v; 
      $ret->g = $v; 
      $ret->b = $v; 

      return $ret; 
    } 
    else 
    { 
      $h = floatval($h) / 255.0; 
      $s = floatval($s) / 255.0; 
      $v = floatval($v) / 255.0; 

      $hue = $h; 

      if($hue == 1.0) 
        $hue = 0.0; 

      $hue *= 6.0; 

      $i = intval($hue); 
      $f = $hue - floatval($i); 
      $w = $v * (1.0 - $s); 
      $q = $v * (1.0 - ($s * $f)); 
      $t = $v * (1.0 - ($s * (1.0 - $f))); 

      switch($i) 
      { 
         case 0: $ret->r = $v; $ret->g = $t; $ret->b = $w; break; 
         case 1: $ret->r = $q; $ret->g = $v; $ret->b = $w; break; 
         case 2: $ret->r = $w; $ret->g = $v; $ret->b = $t; break; 
         case 3: $ret->r = $w; $ret->g = $q; $ret->b = $v; break; 
         case 4: $ret->r = $t; $ret->g = $w; $ret->b = $v; break; 
         case 5: $ret->r = $v; $ret->g = $w; $ret->b = $q; break; 
      } 
    } 
    
    $ret->r = intval($ret->r * 255.0); 
    $ret->g = intval($ret->g * 255.0); 
    $ret->b = intval($ret->b * 255.0); 

    return $ret; 
  } 
}
?>
