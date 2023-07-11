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

    public function show_services_by_param($location,$extraData = null){ //from url param
	    if ($extraData['serviceTypeSelected']){
            $stmt = "SELECT * FROM  sym_services WHERE active = 1 and previewOnServices = 1 AND type = :serviceType ORDER BY short ASC";
            $this->sdb->query($stmt);
            $this->sdb->bind(":serviceType", $extraData['serviceTypeSelected']);
            $services = $this->sdb->resultset();
        }else{
            $stmt = "SELECT * FROM  sym_services WHERE active = 1 and previewOnServices = 1 ORDER BY short ASC";
            $this->sdb->query($stmt);
            $services = $this->sdb->resultset();
        }
        $result = [];
        if($services){
            $areaId = 0; // Default area ID
            if (!empty($location)) {
                // Get the ID of the known area by its URL parameter
                $stmt = "SELECT id FROM sym_known_areas WHERE urlParam = :urlParam";
                $this->sdb->query($stmt);
                $this->sdb->bind(":urlParam", $location);
                $areaIdResult = $this->sdb->single();

                // If the area is found, use its ID, else keep the default
                if ($areaIdResult) {
                    $areaId = $areaIdResult['id'];
                }
            }
            // Check each service if it's available in this area
            foreach ($services as $service) {
                $availableOnAreas = json_decode($service['availableOnAreas'], true);
                if (in_array($areaId, $availableOnAreas)) {
                    $result[] = $service;
                }
            }
        }
        return !empty($result) ? $result : false;
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

    public function show_services_by_id_limited(int $serviceId,$lang){
	    $settings = new Settings();
        $stmt = "SELECT * FROM  sym_services WHERE id = :serviceId AND previewonservices = 1 AND active = 1 ORDER BY short ASC";
        $this->sdb->query($stmt);
        $this->sdb->bind(":serviceId", $serviceId);
        $result = $this->sdb->single();
        if($result){
            $selectedDescription = $settings->render_lang($result['description'],$result['descriptionEn'],$lang);
            $constructedDesc = '';
            if($result['avExecutionPrint'] == '1'){
                $constructedDesc = ' <small style="color: grey;font-size: 12px;">️≈ '. $result['avExecution'] .' - '. $result['avExecutionStandAlone'] .'  ⌛</small>';
            }else if($result['description'] !== '' && !is_null($result['description']) && !empty($result['description'])){
                $constructedDesc = '<small style="color: #03a76f;font-size: 12px;">'.$selectedDescription.'</small>';
            }
            $export['id'] = $result['id'];
            $export['icon'] = 'images/services/'.$result['icon'];
            $export['name'] = $settings->render_lang($result['name'],$result['nameEn'],$lang);
            $export['type'] = $result['type'];
            $export['description'] = $constructedDesc;
            return $export;
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

    public function gather_category_by_id($categoryId){
        $stmt = "SELECT * FROM  sym_services_categories WHERE id = :serviceId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":serviceId", $categoryId);
        $result = $this->sdb->single();
        if($result){
            if($result['icon'] == '' || empty($result['icon']) || is_null($result['icon'])){
                $result['icon'] = '';
            }else{
                $result['icon'] = 'images/categories/'.$result['icon'];
            }
            return $result;
        }
        return false;
    }

    public function gather_categories_with_services_and_barbers($selectedArea,$lang,$extraData=null) {
        $settings = new Settings();
        $barbers = new barbers();
        $activeServices = $this->show_services_by_param($selectedArea,$extraData);
        $servicesOnCategories = [];
        foreach ($activeServices as $serv => $value) {
            $categoryId = $activeServices[$serv]['categoryId'];
            $serviceId = $activeServices[$serv]['id'];
            // Check if the category already exists in servicesOnCategories
            $categoryExists = false;
            foreach ($servicesOnCategories as &$category) {
                if ($category['id'] == $categoryId) {
                    $categoryExists = true;
                    // Add the service to the category's 'services' array
                    $category['services'][] = $this->show_services_by_id_limited($serviceId,$lang);
                    break;
                }
            }
            if (!$categoryExists) {
                $categoryData = $this->gather_category_by_id($categoryId);
                // Create a new category entry with all category details and 'services' array containing the current service
                $categoryArray = [
                    'id' => $categoryId,
                    'name' => $settings->render_lang($categoryData['name'],$categoryData['nameEn'],$lang),
                    'icon' => ' <img src="'.$categoryData['icon'].'" style="width:20px;height:20px;border-radius:100%;margin-right:10px;"> ',
                    'short' => $categoryData['short'],
                    'services' => [
                        $this->show_services_by_id_limited($serviceId,$lang)
                    ],
                    //reduce here the data
                    'barbers' => $barbers->gather_barbers_by_cat_id_and_service_type($categoryId,$extraData,$lang)
                ];
                // Add the new category array to servicesOnCategories
                array_push($servicesOnCategories, $categoryArray);
            }
        }
        $resultArray = [];
        foreach ($servicesOnCategories as $cat) {
            array_push($resultArray, $cat);
        }
        return $resultArray;
    }

    public function gather_categories_with_services($selectedArea,$lang,$extraData) {
        $settings = new Settings();
        $activeServices = $this->show_services_by_param($selectedArea,$extraData);
        $servicesOnCategories = [];
        foreach ($activeServices as $serv => $value) {
            $categoryId = $activeServices[$serv]['categoryId'];
            $serviceId = $activeServices[$serv]['id'];
            // Check if the category already exists in servicesOnCategories
            $categoryExists = false;
            foreach ($servicesOnCategories as &$category) {
                if ($category['id'] == $categoryId) {
                    $categoryExists = true;
                    // Add the service to the category's 'services' array
                    $category['services'][] = $this->show_services_by_id_limited($serviceId,$lang);
                    break;
                }
            }
            if (!$categoryExists) {
                $categoryData = $this->gather_category_by_id($categoryId);
                // Create a new category entry with all category details and 'services' array containing the current service
                $categoryArray = [
                    'id' => $categoryId,
                    'name' => $settings->render_lang($categoryData['name'],$categoryData['nameEn'],$lang),
                    'icon' => ' <img src="'.$categoryData['icon'].'" style="width:20px;height:20px;border-radius:100%;margin-right:10px;"> ',
                    'short' => $categoryData['short'],
                    'services' => [
                        $this->show_services_by_id_limited($serviceId,$lang)
                    ]
                ];
                // Add the new category array to servicesOnCategories
                array_push($servicesOnCategories, $categoryArray);
            }
        }
        $resultArray = [];
        foreach ($servicesOnCategories as $cat) {
            array_push($resultArray, $cat);
        }
        return $resultArray;
    }


}

?>