<?php
class barbers{
	var $error = '';
	var $msg = '';
	var $key = 'SYMBIOTIC';
	public $sdb;
	
	public function __construct(){
	global $db;
	 $this->sdb = $db;
	}
    public function show_barbers(){
        $stmt = "SELECT * FROM  sym_hair_cutters WHERE active = 1";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result){
            return $result;
        }
        return false;
    }
    public function count_barbers(){
        $stmt = "SELECT * FROM  sym_hair_cutters WHERE active = 1";
        $this->sdb->query($stmt);
        $this->sdb->resultset();
        $result = $this->sdb->rowCount();
        if($result){
            return $result;
        }
        return false;
    }

    public function show_barbers_by_haircut_id($haicutId){
        $stmt = "SELECT * FROM  sym_haircuts WHERE id = :haicutId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":haicutId", $haicutId);
        $result = $this->sdb->single();
        if($result){
            return $result['hairCutterId'];
        }
        return false;
    }


    public function show_barbers_by_id($barberId){
        $stmt = "SELECT * FROM  sym_hair_cutters WHERE id = :barberId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":barberId", $barberId);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }
}

?>