<?php
class services{
	var $error = '';
	var $msg = '';
	var $key = 'SYMBIOTIC';
	public $sdb;
	
	public function __construct(){
	global $db;
	 $this->sdb = $db;
	}
    public function show_services(){
        $stmt = "SELECT * FROM  sym_services WHERE active = 1";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result){
            return $result;
        }
        return false;
    }

    public function show_services_by_haircut_id($haicutId){
        $stmt = "SELECT * FROM  sym_haircuts WHERE id = :haicutId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":haicutId", $haicutId);
        $result = $this->sdb->single();
        if($result){
            $export = array();
            foreach (json_decode($result['serviceId']) as $service){
                 array_push($export,$this->show_services_by_id($service));
            }
            return $export;
        }
        return false;
    }

    public function show_services_by_id(int $serviceId){
        $stmt = "SELECT * FROM  sym_services WHERE id = :serviceId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":serviceId", $serviceId);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }

    public function show_service_by_name($serviceName){
        $stmt = "SELECT * FROM  sym_services WHERE name = :serviceName";
        $this->sdb->query($stmt);
        $this->sdb->bind(":serviceName", $serviceName);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }

    public function show_services_from_db_id_translate($servicesId){

        $export = array();
        foreach (json_decode($servicesId) as $service){
            $serviceInf = $this->show_services_by_id($service);
            array_push($export,$serviceInf['name']);
        }
        return implode(',',$export);
    }
}

?>