<?php 

class Tempo{
	
	//local class variables
	protected $_mtID;
	
	protected $_future;
	protected $_past;
	protected $_present;
	protected $_tempo;
	protected $_long_name;
	protected $_keywords;
	protected $_insight;
	
	protected $_tempos;
	
	public function Tempo($tempoId){
		$this->_tempoId = $tempoId;
		$this->queryTempo();
		$tempos = array();
	}
	
	private function queryTempo(){
		$sql = "SELECT api.section, api.type, api.txt, api90.long_name, api90.keywords, api90.insight, api90.tempo_id
				FROM api_archetype_data_90 api90 
				JOIN api_tempos api ON api.tempos = api90.tempo_id
				WHERE api90.tempo_id = \"$this->_tempoId\"";
		
		$result = mysql_query($sql);
		
		$first = true;
		while($myrow = mysql_fetch_array($result)){
			if($first){
//				$this->_future = $myrow['future'];
//				$this->_past = $myrow['past'];
//				$this->_present = $myrow['present'];
				$this->_tempo = $myrow['tempo_id'];
				$this->_long_name = $myrow['long_name'];
				$this->_keywords = $myrow['keywords'];
				$this->_insight = $myrow['insight'];
				$first = false;
			}
			$section = $myrow['section'];
			$type = $myrow['type'];
			$txt = $myrow['txt'];
			
			$this->_tempos[$section][$type] = $txt;
		}
	}
	
	public function renderTempo($section = "OVERVIEW"){
		echo "<h2 style='text-align: center; font-size: 16px; margin-bottom: 5px;'>$section</h2>";
		if($section == "OVERVIEW") $tempo =  stripslashes($this->_tempos[$section]["KEY CONCEPT"].$this->_tempos[$section]["TXT"].$this->_tempos[$section]["CHALLENGES"]);
//		else $tempo =  stripslashes($this->_tempos[$section]["TRUISM"].$this->_tempos[$section]["TXT"].$this->_tempos[$section]["RESISTANCE"]);
		else $tempo =  "<br>".stripslashes($this->_tempos[$section]["TXT"].(isset($this->_tempos[$section]["RESISTANCE"]) ? $this->_tempos[$section]["RESISTANCE"] : ''));
		$tempo = preg_replace('/[^(\x20-\x7F)]*/','', $tempo);
		return $tempo;
	}
	
	
}





?>
