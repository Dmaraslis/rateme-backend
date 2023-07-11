<?php
class Settings
{
    var $error = '';
    var $msg = '';
    public $sdb;

    public function __construct()
    {
        global $db;
        $this->sdb = $db;
    }

    public function gather_access_level_by_id($id){
        $stmt ="SELECT * FROM sym_access_levels WHERE id = :id";
        $this->sdb->query($stmt);
        $this->sdb->bind(':id',$id);
        $accessLevelName = $this->sdb->single();
        if ($accessLevelName) {
            return $accessLevelName['type'];
        }
        return false;
    }


    public function get_all()
    {
        $setting = array();

        $stmt = "SELECT * FROM  " . PFX . "settings";
        $this->sdb->query($stmt);
        $rows = $this->sdb->resultset();
        foreach ($rows as $row) {
            $setting[$row['setting']] = $row['value'];
        }

        return $setting;
    }

    public function gather_cms_balances_preview(){
        $stmt = "SELECT balancesPreview FROM sym_users WHERE id=:user";
        $this->sdb->query($stmt);
        $this->sdb->bind(':user', USERID);
        $update = $this->sdb->single();
        if ($update) {
            return $update['balancesPreview'];
        }
        $this->error = "Error saving settings";
        return false;
    }

    public function update_cms_balances_preview($settingValue){
        $stmt = "UPDATE sym_users  SET  balancesPreview= :val WHERE id=:user";
        $this->sdb->query($stmt);
        $this->sdb->bind(':val', $settingValue);
        $this->sdb->bind(':user', USERID);
        $update = $this->sdb->execute();
        if ($update) {
            return true;
        }
        $this->error = "Error saving settings";
        return false;
    }

    public function update_setting($settingName,$value){
        $stmt = "UPDATE sym_settings  SET `value` = :val WHERE setting =:key";
        $this->sdb->query($stmt);
        $this->sdb->bind(':val', $value);
        $this->sdb->bind(':key', $settingName);
        $update = $this->sdb->execute();
        if ($update) {
            return true;
        }
        $this->error = "Error saving settings";
        return false;
    }

    public function update($newsettings)
    {
        //print_r($newsettings);
        foreach ($newsettings as $key => $value) {
            $stmt = "UPDATE " . PFX . "settings  SET `value` = :val WHERE setting =:key";
            $this->sdb->query($stmt);
            $this->sdb->bind(':val', $value);
            $this->sdb->bind(':key', $key);
            $update = $this->sdb->execute();
            if (!$update) {
                $this->error = "Error saving settings";
                return false;
            }
        }
        if (empty($this->error)) {
            $this->msg = "Settings updated successfully";
            return true;
        }
    }

    public function GetXmlInfo()
    {
        $stmt = "SELECT * FROM skroutz_xml_info ORDER BY id DESC LIMIT 1";
        $this->sdb->query($stmt);
        $rows = $this->sdb->single();
        if ($rows) {
            return $rows['date'];
        }
        return false;
    }

    public function arrayVarDump($array){
        return highlight_string("<?php\n\$data =\n" . var_export($array, true) . ";\n?>");
    }

    function arrayVarDump2($data){
        highlight_string("<?php\n " . var_export($data, true) . "?>");
        echo '<script>document.getElementsByTagName("code")[0].getElementsByTagName("span")[1].remove() ;document.getElementsByTagName("code")[0].getElementsByTagName("span")[document.getElementsByTagName("code")[0].getElementsByTagName("span").length - 1].remove() ; </script>';
        die();
    }

    public function getTodaysCompleteTransactionsWithDateRange($startDate,$endDate){
        date_default_timezone_set('Europe/Athens');
        $startDate = date($startDate);
        $endDate = date($endDate);
        $stmt ="SELECT profit,givenCoin,getCoin,id,dateTimeCreated,moonpayProfit,depositAmount,OurProfitEurStamp,isFiatTransaction,ourProfitFromMoonpay,feeCurrency FROM  sym_orders WHERE orderStateId = 4 AND dateTimeCreated >= :startDate AND dateTimeCreated <=:endDate";
        $this->sdb->query($stmt);
        $this->sdb->bind(':startDate',$startDate);
        $this->sdb->bind(':endDate',$endDate);
        $result = $this->sdb->resultset();
        if($result) {
            return $result;
        }
        return false;
    }

    public function show_fiat_coins(){
        $stmt = "SELECT * FROM  sym_coins WHERE currencyType = 'fiat'";
        $this->sdb->query($stmt);
        $products = $this->sdb->resultset();
        if ($products) {
            return $products;
        }
        return false;
    }

    public function gather_moonpay_provided_pairs(){
        $stmt ="SELECT * FROM sym_moonpay_provided_pairs WHERE active = 1 ";
        $this->sdb->query($stmt);
        $results = $this->sdb->resultset();
        if($results){
            return $results;
        }
        return false;
    }

    public function calculate_services_sum_time($activeServices){
        $services = new services();
        $activeServices = $activeServices ?? false;
        $activeServices = json_decode($activeServices);
        $sumTime = 0;
        if($activeServices){
            $countedSelectedServices = count($activeServices);
            foreach ($activeServices as $service) {
                $selectedServiceInfos = $services->show_services_by_id($service);
                if ($selectedServiceInfos) {
                    if($countedSelectedServices > 1){
                        $sumTime = $sumTime + $selectedServiceInfos['avExecution'];
                    }else{
                        $sumTime = $sumTime + $selectedServiceInfos['avExecutionStandAlone'];
                    }
                }
            }
        }
        return $sumTime;
    }

    function addMinutesToTimestamp($timestamp, $minutesToAdd) {
        $newTimestamp = $timestamp + ($minutesToAdd * 60);
        return $newTimestamp;
    }



    public function getRecourcesForSchedule($from, $to)
    {
        $barberId = BARBERID;
        $setting = $this->get_all();
        $preTo = '';
        // Setting date range
        if ($from === '' && $to === '' || $from === null && $to === null) {
            // If no dates are provided, generate a date range for the current week
            $startOfWeek = (new DateTime())->modify('Monday this week');
            $endOfWeek = (clone $startOfWeek)->modify('+6 days');
            $from = $startOfWeek->format('Y-m-d');
            $to = $endOfWeek->format('Y-m-d');
        }

        // givenDateRange
        if ($to === "daily" || $from === $to) {
            // If 'daily' is provided, generate a date range for the given day
            $startOfDay = (new DateTime($from))->setTime(0, 0);
            $endOfDay = (clone $startOfDay)->setTime(23, 59, 59);
            $from = $startOfDay->format('Y-m-d H:i:s');
            $to = $endOfDay->format('Y-m-d H:i:s');
            if ($to === "daily"){
                $preTo = 'daily';
                $preFrom = $startOfDay->format('Y-m-d');
            }
        }

        // Construct the query based on the setting and barberId
        $query = "SELECT * FROM sym_haircuts WHERE active = 1 AND dateTimeExecuted >= :fromRange AND dateTimeExecuted <=:toRange";
        if ($barberId !== null && $setting['showCMSCalendarFullBooks'] === '0') {
            $query .= " AND hairCutterId = $barberId";
        }
        $this->sdb->query($query);
        $this->sdb->bind(':fromRange',$from);
        $this->sdb->bind(':toRange',$to);
        $results = $this->sdb->resultset();
        foreach ($results as $resss =>$value3){
            $results[$resss]['newApp'] = false;
            $results[$resss]['clientNote'] = $results[$resss]['note'];
        }

        // Check for pending appointments in sym_sos_approval_list only if $to is 'daily'
        if ($preTo === 'daily' && $results) {
            $querySos = "SELECT * FROM sym_sos_approval_list WHERE onDate = :fromDate AND  active = 1 AND accepted = 0 AND declined = 0";
            if ($barberId !== null && $setting['showCMSCalendarFullBooks'] === '0') {
                $querySos .= " AND hairCutterId = ".(int)$barberId;
            }
            $this->sdb->query($querySos);
            $this->sdb->bind(':fromDate',$preFrom);
            $resultsSos = $this->sdb->resultset();
            foreach ($resultsSos as $ress =>$value2){
                $explodedSlot = explode(' - ',$resultsSos[$ress]['slot']);
                $resultsSos[$ress]['dateTimeExecuted'] = $resultsSos[$ress]['onDate'].' '.$explodedSlot[0].':00';
                $execTime = $this->getPriceAndTimeForServices(explode(', ',$resultsSos[$ress]['serviceId']));
                $resultsSos[$ress]['executionTime'] = $execTime['sumTime'];
                $resultsSos[$ress]['newApp'] = true;
            }
            // Combine results from both queries
            $results = array_merge($results, $resultsSos);
        }

        if (!$results) {
            return false;
        }
        return $this->processResults($results);
    }



    public function processResults($results)
    {
        $user = new User();
        $notifications = new notifications();
        $exported = array();
        $events = array();

        foreach ($results as $event) {
            $customerInfos = $user->show_customer_by_id($event['customerId']);
            $state = $notifications->check_notification_state('', $event['executionTime'], $event['dateTimeExecuted'], $event['appointmentAccepted'], $event['appointmentDeclined'], $event['commission'], $event['serviceId']);
            $pushable = $this->generatePushableEvent($event, $customerInfos, $state);
            array_push($events, $pushable);
        }

        $exported['events'] = $events;
        return $exported;
    }

    private function generatePushableEvent($event, $customerInfos, $state)
    {
        $services = new services();
        $pushable['id'] = $event['id'];
        $pushable['title'] = $this->generateEventTitle($event, $customerInfos, $state);
        $pushable['start'] = $event['dateTimeExecuted'];
        $pushable['end'] = $this->calculateEventEndTime($event);
        $pushable['allDay'] = false;
        $pushable['price'] = $event['commission'];

        $pushable['overlap'] = true;
        $pushable['rendering'] = 'background';
        if($event['newApp'] == true){
            $pushable['backgroundColor'] = '#17ff00';
            $pushable['borderColor'] = '#f5f5f557';
            $pushable['services'] = $event['serviceId'];
        }else{
            $pushable['backgroundColor'] = '#1d1d1d';
            $pushable['borderColor'] = '#f5f5f557';
            $pushable['services'] = $services->show_services_from_db_id_translate($event['serviceId']);
        }
        $pushable['newApp'] = $event['newApp'];
        $pushable['clientNote'] = $event['clientNote'] ?? '';
        return $pushable;
    }

    private function generateEventTitle($event, $customerInfos, $state)
    {
        if ($event['customerId'] == 0) {
            return strtoupper($customerInfos['name']) . ' ' . strtoupper($customerInfos['surname']);
        } else {
            return strtoupper($customerInfos['name']) . ' ' . strtoupper($customerInfos['surname']) . ' ' . $state['emoji'];
        }
    }

    private function calculateEventEndTime($event)
    {
        if ($event['executionTime'] == '0') {
            return date('Y-m-d H:i:s', $this->addMinutesToTimestamp(strtotime($event['dateTimeExecuted']), $this->calculate_services_sum_time($event['serviceId'])));
        } else {
            return date('Y-m-d H:i:s', $this->addMinutesToTimestamp(strtotime($event['dateTimeExecuted']), $event['executionTime']));
        }
    }

    public function getBusinessHours(){
        $stmt ="SELECT * FROM  sym_business_hours WHERE 1";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result) {
            return $result;
        }
        return false;
    }

    public function getSosHours($lang){
        $stmt ="SELECT id,active,endTime,startTime,name,nameEn FROM  sym_sos_business_hours WHERE 1";
        $this->sdb->query($stmt);
        $results = $this->sdb->resultset();
        if($results) {
            foreach ($results as $result =>$value){
                if($lang == 'en'){
                    $newName = $results[$result]['nameEn'];
                }else{
                    $newName = $results[$result]['name'];
                }
                $results[$result]['name'] = $newName;
                unset($results[$result]['nameEn']);
            }
            return $results;
        }
        return false;
    }

    public function save_business_hours($form){
        parse_str(htmlspecialchars_decode($form), $formParameters);
        $businessHours = $this->getBusinessHours();
        foreach ($businessHours as $hour =>$value){
            $active = 0;
            if($formParameters[strtolower($businessHours[$hour]['name']).'Checkbox'] == 'on'){
                $active = 1;
            }
            if($active == 1 && $businessHours[$hour]['active'] !== 1 || $active == 0 && $businessHours[$hour]['active'] !== 0){
                $this->update_business_infos($formParameters[strtolower($businessHours[$hour]['name']).'End'],$formParameters[strtolower($businessHours[$hour]['name']).'Start'],$active,$businessHours[$hour]['id']);
            }
        }
        return $formParameters;
    }

    public function update_business_infos($endTime,$startTime,$active,$id){
       date_default_timezone_set('Europe/Athens');
       $dateTimeUpdated = date('Y-m-d H:i:s');
       $stmt = "UPDATE sym_business_hours SET startTime= :startTime, endTime=:endTime,active = :active, dateTimeUpdated=:dateTimeUpdated WHERE id=:id";
       $this->sdb->query($stmt);
       $this->sdb->bind(':dateTimeUpdated', $dateTimeUpdated);
       $this->sdb->bind(':endTime', $endTime);
       $this->sdb->bind(':startTime', $startTime);
       $this->sdb->bind(':active', $active);
       $this->sdb->bind(':id', $id);
       $update = $this->sdb->execute();
       if ($update) {
           return true;
       }
       $this->error = "Error saving settings";
       return false;
   }

    public function getPriceAndTimeForServices($selectedServices){
        $services = new services();
        $sumPrice = 0;
        $sumTime = 0;
        $countedSelectedServices = count($selectedServices);
        foreach ($selectedServices as $service) {
            $selectedServiceInfos = $services->show_service_by_name($service);
            if ($selectedServiceInfos) {
                if($countedSelectedServices > 1){
                    $sumPrice = $sumPrice + $selectedServiceInfos['price'];
                    $sumTime = $sumTime + $selectedServiceInfos['avExecution'];
                }else{
                    $sumPrice = $sumPrice + $selectedServiceInfos['priceStandAlone'];
                    $sumTime = $sumTime + $selectedServiceInfos['avExecutionStandAlone'];
                }
            }
        }
        $response['sumPrice'] = $sumPrice;
        $response['sumTime'] = $sumTime;
        return $response;
    }

    function greek_to_english($text) {
        $greek = array('α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω', 'χ');
        $english = array('a', 'b', 'g', 'd', 'e', 'z', 'h', '8', 'i', 'k', 'l', 'm', 'n', 'ks', 'o', 'p', 'r', 's', 't', 'u', 'f', 'x', 'y', 'w');
        $hasEnglish = false;
        for ($i = 0; $i < mb_strlen($text); $i++) {
            $char = mb_substr($text, $i, 1);
            if (preg_match('/[a-zA-Z]/', $char)) {
                $hasEnglish = true;
                break;
            }
        }
        if (is_numeric($text)) {
            return $text;
        } else if ($hasEnglish) {
            return $text;
        } else {
            return str_replace($greek, $english, strtolower($text));
        }
    }

    function greeklish_to_greek($text) {
        $greeklish = array('a', 'b', 'g', 'd', 'e', 'z', 'h', '8', 'i', 'k', 'l', 'm', 'n', 'ks', 'o', 'p', 'r', 's', 't', 'u', 'f', 'x', 'y', 'w');
        $greek = array('α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω');
        $hasGreek = false;
        for ($i = 0; $i < mb_strlen($text); $i++) {
            $char = mb_substr($text, $i, 1);
            if (preg_match('/\p{Greek}/u', $char)) {
                $hasGreek = true;
                break;
            }
        }
        if (is_numeric($text)) {
            return $text;
        }else if ($hasGreek) {
            return $text;
        } else {
            return str_replace($greeklish, $greek, strtolower($text));
        }
    }

    public function general_search($query,$type){
        $services = new services();
        $query = trim($query);
        $queryGreeklish = $this->greeklish_to_greek($query);
        $queryGreektoEng = $this->greek_to_english($query);
        $results1 = [];
        $results2 = [];
        if($type === 'haircuts' || $type === 'general'){ //haircuts
            $stmt = "SELECT hc.id, hc.customerId, hc.serviceId, hc.hairCutterId, hc.dateTimeExecuted,
                h.name AS hairCutterName, 
                c.name AS customerFirstName, c.surname AS customerLastName, c.phone AS customerPhoneNumber 
         FROM sym_haircuts hc 
         JOIN sym_hair_cutters h ON hc.hairCutterId = h.id 
         JOIN sym_customers c ON hc.customerId = c.id 
         WHERE (hc.id LIKE '%$query%' OR hc.id LIKE '%$queryGreeklish%' OR hc.id LIKE '%$queryGreektoEng%') AND hc.active = 1
            OR (h.name LIKE '%$query%' OR h.name LIKE '%$queryGreeklish%' OR h.name LIKE '%$queryGreektoEng%') AND hc.active = 1
            OR (c.name LIKE '%$query%' OR c.name LIKE '%$queryGreeklish%' OR c.name LIKE '%$queryGreektoEng%') AND hc.active = 1
            OR (c.surname LIKE '%$query%' OR c.surname LIKE '%$queryGreeklish%' OR c.surname LIKE '%$queryGreektoEng%') AND hc.active = 1
            OR (c.phone LIKE '%$query%' OR c.phone LIKE '%$queryGreeklish%' OR c.phone LIKE '%$queryGreektoEng%') AND hc.active = 1
         LIMIT 50";
            $this->sdb->query($stmt);
            $results1 = $this->sdb->resultset();
            if (count($results1) > 0) {
                foreach ($results1 as $result1 =>$value) {
                    $results1[$result1]['type'] = 'haircuts';
                    $decodedServices = json_decode($results1[$result1]['serviceId']);
                    foreach ($decodedServices as $serv =>$value2){
                        $serviceInfos = $services->show_services_by_id($decodedServices[$serv]);
                        $results1[$result1]['services'][$serv] = $serviceInfos['name'];
                    }
                    $results1[$result1]['services']['length'] = count($decodedServices);
                }
            }
        }
        if($type === 'clients' || $type === 'general'){
            $stmt = "SELECT c.id, c.name, c.surname, c.phone, c.email
         FROM sym_customers c
         WHERE 
         (CONCAT(c.name, ' ', c.surname)  LIKE '%$query%' OR CONCAT(c.name, ' ', c.surname)  LIKE '%$queryGreeklish%' OR CONCAT(c.name, ' ', c.surname)  LIKE '%$queryGreektoEng%')  AND c.active = 1
         OR (c.phone LIKE '%$query%' OR c.phone LIKE '%$queryGreeklish%' OR c.phone LIKE '%$queryGreektoEng%') AND c.active = 1
         LIMIT 50";
            $this->sdb->query($stmt);
            $results2 = $this->sdb->resultset();
            if (count($results2) > 0) {
                foreach ($results2 as $result2 =>$value) {
                    $results2[$result2]['type'] = 'clients';
                }
            }
        }
        //todo: services,e-shop,barbers
        $response = array_merge($results2,$results1);
        if($response){
            return $response;
        }
        return false;
    }

    public function add_discount_rule($data) {
        $discountMode = $data['discountMode'];
        $clientType = $data['clientType'];
        $discountType = $data['discountType'];
        $discountValue = $data['discountValue'];
        $rangeCategory = $data['rangeCategory'];
        $rangeType = $data['rangeType'];
        // You will need to adjust the range type values based on the selected range type
        if ($rangeCategory == "price") {
            $rangeCategoryValues = array(
                'from' => $data['priceRangeFrom'],
                'to' => $data['priceRangeTo']
            );
        } else if ($rangeCategory == "time") {
            $rangeCategoryValues = array(
                'to' => $data['timeRangeTo']
            );
        } else if ($rangeCategory == "appointment") {
            $rangeCategoryValues = array(
                'from' => $data['appointmentRangeFrom'],
                'to' => $data['appointmentRangeTo']
            );
        } else if ($rangeCategory == "combined") {
            $rangeCategoryValues = array(
                'price' => array(
                    'from' => $data['priceRangeFrom'],
                    'to' => $data['priceRangeTo']
                ),
                'time' => array(
                    'from' => $data['timeRangeFrom'],
                    'to' => $data['timeRangeTo']
                ),
                'appointment' => array(
                    'from' => $data['appointmentRangeFrom'],
                    'to' => $data['appointmentRangeTo']
                )
            );
        }
        $dateTimeCreated = date("Y-m-d H:i:s");
        // Insert the data into the database
        $stmt = "INSERT INTO sym_discounts_rules (discountMode, clientType, discountType, discountValue, rangeCategory, rangeCategoryValues, dateTimeCreated,rangeType) VALUES (:discountMode, :clientType, :discountType, :discountValue, :rangeCategory, :rangeCategoryValues, :dateTimeCreated,:rangeType)";
        $this->sdb->query($stmt);
        $this->sdb->bind(":discountMode", $discountMode);
        $this->sdb->bind(":clientType", $clientType);
        $this->sdb->bind(":discountType", $discountType);
        $this->sdb->bind(":discountValue", $discountValue);
        $this->sdb->bind(":rangeCategory", $rangeCategory);
        $this->sdb->bind(":rangeCategoryValues", json_encode($rangeCategoryValues));
        $this->sdb->bind(":dateTimeCreated", $dateTimeCreated);
        $this->sdb->bind(":rangeType", $rangeType);
        $add = $this->sdb->execute();
        if ($add) {
            $this->msg = 'Data inserted successfully!';
            return true;
        } else {
            $this->error = "Error inserting data!";
            return false;
        }
    }

    public function update_discount_rule($data){
        $id = $data['id'];
        $discountMode = $data['discountMode'];
        $clientType = $data['clientType'];
        $discountType = $data['discountType'];
        $discountValue = $data['discountValue'];
        $rangeCategory = $data['rangeCategory'];
        $rangeType = $data['rangeType'];
        if($rangeType == 'step'){
            $stepCategory = $data['stepCategory'];
            if ($stepCategory == "price") {
                $rangeCategoryValues = json_encode(array("priceStepFrom" => $data['priceStepFrom']));
            } else if ($stepCategory == "time") {
                $rangeCategoryValues = json_encode(array("timeStepFrom" => $data['timeStepFrom']));
            } else if ($stepCategory == "appointment") {
                $rangeCategoryValues = json_encode(array("appointmentStepFrom" => $data['appointmentStepFrom']));
            } else if ($stepCategory == "combined") {
                $rangeCategoryValues = json_encode(array("priceStepFrom" => $data['priceRangeFrom'], "timeRangeFrom" => $data['timeRangeFrom'], "appointmentRangeFrom" => $data['appointmentRangeFrom']));
            }
        }else{
            // You will need to adjust the range type values based on the selected range type
            if ($rangeCategory == "price") {
                $rangeCategoryValues = json_encode(array("priceRangeFrom" => $data['priceRangeFrom'], "priceRangeTo" => $data['priceRangeTo']));
            } else if ($rangeCategory == "time") {
                $rangeCategoryValues = json_encode(array("timeRangeFrom" => $data['timeRangeFrom'], "timeRangeTo" => $data['timeRangeTo']));
            } else if ($rangeCategory == "appointment") {
                $rangeCategoryValues = json_encode(array("appointmentRangeFrom" => $data['appointmentRangeFrom'], "appointmentRangeTo" => $data['appointmentRangeTo']));
            } else if ($rangeCategory == "combined") {
                $rangeCategoryValues = json_encode(array("priceRangeFrom" => $data['priceRangeFrom'], "priceRangeTo" => $data['priceRangeTo'], "timeRangeFrom" => $data['timeRangeFrom'], "timeRangeTo" => $data['timeRangeTo'], "appointmentRangeFrom" => $data['appointmentRangeFrom'], "appointmentRangeTo" => $data['appointmentRangeTo']));
            }
        }
        $dateTimeUpdated = date("Y-m-d H:i:s");
        // Update the data in the database
        $stmt = "UPDATE sym_discounts_rules SET discountMode=:discountMode, clientType=:clientType, discountType=:discountType, discountValue=:discountValue, rangeCategory=:rangeCategory, rangeCategoryValues=:rangeCategoryValues, dateTimeUpdated=:dateTimeUpdated, rangeType=:rangeType WHERE id=:id";
        $this->sdb->query($stmt);
        $this->sdb->bind(":discountMode", $discountMode);
        $this->sdb->bind(":clientType", $clientType);
        $this->sdb->bind(":discountType", $discountType);
        $this->sdb->bind(":discountValue", $discountValue);
        $this->sdb->bind(":rangeCategory", $rangeCategory);
        $this->sdb->bind(":rangeCategoryValues", $rangeCategoryValues);
        $this->sdb->bind(":dateTimeUpdated", $dateTimeUpdated);
        $this->sdb->bind(":rangeType", $rangeType);
        $this->sdb->bind(":id", $id);
        $update = $this->sdb->execute();
        if ($update) {
            $this->msg = 'Data inserted successfully!';
            return true;
        } else {
            $this->error = "Error inserting data!";
            return false;
        }
    }

    public function count_annual_orders(){
        $stmt = "SELECT id FROM  sym_haircuts WHERE customerId > 0 AND active = 1 AND executionTime > 0 and commission > 0";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        $result2 = $this->sdb->rowCount();
        return $result2;
    }

    public function gather_stats_for_graph_ZTD($monthsBefore){
        date_default_timezone_set('Europe/Athens');
        $haircuts = new haircuts();
        $sumAnualOrders = $this->count_annual_orders();
        $nowMonth = date('m');
        $nowYear = date('Y');
        $time1 = strtotime($nowYear.'-'.$nowMonth.'-'.'01');
        $dateMonthsBefore = date("Y-m-d", strtotime("-".$monthsBefore." months",$time1));
        $time = strtotime($dateMonthsBefore);
        $oneMonthFromDate =  date("Y-m-d", strtotime("+1 month", $time));
        $completedOrders = $this->getCachedHaircuts($dateMonthsBefore, $oneMonthFromDate);
        $DateTime = new DateTime();
        $monthNumb = date("m", strtotime("-".$monthsBefore." months",$time1));
        $monthNumb2 = date("M", strtotime("-".$monthsBefore." months",$time1));
        $fullDate = date("m-Y", strtotime("-".$monthsBefore." months",$time1));
        $this->empty_stats_now_month_to_renew($fullDate);
        $year = date("Y", strtotime("-".$monthsBefore." months",$time1));
        $todayDay = date('d');
        $monthDays = cal_days_in_month(CAL_GREGORIAN, $monthNumb, $year);
        $OrdersByDays = array();
        $OrdersByDays['SumCompletedMonthlyOrders'] = 0;
        $OrdersByDays['currentMonth'] = $monthNumb2;
        $OrdersByDays['fullDate'] = $fullDate;
        $OrdersByDays['SumAnnualOrders'] = $sumAnualOrders;
        if ($monthsBefore == 0){
            $OrdersByDays['sumdays'] = $todayDay;
        }else{
            $OrdersByDays['sumdays'] = (string)$monthDays;
        }
        $checkIfThisGraphExists = $this->check_data_if_in_base($fullDate);
        ///  if($checkIfThisGraphExists['response'] == 'nothing'){return 'Stats Already Saved For This Date';}
        $OrdersByDays['importType'] = $checkIfThisGraphExists['response'];
        $OrdersByDays['leftToImportDays'] = $checkIfThisGraphExists['daysLeft'];
        $OrdersByDays['prevSumProfit'] = $checkIfThisGraphExists['prevSumProfit'];
        $OrdersByDays['foundId'] = $checkIfThisGraphExists['foundId'];
        $OrdersByDays['SumMonthOrders'] = count($completedOrders);
        foreach ($completedOrders as $ord =>$value){
            $OrdersByDays['SumEuroProfit'] =  $OrdersByDays['SumEuroProfit'] + $completedOrders[$ord]['commission'];
        }
        $TOrdersByDays = array();
        for ($i=1;$i<=$monthDays;$i++){
            $date = new DateTime($year.'-'.$monthNumb.'-'.$i);
            if($date < $DateTime) {
                $TOrdersByDays[$i]['sumOrders'] = 0;
                $TOrdersByDays[$i]['sumTime'] = 0;
                $TOrdersByDays[$i]['year'] = $year;
                $TOrdersByDays[$i]['month'] = (int)$monthNumb;
                $TOrdersByDays[$i]['day'] = $i;
                $TOrdersByDays[$i]['sumEurProfit'] = 0;
            }
        }
        for ($i=1;$i<=$monthDays;$i++){
            foreach ($completedOrders as $swap => $value){
                $dateTimeFromDb = $completedOrders[$swap]['dateTimeCreated'];
                $explodedDate = explode(' ',$dateTimeFromDb);
                $explodedMonth = explode('-',$explodedDate[0]);
                $explodedDay = $explodedMonth[2];
                if ($i == (int)$explodedDay){
                    if ($completedOrders[$swap]['commission'] > 0 && $completedOrders[$swap]['executionTime'] != '0'){
                        $TOrdersByDays[$i]['sumHaircuts']++;
                        $TOrdersByDays[$i]['sumTime']++;
                    }
                }
            }
        }
        $FindMaxOrdersOnDay = $TOrdersByDays;
        $FindMaxCompletedOrdersOnDay = $TOrdersByDays;
        $FindMaxOrdersOnDay = array_reduce($FindMaxOrdersOnDay, function ($a, $b) {
            return @$a['sumHaircuts'] > $b['sumHaircuts'] ? $a : $b ;
        });
        $FindMaxCompletedOrdersOnDay = array_reduce($FindMaxCompletedOrdersOnDay, function ($a, $b) {
            return @$a['sumTime'] > $b['sumTime'] ? $a : $b ;
        });
        $OrdersByDays['maxOrdersNumOnADay'] = $FindMaxOrdersOnDay['sumHaircuts'];
        $OrdersByDays['maxCompletedOrdersNumOnADay'] = $FindMaxCompletedOrdersOnDay['sumTime'];
        for ($i=1;$i<=$monthDays;$i++){
            $OrdersByDays['days'][$i]['sumHaircuts'] = 0;
            $OrdersByDays['days'][$i]['sumTime'] = 0;
            $OrdersByDays['days'][$i]['year'] = $year;
            $OrdersByDays['days'][$i]['month'] = (int)$monthNumb;
            $OrdersByDays['days'][$i]['day'] = $i;
            $OrdersByDays['days'][$i]['sumEurProfit'] = 0;
        }
        for ($i=1;$i<=$monthDays;$i++){
            foreach ($completedOrders as $swap => $value){
                $dateTimeFromDb = $completedOrders[$swap]['dateTimeExecuted'];
                $explodedDate = explode(' ',$dateTimeFromDb);
                $explodedMonth = explode('-',$explodedDate[0]);
                $explodedDay = $explodedMonth[2];
                if ($i == (int)$explodedDay){
                    if ($completedOrders[$swap]['commission'] != '0'){
                        $OrdersByDays['days'][$i]['sumHaircuts']++;
                        $OrdersByDays['days'][$i]['sumTime'] = (int)$OrdersByDays['days'][$i]['sumTime'] + (int)$completedOrders[$swap]['executionTime'];
                        $OrdersByDays['sumHaircuts']++;
                        $OrdersByDays['sumTime'] = $OrdersByDays['sumTime'] + $completedOrders[$swap]['executionTime'];
                        $commission = (float) $completedOrders[$swap]['commission'];
                        $sumEurProfit = (float) $OrdersByDays['days'][$i]['sumEurProfit'];
                        $discountPercentage = (float) $completedOrders[$swap]['discountPercentage'];
                        $discountedCommission = $commission - ($commission * $discountPercentage / 100);
                        $OrdersByDays['days'][$i]['sumEurProfit'] = $sumEurProfit + $discountedCommission;
                    }
                }
            }
            //  $OrdersByDays['days'][$i]['sumEurProfit'] = number_format($OrdersByDays['days'][$i]['sumEurProfit'],'2','.','');
        }
        array_reduce($FindMaxOrdersOnDay, function ($a, $b) {
            return (is_array($a) && array_key_exists('sumHaircuts', $a) && $a['sumHaircuts'] > $b['sumHaircuts']) ? $a : $b;
        }, array());

        array_reduce($FindMaxCompletedOrdersOnDay, function ($a, $b) {
            return (is_array($a) && array_key_exists('sumTime', $a) && $a['sumTime'] > $b['sumTime']) ? $a : $b;
        }, array());
        $this->insert_stats_data_to_base($OrdersByDays);
        return $OrdersByDays;
    }

    public function insert_stats_data_to_base($dato){
        //  if($dato['SumEuroProfit'] && $dato['importType'] !='nothing'){
        $successTxCount = $dato['sumHaircuts'] ?? 1;
        $timeCount = $dato['sumTime'] ?? 1;
        $date = $dato['fullDate'];
        $dato['SumEuroProfit'] = $dato['SumEuroProfit'] ?? 1;
        $dayAnalisysExport = array();
        foreach ($dato['days'] as $monthDay =>$value){ // "date":"1-1-2023","sumHaircuts":75,"sumTime":100, "sumEur": 100
            $dayDate = $dato['days'][$monthDay]['day'].'-'.$dato['days'][$monthDay]['month'].'-'.$dato['days'][$monthDay]['year'];
            $dayAnalisysExportAnl = array(
                'date'=>$dayDate,
                'sumHaircuts'=>$dato['days'][$monthDay]['sumHaircuts'],
                'sumTime'=>$dato['days'][$monthDay]['sumTime'],
                'sumEur'=>$dato['days'][$monthDay]['sumEurProfit']);
            array_push($dayAnalisysExport,$dayAnalisysExportAnl);
        }
        $dayAnalysis = json_encode($dayAnalisysExport);
        if($dato['importType'] == 'insert'){
            $stmt = "INSERT INTO sym_profit_stats (`month`,`monthlyTimeCount`,`monthlyAppointmentsCount`,`dayAnalysis`,`sumEurProfit`) VALUES (:date,:monthlyTimeCount,:monthlyAppointmentsCount,:dayAnalysis,:SumEuroProfit)";
            $this->sdb->query($stmt);
            $this->sdb->bind(':date',$date);
            $this->sdb->bind(':monthlyTimeCount',$timeCount);
            $this->sdb->bind(':monthlyAppointmentsCount',$successTxCount);
            $this->sdb->bind(':dayAnalysis',$dayAnalysis);
            $this->sdb->bind(':SumEuroProfit',$dato['SumEuroProfit']);
            $resp = $this->sdb->execute();
        }
        if($dato['importType'] == 'update'){
            $stmt = "UPDATE sym_profit_stats SET monthlyTimeCount =:monthlyTimeCount, monthlyAppointmentsCount =:monthlyAppointmentsCount, dayAnalysis=:dayAnalysis, sumEurProfit=:SumEuroProfit WHERE id=:id";
            $this->sdb->query($stmt);
            $this->sdb->bind(':id',$dato['foundId']);
            $this->sdb->bind(':monthlyTimeCount',$timeCount);
            $this->sdb->bind(':monthlyAppointmentsCount',$successTxCount);
            $this->sdb->bind(':dayAnalysis',$dayAnalysis);
            //  $this->sdb->bind(':SumEuroProfit',($dato['SumEuroProfit'] + $dato['prevSumProfit']));
            $this->sdb->bind(':SumEuroProfit',$dato['SumEuroProfit']);
            $this->sdb->execute();
            return true;
        }
        if($resp){
            return true;
        }
        /* }else{
             return 'No Profit Gather';
         }*/
        return 'false4';
    }

    public function check_data_if_in_base($date){
        $stmt ="SELECT * FROM  sym_profit_stats WHERE month=:dates";
        $this->sdb->query($stmt);
        $this->sdb->bind(':dates',$date);
        $results = $this->sdb->single();
        if($results ){
            $date = explode('-',$date);
            $daysNumberOfMonth = cal_days_in_month(CAL_GREGORIAN, intval($date[0]), intval($date[1]));
            $decodedDays = json_decode($results['dayAnalysis']);
            foreach ($decodedDays as $day=>$value){
                if($decodedDays[$day]->sumTxs == 0){
                    unset($decodedDays[$day]);
                }
            }
            if(count($decodedDays) != $daysNumberOfMonth){
                $calculateDays = ($daysNumberOfMonth - ($daysNumberOfMonth - count($decodedDays))) + 1;
                $meter = 0;
                for ($i=$calculateDays;$i<=$daysNumberOfMonth;$i++){
                    $export['daysLeft'][$meter] = $i.'-'.$date[0].'-'.$date[1];
                    $meter++;
                }
                $export['foundId'] = $results['id'];
                $export['prevSumProfit'] = $results['sumEurProfit'];
                $export['response'] = 'update';
                return $export;
            }else{
                $export['response'] = 'nothing';
                return $export;
            }
        }
        $export['response'] = 'insert';
        return $export;
    }

    public function empty_stats_now_month_to_renew($fullDate){
        date_default_timezone_set('Europe/Athens');
        $nowTimestamp = date('d');
        $MonthRenewMinutesInterval = array('2','2','2','2','3','3','3','4','4','4','5','5','5','6','6','6','10','10','10','10','15','15','15','15','30','30','30','30','50','50','50','50');
        $selectedInterval = $MonthRenewMinutesInterval[$nowTimestamp];
        // if(count($completedOrders) > 1000 && $nowTimestamp < 10){ $selectedInterval = '120'; }
        $timeInterval = date('i');
        $stamp = 1;
        $preferedIntval = array();
        for ($i=0;$i<=60;$i++){
            if($i == $selectedInterval){$stamp++;array_push($preferedIntval,$i);}
            if($i == $selectedInterval * $stamp){$stamp++;array_push($preferedIntval,$i);}
        }
        $searchedRunStamp = array_search($timeInterval,$preferedIntval);
        $run = false;
        if(!is_bool($searchedRunStamp)){ $run = true; }
        if($run){
            $stmt = "DELETE FROM sym_profit_stats WHERE month = :month";
            $this->sdb->query($stmt);
            $this->sdb->bind(':month', $fullDate);
            $update = $this->sdb->execute();
            if ($update) {
                return true;
            }
            return false;
        }
        return false;
    }

    private $cachedHaircuts = null;

    public function getCachedHaircuts($fromDate, $toDate) {
        // If cached data is null, fetch from DB and store it.
        if($this->cachedHaircuts === null) {
            $haircuts = new haircuts();
            $this->cachedHaircuts = $haircuts->haircutsByDateRange($fromDate, $toDate);
        }
        return $this->cachedHaircuts;
    }



    public function accept_sos($id,$message){
        $stmt = "UPDATE sym_sos_approval_list  SET `accepted`= 1, `serviceNote`= :message WHERE `id` =:id";
        $this->sdb->query($stmt);
        $this->sdb->bind(":id", $id);
        $this->sdb->bind(":message", $message);
        $update = $this->sdb->execute();
        if ($update) {
            return true;
        }
        return false;
    }

    public function decline_sos($id,$message){
        $stmt = "UPDATE sym_sos_approval_list  SET `declined`= 1, `serviceNote`= :message WHERE `id` =:id";
        $this->sdb->query($stmt);
        $this->sdb->bind(":id", $id);
        $this->sdb->bind(":message", $message);
        $update = $this->sdb->execute();
        if ($update) {
            return true;
        }
        return false;
    }


}

?>