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
        $stmt = "SELECT * FROM  sym_hair_cutters WHERE id = :barberId AND active = 1";
        $this->sdb->query($stmt);
        $this->sdb->bind(":barberId", $barberId);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }

    public function show_barbers_available_for_outcall($lang){
        $settings = new Settings();
        $setting = $settings->get_all();
        $stmt = "SELECT * FROM  sym_hair_cutters WHERE lng > 0 AND lat > 0 AND outcall = 1 AND pricePerKm > 0 AND maxDistance > 0 AND active = 1";
        $this->sdb->query($stmt);
        $results = $this->sdb->resultset();
        if($results){
            foreach ($results as $res =>$value){
                if(isset($results[$res]['icon']) && !empty($results[$res]['icon']) && $results[$res]['icon'] !== ''){ //update search also if its specific .format
                    $results[$res]['iconPath'] = $setting['barberIconsPath'];
                }else{
                    $results[$res]['iconPath'] = 'images/';
                    $results[$res]['icon'] = $setting['defaultServiceProviderImage'];
                }
                $results[$res]['name'] = $settings->render_lang($results[$res]['name'],$results[$res]['nameEN'],$lang);
            }
            return $results;
        }
        return false;

    }

    public function gather_barbers_by_cat_id_and_service_type($categoryId,$extraData,$lang){
	    $settings = new Settings();
        $setting = $settings->get_all();
        if ($extraData['serviceTypeSelected'] && ($extraData['serviceTypeSelected'] == 'incall' || $extraData['serviceTypeSelected'] == 'outcall')){
            if($extraData['serviceTypeSelected'] == 'incall'){
                $stmt = "SELECT id,name,nameEN,icon FROM sym_hair_cutters WHERE active = 1 AND incall = 1 AND JSON_CONTAINS(catIdsExecuted, :categoryId, '$')";
            }
            if($extraData['serviceTypeSelected'] == 'outcall'){
                $stmt = "SELECT id,name,nameEN,icon FROM sym_hair_cutters WHERE active = 1 AND outcall = 1 AND JSON_CONTAINS(catIdsExecuted, :categoryId, '$')";
            }
            $this->sdb->query($stmt);
            $this->sdb->bind(":categoryId", json_encode($categoryId));
            $results = $this->sdb->resultSet();
            if($results){
                foreach ($results as $res =>$value){
                    if(isset($results[$res]['icon']) && !empty($results[$res]['icon']) && $results[$res]['icon'] !== ''){ //update search also if its specific .format
                        $results[$res]['iconPath'] = $setting['barberIconsPath'];
                    }else{
                        $results[$res]['iconPath'] = 'images/';
                        $results[$res]['icon'] = $setting['defaultServiceProviderImage'];
                    }
                    $results[$res]['name'] = $settings->render_lang($results[$res]['name'],$results[$res]['nameEN'],$lang);
                    unset($results[$res]['nameEN']);
                }
                return $results;
            }
        }
        return false;
    }

}

?>