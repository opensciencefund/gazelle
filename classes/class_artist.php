<?
class ARTIST {
	var $ID = 0;
	var $Name = 0;
	var $SimilarID = 0;
	var $Displayed = false;
	var $x = 0;
	var $y = 0;
	var $Similar = array();
	
	function ARTIST($ID='', $Name=''){
		$this->ID = $ID;
		$this->Name = $Name;
	}
	
}


?>