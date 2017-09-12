<?php 

//include_once dirname(__FILE__)."/fx.php";

class GPSFX {

	//local class variables
	var $_maxts; 
	var $_hights; 
	var $_midts; 
	var $_lowts; 
	var $_highgap; 
	var $_midgap; 
	var $_lowgap;
	
	var $_tempo;
	var $_present;
	var $_past;
	var $_future;
	
	
	public function GPSFX($max = 600){
		$this->_maxts = $max; 
		$this->_hights = 75; 
		$this->_midts = 40; 			//changed from 50 on 5-11-2010
		$this->_lowts = 25; 			//changed from 35 on 5-11-2010
		$this->_highgap = 15; 
		$this->_midgap = 8; 
		$this->_lowgap = 6;
	}
	
	public function calcGPS($future, $past, $present){
		
		$this->_past = $past;
		$this->_present = $present;
		$this->_future = $future;
		
		$ts1 = max($past, $present, $future); // Value of maximum timestyle
	    $ts3 = min($past, $present, $future); // Value of minimum timestyle
	    $ts2 = $past + $present + $future - $ts1 - $ts3; // Value of the middle timestyle
	    $gap = $ts1 - $ts3; // Gap between primary and final timestyle
	    $gap1 = $ts1 - $ts2; // Gap between primary and secondary timestyle
	    $gap2 = $ts2 - $ts3; // Gap between secondary and final tyimestyle
	    $scale = $this->_maxts / 100; // Scaling factor for intensity ranges
	    $highbreak = $this->_hights * $scale; // Value for the high range break
	    $midbreak = $this->_midts * $scale;  // Value for the mid range break
	    $lowbreak = $this->_lowts * $scale;  // Value for the low range break
	    $highrange = $this->_highgap * $scale;  // Value for the high range gap
	    $midrange = $this->_midgap * $scale;  // Value for the mid range gap
	    $lowrange = $this->_lowgap * $scale;  // Value for the low range gap
	
	    // Figure out the order of the three timestyles
	    // Future = 1, Past = 2, Present = 3
	    // $X = Number of primary timestyle ($ts1)
	    // $Y = Number of secondary tyimestyle ($ts2)
	    // $Z = Number of final timestytle ($ts3)
	    
	    $fpp = array("1" => $future, "2" => $past, "3" => $present);
	    arsort($fpp);
	    
	    $counter = 1;
	    foreach($fpp as $key => $value){
	    	if($counter == 1) $X = $key;
	    	else if($counter == 2) $Y = $key;
	    	else if($counter == 3) $Z = $key;
	    	$counter++;
	    }
	    /*
	    if ( $ts1 == $future ) $X=1;
	    elseif ( $ts1 == $past ) $X=2;
	    elseif ( $ts1 == $present ) $X=3;
	    
	    if ( $ts2 == $future && $X != 1 ) $Y=1;
	    elseif ( $ts2 == $past && $X != 2 ) $Y=2;
	    elseif ( $ts2 == $present && $X != 3 ) $Y=3;
	    
	    if ( $ts3 == $future && $X != 1 && $Y != 1 ) $Z=1;
	    elseif ( $ts3 == $past && $X != 2 && $Y != 2 ) $Z=2;
	    else
	    if ( $ts3 == $present && $X != 3 && $Y != 3 ) $Z=3;
		*/
	    // Dominant Integrated
	    if ( $ts1 >= $highbreak && $ts3 >= $midbreak && $gap < $highrange ){
	      $tempo = sprintf("%s%s%sDN", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Dominant Integrated with Lead
	    if ( $ts3 >= $highbreak && $gap >= $highrange && $gap1 >= $gap2 ){
	      $tempo = sprintf("%s%s%sDNL", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Dominant Blend with No Resistance
	    if ( $ts1 >= $highbreak && $ts3 >= $midbreak && $gap >= $highrange && $gap1 < $gap2 ){
	      $tempo = sprintf("%s%s%sBD", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Dominant Blend with Single Resistance
	    if ( $ts2 >= $highbreak && $ts3 < $midbreak && $gap1 < $highrange ){
	      $tempo = sprintf("%s%s%sBDR", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Dominant with No Resistance
	    if ( $ts1 >= $highbreak && $ts3 >= $midbreak && $ts3 < $highbreak && $gap >= $highrange && $gap1 >= $gap2 ){
	      $tempo = sprintf("%s%s%sD", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Dominant with Single Resistance
	    if ( $ts1 >= $highbreak && $ts2 >= $midbreak && $ts3 < $midbreak && $gap1 >= $highrange && $gap2 >= $midrange ){
	      $tempo = sprintf("%s%s%sDR", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Dominant with Double Resistance
	    if ( ( $ts1 >= $highbreak && $ts2 >= $midbreak && $ts2 < $highbreak && $ts3 < $midbreak && $gap1 >= $highrange && $gap2 < $midrange ) ||
	         ( $ts1 >= $highbreak && $ts2 < $midbreak ) ){
	      $tempo = sprintf("%s%s%sDRR", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Moderate Integrated
	    if ( $ts1 >= $midbreak && $ts1 < $highbreak && $gap < $midrange ){
	      $tempo = sprintf("%s%s%sMN", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Moderate Integrated with Lead
	    if ( $ts3 >= $midbreak && $ts1 < $highbreak && $gap >= $midrange && $gap1 >= $gap2 ){
	      $tempo = sprintf("%s%s%sMNL", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Moderate Blend with No Resistance
	    if ( $ts3 >= $midbreak && $ts1 < $highbreak && $gap >= $midrange && $gap1 < $gap2 ){
	      $tempo = sprintf("%s%s%sBM", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Moderate Blend with Single Resistance
	    if ( ( $ts1 >= $highbreak && $ts2 >= $midbreak && $ts2 < $highbreak && $ts3 < $midbreak && $gap1 < $highrange ) ||
	         ( $ts1 >= $midbreak && $ts1 < $highbreak && $ts3 < $midbreak && $gap >= $midrange && $gap1 < $midrange ) ){
	      $tempo = sprintf("%s%s%sBMR", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Moderate with No Resistance (This case does not exist)
	
	    // Moderate with Single Resistance
	    if ( $ts1 >= $midbreak && $ts1 < $highbreak && $ts3 < $midbreak && $gap >= $midrange && $gap1 >= $midrange && $gap1 < $gap2 ){
	      $tempo = sprintf("%s%s%sMR", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Moderate with Double Resistance
	    if ( $ts1 >= $midbreak && $ts1 < $highbreak && $ts3 < $midbreak && $gap >= $midrange && $gap1 >= $midrange && $gap1 >= $gap2 ){
	      $tempo = sprintf("%s%s%sMRR", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Low Integrated
	    if ( $ts1 >= $lowbreak && $ts1 < $midbreak && $gap < $midrange ){
	      $tempo = sprintf("%s%s%sLN", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Low Integrated Spread
	    if ( $ts1 < $midbreak && ( $gap >= $midrange || $ts1 < $lowbreak ) ) {
	      $tempo = sprintf("%s%s%sLN", $X, $Y, $Z);
	      $this->_tempo = $tempo;
	      return $tempo;
	    }
	
	    // Error No Tempo Found (this should hopefully not happen)
	    $tempo = sprintf("%s%s%sERROR", $X, $Y, $Z);
	    $this->_tempo = $tempo;
	    return $tempo;
	}
	
	function storeTempo(){
		$past = $this->_past;
		$present = $this->_present;
		$future = $this->_future;
		$sql = "INSERT INTO fb_gps_results (id, past, present, future, tempo) values(0, $past, $present, $future, '$this->_tempo')";
		$result = mysql_query($sql);
		return mysql_insert_id();	
	}

	public function renderBadge($size = 102){
		
		if($size == 102) $h = 93;
		else if($size == 68) $h = 62;
		
		$scale = 100 / $this->_maxts;
		$past    = $this->_past * $scale;
		$present = $this->_present * $scale;
		$future  = $this->_future * $scale;
		
		$badge = "";
		if($future < 25) $badge .= "0";
		else if($future < 40) $badge .= "1";
		else if($future < 75) $badge .= "2";
		else if($future < 101) $badge .= "3";
		
		if($past < 25) $badge .= "0";
		else if($past < 40) $badge .= "1";
		else if($past < 75) $badge .= "2";
		else if($past < 101) $badge .= "3";
		
		if($present < 25) $badge .= "0";
		else if($present < 40) $badge .= "1";
		else if($present < 75) $badge .= "2";
		else if($present < 101) $badge .= "3";
		
		if($future > 74 && $past > 74 && $present < 75){
			if($future >= $past) $badge .= "-1";
			else $badge .= "-2";
		}else if($future > 74 && $past < 75 && $present > 74){
			if($future >= $present) $badge .= "-1";
			else $badge .= "-3";
		}else if($future < 75 && $past > 74 && $present > 74){
			if($past >= $present) $badge .= "-2";
			else $badge .= "-3";
		}else if($future > 74 && $past > 74 && $present > 74){
			if($future >= $past && $future >= $present){
				if($past >= $present) $badge .= "-12";
				else $badge .= "-13";
			}else if($past >= $future && $past >= $present){
				if($future >= $present) $badge .= "-21";
				else $badge .= "-23";
			}else if($present >= $future && $present >= $past){
				if($future >= $past) $badge .= "-31";
				else $badge .= "-32";
			}else $badge .= "-12";
			
		}
		
		$badge .= ".png";
		?>
		<p style="text-align: right;"><img style="border:2px solid green" src="img/BADGES/<?php echo $size?>b/<?php echo $badge?>" width="<?php echo $size?>" height="<?php echo $h?>" alt="mindtime thinking style badge (<?php echo $this->_future.",".$this->_past.",".$this->_present;?>)" /></p>
		<?php
	}

	public function renderIntensityBars(){
		$scale = 100 / $this->_maxts;
		?>
		<div style="width: 102px; height: 81px; border: 1px solid #FFF; position: relative; margin-bottom: 8px; margin-top: 5px; ">
			<div style="width: 24px; height: 81px; border-right: 1px solid #CCC; position: absolute; top: 0px; left: 0px;">&nbsp;</div>
			<div style="width: 24px; height: 81px; border-right: 1px solid #CCC; position: absolute; top: 0px; left: 25px;">&nbsp;</div>
			<div style="width: 24px; height: 81px; border-right: 1px solid #CCC; position: absolute; top: 0px; left: 50px;">&nbsp;</div>
			
			<div style="width: 200px; height: 17px; margin-bottom: 10px; margin-top: 5px;">
				<div style="width: 100px; height: 15px; border: 1px solid #EC1C24; float: left; display: inline; position: relative;">
					<div style="width: <?php echo $scale*$this->_past;?>px; top: 0px; left: 0px; position: relative; height: 15px; background: #EC1C24;">&nbsp;</div>
				</div>
				<div style="margin-left: 10px; float: left; display: inline; line-height: 17px;">
					<a>Past</a>
				</div>
			</div>
			<div style="width: 200px; height: 17px; margin-bottom: 10px;">
				<div style="width: 100px; height: 15px; border: 1px solid #05924C; float: left; display: inline; position: relative;">
					<div style="width: <?php echo $scale*$this->_present;?>px; top: 0px; left: 0px; position: relative; height: 15px; background: #05924C;">&nbsp;</div>
				</div>
				<div style="margin-left: 10px; float: left; display: inline; line-height: 17px;">
					<a>Present</a>
				</div>
			</div>
			<div style="width: 200px; height: 17px; margin-bottom: 15px;">
				<div style="width: 100px; height: 15px; border: 1px solid #2E358F; float: left; display: inline; position: relative;">
					<div style="width: <?php echo $scale*$this->_future;?>px; top: 0px; left: 0px; position: relative; height: 15px; background: #2E358F;">&nbsp;</div>
				</div>
				<div style="margin-left: 10px; float: left; display: inline; line-height: 17px;">
					<a>Future</a>
				</div>
			</div>
		</div>
		<?php 
	}

}//class GPSFX{}

?>
