<?php
class knownAreas{
	var $error = '';
	var $msg = '';
	var $key = 'SYMBIOTIC';
	public $sdb;
	
	public function __construct(){
	global $db;
	 $this->sdb = $db;
	}


	public function gather_area_by_url_param($urlParam){
        $stmt = "SELECT * FROM  sym_known_areas WHERE urlParam=:urlParam";
        $this->sdb->query($stmt);
        $this->sdb->bind(':urlParam',$urlParam);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }




}

?>