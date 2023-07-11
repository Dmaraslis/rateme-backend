<?php
class haircuts{
	var $error = '';
	var $msg = '';
	var $key = 'SYMBIOTIC';
	public $sdb;
	
	public function __construct(){
	global $db;
	 $this->sdb = $db;
	}

    public function count_haircuts(){
        $stmt = "SELECT * FROM  sym_haircuts WHERE active = 1 AND customerId > 0 AND executionTime > 0 and commission > 0";
        $this->sdb->query($stmt);
        $this->sdb->resultset();
        $result = $this->sdb->rowCount();
        if($result){
            return $result;
        }
        return false;
    }

    public function show_haircuts(){
        $stmt = "SELECT * FROM  sym_haircuts WHERE active = 1";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result){
            return $result;
        }
        return false;
    }

    public function show_haircut_by_id($hairCutId){
        $stmt = "SELECT * FROM  sym_haircuts WHERE active = 1 AND id=:hairCutId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":hairCutId", $hairCutId);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }

    public function get_appointments_current_month() {
        $startDate = (new DateTime())->modify('first day of this month')->format('Y-m-d');
        $endDate = (new DateTime())->format('Y-m-d');

        $stmt = "SELECT * FROM sym_haircuts WHERE active = 1 AND DATE(dateTimeExecuted) BETWEEN :startDate AND :endDate";
        $this->sdb->query($stmt);
        $this->sdb->bind(":startDate", $startDate);
        $this->sdb->bind(":endDate", $endDate);
        $result = $this->sdb->resultSet();

        if ($result) {
            return $result;
        }
        return false;
    }



    public function show_haircut_by_id_fix_exec_time($hairCutId){
	    $settings = new Settings();
        $stmt = "SELECT * FROM  sym_haircuts WHERE active = 1 AND id=:hairCutId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":hairCutId", $hairCutId);
        $result = $this->sdb->single();
        if($result){
            if($result['executionTime'] == 0){
                $result['executionTimeCalculated'] = $settings->calculate_services_sum_time($result['serviceId']);
            }else{
                $result['executionTimeCalculated'] = 0;
            }
            return $result;
        }
        return false;
    }

    public function gather_haircuts_pagination($limit,$offset,$previewType){
	    $user = new User();
	    $services = new services();
	    $barbers = new barbers();
	    $notifications = new notifications();
	    if($previewType == 'reservedHours'){
	        $query = 'AND customerId = 0';
        }
	    if($previewType == 'appointments'){
            $query = 'AND customerId > 0';
        }
        $stmt ="SELECT * FROM sym_haircuts WHERE active = 1 $query ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $this->sdb->query($stmt);
        $results = $this->sdb->resultset();
        if($results ){
            foreach ($results as $res =>$value){
                $pusable = array();
                foreach (json_decode($results[$res]['serviceId']) as $service){
                    array_push($pusable,$services->show_services_by_id($service));
                }
                $servicesInfos = $notifications->check_notification_state('',$results[$res]['executionTime'],$results[$res]['dateTimeExecuted'],$results[$res]['appointmentAccepted'],$results[$res]['appointmentDeclined'],$results[$res]['commission'],$results[$res]['serviceId']);
                $results[$res]['state']['name'] = $servicesInfos['name'];
                $results[$res]['state']['icon'] = $servicesInfos['icon'];
                $results[$res]['clientInfos'] = $user->show_customer_by_id($results[$res]['customerId']);
                $results[$res]['servicesInfos'] = $pusable;
                $results[$res]['barberInfos'] = $barbers->show_barbers_by_id($results[$res]['hairCutterId']);
                $results[$res]['dateTimeExecuted'] = date('d D-M h:i',strtotime($results[$res]['dateTimeExecuted']));

            }
            return $results;
        }
        return false;
    }

    public function get_most_frequent_customer_id($minEntries = 3) {
        $customer = new User();
        $currentDate = date('Y-m-d H:i:s');
        $stmt = "SELECT customerId, discountPercentage, id, COUNT(*) as count 
             FROM sym_haircuts 
             WHERE (active = 1 AND customerId > 0 AND executionTime > 0 AND dateTimeExecuted < :currentDate) OR (active = 1 AND customerId > 0 AND executionTime = 0 AND dateTimeExecuted >= :currentDate)
             GROUP BY customerId 
             HAVING count >= :minEntries 
             ORDER BY count DESC LIMIT 50";
        $this->sdb->query($stmt);
        $this->sdb->bind(':minEntries', $minEntries);
        $this->sdb->bind(':currentDate', $currentDate);
        $result = $this->sdb->resultset();

        $finalResult = [
            'fourthAppointment' => [],
            'otherAppointments' => []
        ];

        if ($result) {
            foreach ($result as $res => $value) {
                $customerData = [
                    'customerId' => $result[$res]['customerId'],
                    'customerInfos' => $customer->show_customer_by_id($result[$res]['customerId']),
                    'nextAppointment' => $this->get_next_appointment_for_customer($result[$res]['customerId']),
                    'appointmentCount' => $result[$res]['count']
                ];

                if ($result[$res]['count'] % 4 == 0) {
                    $finalResult['fourthAppointment'][] = $customerData;
                } elseif ($result[$res]['count'] % 4 == 3) {
                    $finalResult['otherAppointments'][] = $customerData;
                }
            }
        }

        // Sort both lists by 'nextAppointment' not being false
        usort($finalResult['fourthAppointment'], function($a, $b) {
            return ($a['nextAppointment'] === false) <=> ($b['nextAppointment'] === false);
        });

        usort($finalResult['otherAppointments'], function($a, $b) {
            return ($a['nextAppointment'] === false) <=> ($b['nextAppointment'] === false);
        });

        return $finalResult;
    }








    public function get_next_appointment_for_customer($customerId) {
        $currentDateTime = date('Y-m-d H:i:s');
        $stmt = "SELECT * 
             FROM sym_haircuts 
             WHERE customerId = :customerId AND dateTimeExecuted > :currentDateTime 
             AND active = 1 
             ORDER BY dateTimeExecuted ASC 
             LIMIT 1";
        $this->sdb->query($stmt);
        $this->sdb->bind(':customerId', $customerId);
        $this->sdb->bind(':currentDateTime', $currentDateTime);
        $result = $this->sdb->single();
        if ($result) {
            return $result;
        }

        return false;
    }



    public function add_haircut($serviceId,$note,$haircutPrice,$execTime,$haircutDiscount,$clientId,$barberId,$appointment){
	    //edw prepei na ftiaksw ena check gia to an einai available o pelatis kai o barber ekinh tin wra
        $notifications = new notifications();
        $services = new services();
        $settings = new Settings();
        if(empty($haircutPrice)){ $haircutPrice = 0; }
        if(empty($execTime)){ $execTime = 0; }
        $stmt = "INSERT INTO sym_haircuts (`serviceId`, `note`, `commission`,`executionTime`,`discountPercentage`,`customerId`,`hairCutterId`,`dateTimeExecuted`) VALUES (:serviceId, :note,:haircutPrice,:execTime,:haircutDiscount,:clientId,:barberId,:appointment)";
        $this->sdb->query($stmt);
        $this->sdb->bind(":serviceId", $serviceId);
        $this->sdb->bind(":note", $note);
        $this->sdb->bind(":haircutPrice", $haircutPrice);
        $this->sdb->bind(":execTime", $execTime);
        $this->sdb->bind(":clientId", $clientId);
        $this->sdb->bind(":barberId", $barberId);
        $this->sdb->bind(":appointment", $appointment);
        $this->sdb->bind(":haircutDiscount", $haircutDiscount);
        $add = $this->sdb->execute();
        $haircutId = $this->sdb->lastInsertId();
        if ($add) {
            $serviceInfos = $services->show_services_by_haircut_id($haircutId);
            $constructedAppointmentType = '';
            $sumServices = array();
            foreach ($serviceInfos as $service =>$value){
                $constructedAppointmentType .= $serviceInfos[$service]['name'].' ';
                array_push($sumServices,$serviceInfos[$service]['name']);
            }
            $servicesInfosExtra = $settings->getPriceAndTimeForServices($sumServices);
            $executionTime = 'PT'.$servicesInfosExtra['sumTime'].'M';
            $date = new DateTime($appointment);
            $interval = new DateInterval($executionTime);
            $date->add($interval);
            $newTime = $date->getTimestamp();
            $startTime = strtotime($appointment).'000';
            $endTime = $newTime.'000';
            $startDate = new DateTime($appointment);
            $endDate = new DateTime($appointment);
            $startTimeText = $startDate->format('Y-m-d H:i:s');
            $endDate->add($interval);
            $endDateText = $endDate->format('Y-m-d H:i:s');
            $notifications->push_appointment($haircutId,$constructedAppointmentType,$barberId,$clientId,$startTime,$endTime,$startTimeText,$endDateText,'created');
            return true;
        }
        return false;
    }

    public function update_haircut($presetId,$serviceId,$note,$haircutPrice,$execTime,$haircutDiscount,$clientId,$barberId,$appointment){
        //edw prepei na ftiaksw ena check gia to an einai available o pelatis kai o barber ekinh tin wra
        $notifications = new notifications();
        $services = new services();
        $settings = new Settings();
        if(empty($haircutPrice)){ $haircutPrice = 0; }
        if(empty($execTime)){ $execTime = 0; }
        $stmt = "UPDATE sym_haircuts  SET `serviceId`=:serviceId, note =:note, commission=:haircutPrice, executionTime=:execTime, discountPercentage=:haircutDiscount, customerId=:clientId, hairCutterId=:barberId, dateTimeExecuted=:appointment WHERE id =:presetId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":serviceId", $serviceId);
        $this->sdb->bind(":note", $note);
        $this->sdb->bind(":haircutPrice", $haircutPrice);
        $this->sdb->bind(":execTime", $execTime);
        $this->sdb->bind(":clientId", $clientId);
        $this->sdb->bind(":barberId", $barberId);
        $this->sdb->bind(":appointment", $appointment);
        $this->sdb->bind(":presetId", $presetId);
        $this->sdb->bind(":haircutDiscount", $haircutDiscount);
        $update = $this->sdb->execute();
        if ($update) {
            $serviceInfos = $services->show_services_by_haircut_id($presetId);
            $constructedAppointmentType = '';
            $sumServices = array();
            foreach ($serviceInfos as $service =>$value){
                $constructedAppointmentType .= $serviceInfos[$service]['name'].' ';
                array_push($sumServices,$serviceInfos[$service]['name']);
            }
            $servicesInfosExtra = $settings->getPriceAndTimeForServices($sumServices);
            $executionTime = 'PT'.$servicesInfosExtra['sumTime'].'M';
            $date = new DateTime($appointment);
            $interval = new DateInterval($executionTime);
            $date->add($interval);
            $newTime = $date->getTimestamp();
            $startTime = strtotime($appointment).'000';
            $endTime = $newTime.'000';
            $startDate = new DateTime($appointment);
            $endDate = new DateTime($appointment);
            $startTimeText = $startDate->format('Y-m-d H:i:s');
            $endDate->add($interval);
            $endDateText = $endDate->format('Y-m-d H:i:s');
            $notifications->push_appointment($presetId,$constructedAppointmentType,$barberId,$clientId,$startTime,$endTime,$startTimeText,$endDateText,'updated');
            return true;
        }
        return false;
    }

    public function update_date($haircutId,$execDate){
	    $execDate = date('Y-m-d H:i:s',strtotime($execDate));
        $appointmentInfos = $this->show_haircut_by_id($haircutId);
        $clientId = $appointmentInfos['customerId'];
        $barberId = $appointmentInfos['hairCutterId'];
        $notifications = new notifications();
        $services = new services();
        $settings = new Settings();
        $stmt = "UPDATE sym_haircuts SET dateTimeExecuted = :execDate WHERE `id` =:haircutId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":haircutId", $haircutId);
        $this->sdb->bind(":execDate", $execDate);
        $update = $this->sdb->execute();
        if ($update) {
            $serviceInfos = $services->show_services_by_haircut_id($haircutId);
            $constructedAppointmentType = '';
            $sumServices = array();
            foreach ($serviceInfos as $service =>$value){
                $constructedAppointmentType .= $serviceInfos[$service]['name'].' ';
                array_push($sumServices,$serviceInfos[$service]['name']);
            }
            $servicesInfosExtra = $settings->getPriceAndTimeForServices($sumServices);
            $executionTime = 'PT'.$servicesInfosExtra['sumTime'].'M';
            $date = new DateTime($execDate);
            $interval = new DateInterval($executionTime);
            $date->add($interval);
            $newTime = $date->getTimestamp();
            $startTime = strtotime($execDate).'000';
            $endTime = $newTime.'000';
            $startDate = new DateTime($execDate);
            $endDate = new DateTime($execDate);
            $startTimeText = $startDate->format('Y-m-d H:i:s');
            $endDate->add($interval);
            $endDateText = $endDate->format('Y-m-d H:i:s');
            $notifications->push_appointment($haircutId,$constructedAppointmentType,$barberId,$clientId,$startTime,$endTime,$startTimeText,$endDateText,'updated');
            return true;
        }
        return false;
    }

    public function deactive_haircut($haircutId,$barberId,$clientId){
	    $appointmentInfos = $this->show_haircut_by_id($haircutId);
        $notifications = new notifications();
        $services = new services();
        $stmt = "UPDATE sym_haircuts SET active = 0 WHERE `id` =:haircutId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":haircutId", $haircutId);
        $update = $this->sdb->execute();
        if ($update) {
            $startTime = strtotime($appointmentInfos['dateTimeExecuted']);
            $minutes = $appointmentInfos['executionTime'] * 60 * 1000;
            $endTime = $startTime + $minutes;
            $serviceInfos = $services->show_services_by_haircut_id($haircutId);
            $constructedAppointmentType = '';
            foreach ($serviceInfos as $service =>$value){
                $constructedAppointmentType .= $serviceInfos[$service]['name'].' ';
            }
            $executionTime = 'PT'.$minutes.'M';
            $interval = new DateInterval($executionTime);
            $startDate = new DateTime($appointmentInfos['dateTimeExecuted']);
            $endDate = new DateTime($appointmentInfos['dateTimeExecuted']);
            $startTimeText = $startDate->format('Y-m-d H:i:s');
            $endDate->add($interval);
            $endDateText = $endDate->format('Y-m-d H:i:s');
            $notifications->push_appointment($haircutId,$constructedAppointmentType,$barberId,$clientId,$startTime,$endTime,$startTimeText,$endDateText,'deleted');
            return true;
        }
        return false;
    }

    public function monthly_execution_time_average($type){
	    if($type == 'annual'){
            $stmt = "SELECT avg(executionTime) as average_time FROM sym_haircuts WHERE active = 1 AND customerId > 0 AND executionTime > 0 and commission > 0";
        }
	    if($type == 'daily'){
            $stmt = "SELECT avg(executionTime) as average_time FROM sym_haircuts WHERE active = 1 AND customerId > 0 AND executionTime > 0 and commission > 0 AND dateTimeExecuted >='".date('Y-m-d')." 00:00:00'";
        }
        $this->sdb->query($stmt);
        $exec = $this->sdb->single();
        if ($exec) {
            return round($exec['average_time']);
        }
        return false;
    }

    public function gather_stats_for_graph($fromMonth){
        $stmt = "SELECT * FROM  sym_profit_stats WHERE 1 ORDER BY id DESC LIMIT 1 OFFSET $fromMonth";
        $this->sdb->query($stmt);
        $products = $this->sdb->single();
        $fromMonth++;
        $stmt = "SELECT * FROM  sym_profit_stats WHERE 1 ORDER BY id DESC LIMIT 1 OFFSET $fromMonth";
        $this->sdb->query($stmt);
        $productsPrev = $this->sdb->single();
        if ($products) {
            $export['dato'] = $products;
            $decodedAnalisis = json_decode($products['dayAnalysis']);
            $export['dato']['dayAnalysis'] = $decodedAnalisis;
            $export['dato']['month'] = date('M Y',strtotime('01-'.$export['dato']['month']));
            $export['dailyProfit'] = $this->countDailyProfits();
            $maxCompletedOrders = 0;
            $maxSpendedTime = 0;
            $maxProfit = 0;
            $sumSpendedTime = 0;
            foreach ($decodedAnalisis as $dd =>$value){
                if($decodedAnalisis[$dd]->sumHaircuts > $maxCompletedOrders){ $maxCompletedOrders = $decodedAnalisis[$dd]->sumHaircuts; }
                if($decodedAnalisis[$dd]->sumTime > $maxSpendedTime){ $maxSpendedTime = $decodedAnalisis[$dd]->sumTime; }
                if($decodedAnalisis[$dd]->sumEur > $maxProfit){ $maxProfit = $decodedAnalisis[$dd]->sumEur; }
                $sumSpendedTime = $sumSpendedTime + $decodedAnalisis[$dd]->sumTime;
            }
            $export['dato']['monthDays'] = count($decodedAnalisis);
            $export['dato']['maxSpendedTimeOnDay'] = $maxSpendedTime;
            $export['dato']['maxHaircutsOnDay'] = $maxCompletedOrders;
            $export['dato']['maxProfitOnDay'] = $maxProfit;
            if($products['sumEurProfit'] == 0){
                $export['dato']['hourAvProf'] = (float)$sumSpendedTime / 1;
            }else{
                $export['dato']['hourAvProf'] = (float)$sumSpendedTime / (float)$products['sumEurProfit'];
            }
            if($productsPrev){
                $export['prevDato'] = $productsPrev;
                $decodedAnalisisPrev = json_decode($productsPrev['dayAnalysis']);
                $export['prevDato']['dayAnalysis'] = $decodedAnalisisPrev;
                $maxCompletedOrdersPrev = 0;
                $maxSpendedTimePrev = 0;
                $maxProfitPrev = 0;
                $sumSpendedTimePrev = 0;
                foreach ($decodedAnalisisPrev as $dd =>$value){
                    if($decodedAnalisisPrev[$dd]->sumHaircuts > $maxCompletedOrdersPrev){ $maxCompletedOrdersPrev = $decodedAnalisisPrev[$dd]->sumHaircuts; }
                    if($decodedAnalisisPrev[$dd]->sumTime > $maxSpendedTimePrev){ $maxSpendedTimePrev = $decodedAnalisisPrev[$dd]->sumTime; }
                    if($decodedAnalisisPrev[$dd]->sumEur > $maxProfitPrev){ $maxProfitPrev = $decodedAnalisisPrev[$dd]->sumEur; }
                    $sumSpendedTimePrev = $sumSpendedTimePrev + $decodedAnalisisPrev[$dd]->sumTime;
                }
                if($products['sumEurProfit'] == 0){
                    $export['prevDato']['hourAvProf'] = (float)$sumSpendedTimePrev / 1;
                }else{
                    $export['prevDato']['hourAvProf'] = (float)$sumSpendedTimePrev / (float)$productsPrev['sumEurProfit'];
                }
                $export['prevDato']['monthDays'] = count($decodedAnalisis);
                $export['prevDato']['maxSpendedTimeOnDay'] = $maxSpendedTimePrev;
                $export['prevDato']['maxHaircutsOnDay'] = $maxCompletedOrdersPrev;
                $export['prevDato']['maxProfitOnDay'] = $maxProfitPrev;
                $export['prevDato']['month'] = date('M Y',strtotime('01-'.$export['prevDato']['month']));
            }
            $stmt = "SELECT * FROM  sym_profit_stats WHERE active = 1";
            $this->sdb->query($stmt);
            $products2 = $this->sdb->execute();
            $products2 = $this->sdb->rowCount();
            $countedSwaps = $this->count_haircuts();
            $export['extraData']['sumActiveMonths'] = $products2;
            $export['extraData']['sumCompletedOrders'] = $countedSwaps;
            $export['dato']['dailyProfit'] = $this->countDailyProfits();
            return $export;
        }
        return false;
    }

    public function countDailyProfits(){
        $stmt = "SELECT * FROM  sym_haircuts WHERE dateTimeExecuted >= :dateTimeExecuted AND active = 1 AND customerId > 0 AND executionTime != '0' AND commission > 0";
        $this->sdb->query($stmt);
        $this->sdb->bind(":dateTimeExecuted", date('Y-m-d 00:00:00'));
        $results = $this->sdb->resultset();
        if($results){
            $sumProfit = 0;
            foreach ($results as $res =>$value){
                $sumProfit = $sumProfit + $results[$res]['commission'];
            }
            return $sumProfit;
        }
        return 0;
    }

    public function haircutsByDateRange($fromDate,$toDate){
        $stmt ="SELECT * FROM  sym_haircuts WHERE  customerId > 0 AND executionTime > 0 and commission > 0 AND active = 1 AND dateTimeExecuted BETWEEN :fromDate AND :toDate";
        $this->sdb->query($stmt);
        $this->sdb->bind('fromDate',$fromDate);
        $this->sdb->bind('toDate',$toDate);
        $results = $this->sdb->resultset();
        if($results ){
            return $results;
        }
        return false;
    }
}

?>