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
        $stmt = "SELECT * FROM  sym_haircuts WHERE active = 1";
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


    public function getBarberMaxDistance($barberId){
	    return 0;
    }

    public function add_haircut($barberId, $clientId, $appointmentInfos, $services,$odos,$arithmos,$polh,$xwra,$tk,$orofos,$lng,$lat,$keyl,$note,$lang){
        $knownAreas = new knownAreas();
        $notifications = new notifications();
        $settings = new Settings();
        $barbers = new barbers();
        $servicesClass = new services();

        if(!isset($barberId)){
	        $this->error = 'Bad request please try again';
	        return false;
        }
        if(!isset($clientId)){
            $this->error = 'Bad request please try again';
            return false;
        }
        if(!isset($appointmentInfos)){
            $this->error = 'Bad request please try again';
            return false;
        }
        if(!isset($services)){
            $this->error = 'Bad request please try again';
            return false;
        }
        if(isset($keyl) && !isset($lng) || isset($keyl) && !isset($lat)){
            $this->error = 'Bad request please try again';
            return false;
        }
        if(isset($lng) && !isset($xwra)){
            $this->error = 'Bad request please try again';
            return false;
        }
        $isOutcall = 0;
        if(isset($lat) && isset($lng) && $lat != '' && $lat != 0 && $lng != '' && $lng != 0){
            //outcall
            $isOutcall = 1;
            $address = $odos.' '.$arithmos.' '.$polh.' '.$xwra.' '.$tk;
        }else{
            $address = '';
        }
        if(!empty($keyl) && !is_null($keyl)){
            $knownAreaInfos = $knownAreas->gather_area_by_url_param($keyl);
            $knownAreaId = $knownAreaInfos['id'];
            $knownAreaFee = $knownAreaInfos['feePercentage'];
        }else{
            $knownAreaId = 0;
            $knownAreaFee = 0;
        }
        if($isOutcall == 1){
            $location['longitude'] = $lng;
            $location['latitude'] = $lat;
        }else{
            $location = 'false';
        }
        $serviceNames = '';
        foreach ($services as $service =>$value){
            $export[$service] = $services[$service]['id'];
            $serviceNames .= $services[$service]['name'].', ';
        }
        $serviceId = json_encode($export);
        $appointment = $appointmentInfos['onDate'].' '.$appointmentInfos['startTime'].':00';
        $barberSelectedMatch['selectedBarberData']['id'] = $barberId;
        $appointmentTimeAndPrice = $settings->getPriceAndTimeForServices($services,$barberSelectedMatch,$location);
        if($appointmentInfos['isSos'] == 'true'){
            $isSOS = 1;
            $sosPercentage = $appointmentTimeAndPrice['sosPercentage'];  //incall & outcall
        }else{
            $isSOS = 0;
            $sosPercentage = 0;
        }
        if($appointmentTimeAndPrice){
            if($location !== 'false'){
                //has location
                $execTime = $appointmentTimeAndPrice['extraData']['distanceTime'] + $appointmentTimeAndPrice['extraData']['servicesTime'];
                if($isSOS === 1){
                    $haircutPriceOnlyServices = $appointmentTimeAndPrice['extraData']['servicesPrice'];
                    if($appointmentTimeAndPrice['sosPercentage'] == 100){
                        $haircutPriceOnlyServices = $haircutPriceOnlyServices * 2;
                    }else{
                        $haircutPriceOnlyServices += $haircutPriceOnlyServices * ($appointmentTimeAndPrice['sosPercentage'] / 100);
                    }
                    $haircutPrice = $appointmentTimeAndPrice['extraData']['distancePrice'] + $haircutPriceOnlyServices;
                }else{
                    $haircutPrice = $appointmentTimeAndPrice['extraData']['distancePrice'] + $appointmentTimeAndPrice['extraData']['servicesPrice'];
                }
                $distance = $appointmentTimeAndPrice['extraData']['distance'];
            }else{
                if($isSOS === 1){
                    $haircutPrice = $appointmentTimeAndPrice['sumPrice'];
                    if($appointmentTimeAndPrice['sosPercentage'] == 100){
                        $haircutPrice = $haircutPrice * 2;
                    }else{
                        $haircutPrice += $haircutPrice * ($appointmentTimeAndPrice['sosPercentage'] / 100);
                    }
                }else{
                    $haircutPrice = $appointmentTimeAndPrice['sumPrice'];
                }
                $execTime = $appointmentTimeAndPrice['sumTime'];
                $distance = 0;
            }
        }else{
            return array('error' => 'Error on calculation.');
        }
        if(empty($haircutPrice)){ $haircutPrice = 0; }
        if(empty($execTime)){ $execTime = 0; }
        $setting = $settings->get_all();
        $barberInfos = $barbers->show_barbers_by_id($barberId);
        if($barberInfos && $barberInfos['isSoftwareRenter'] !== 1){
            $barberPercentageCharge = $barberInfos['percentageChargedIfNotRenter'];
        }else{
            $barberPercentageCharge = 0;
        }
        $applicationFeePercentage = $setting['applicationOutcallPercentageFee'];
        $knownAreaFeePercentage = $knownAreaFee;
        $barberFeePercentage = $barberPercentageCharge;
        // Calculate fees
        $applicationFee = ($haircutPrice * $applicationFeePercentage) / 100;
        $knownAreaFeeAC = ($haircutPrice * $knownAreaFeePercentage) / 100;
        $barberFee = ($haircutPrice * $barberFeePercentage) / 100;
        // Subtract the fees from the haircut price
        $haircutProviderAmount = $haircutPrice - ($applicationFee + $knownAreaFeeAC + $barberFee);
        if($setting['isNormalEmployerCharge'] == 1 && $isOutcall === 0) {
            //normal charge can apply only on incall services not on outcall
            //if its selected that we want normal employee payments system stops to take comissions and also the renter of software
            $applicationFee = 0;
            $applicationFeePercentage = 0;
            $knownAreaFeeAC = 0;
            $knownAreaFeePercentage = 0;
            $barberFee = 0;
            $barberFeePercentage = 0;
            $haircutProviderAmount = 0;
        }//else employer is payed per service and head barber and also the software owner keeps comission and if exists the owner of a place

        $constructedNote = '<br><br> <h1 style="width: 100%">Προμήθειες</h1>';
        if($applicationFee > 0){
            $constructedNote .= '<p> Προμήθεια Datelly  : '.$applicationFee.'€ ('. $applicationFeePercentage .'%)</p>';
        }
        if($knownAreaFeeAC > 0){
            $constructedNote .= '<p> Προμήθεια Χώρου/Τοποθεσίας  : '.$knownAreaFeeAC.'€ ('. $knownAreaFeePercentage .'%)</p>';
        }
        if($barberFee > 0){
            $constructedNote .= '<p> Προμήθεια Ενοικιαστή Λογισμικού (Head Services owner) : '.$barberFee.'€ ('. $barberFeePercentage .'%)</p>';
        }
        if($haircutProviderAmount > 0){
            $constructedNote .= '<p> Ποσό που πρέπει να πάρει ο εργαζόμενος  : '.$haircutProviderAmount.'€ </p>';
        }
        if($note){
            $clientNote = '<h3 style="width: 100%">Σημείωση πελάτη</h3><p>'.$note.'</p>';
        }
        $noteFinal = '<h1 style="width: 100%">Mέσω του Datelly</h1><br><br>'.$clientNote.$constructedNote;


        if($appointmentInfos['isSos'] == 'true' || ($isOutcall == 1 && $setting['outcallNeedsConfirm'] == 1)){
            $sosInfos = $this->gather_pending_sos_by_clientId($clientId);
            if($sosInfos){
                if($sosInfos['slot'] !== $appointmentInfos['startTime'].' - '.$appointmentInfos['endTime'] && $sosInfos['onDate'] !== $appointmentInfos['onDate'] && $sosInfos['hairCutterId'] !== $barberId){
                    $this->error = 'requestCanceled';
                    $this->msg = $settings->render_lang('Η έγκριση του προηγούμενου ραντεβού εκκρεμεί.Δεν μπορείτε να δημιουργήσετε πολλαπλά ραντεβού που απαιτούν επιβεβαίωση.','Previous appointment approval is still pending.You can\'t create multiple appointments that require confirmation.',$lang);
                    return false;
                }
                return 'Waiting sos response...';
            }else{
                $sosInfos = $this->gather_sos_by_clientId($clientId);
                if($sosInfos){
                    if($sosInfos['accepted'] == 1 || $sosInfos['declined'] == 1){
                        $this->deactivate_sos($sosInfos['id']);
                        if($sosInfos['declined'] == 1){
                            $this->error = 'requestCanceled';
                            $this->msg = $sosInfos['serviceNote'];
                            return false;
                        }
                    }
                }else{
                    $this->add_sos_request($barberId,$clientId,$haircutPrice,$appointmentInfos['onDate'],$appointmentInfos['startTime'].' - '.$appointmentInfos['endTime'],$serviceNames,$knownAreaId,$address,$lng,$lat,$clientNote);
                    return 'Waiting sos response...';
                }
            }
        }
        if($setting['appointmentAutoEnd'] == '0'){
            $execTime = 0;
            $haircutPrice = 0;
        }
        $stmt = "INSERT INTO sym_haircuts (`serviceId`, `note`, `commission`,`executionTime`,`customerId`,`hairCutterId`,`dateTimeExecuted`,isOutcall,`isSOS`,`distance`,`knownAreaId`,`knownAreaFee`,`sosPercentage`,`street`,`streetNumber`,`town`,`country`,`zipCode`,`floor`,`lng`,`lat`,`applicationFee`,`applicationFeePercentage`,`knownAreaFeeAC`,`knownAreaFeePercentage`,`barberFee`,`barberFeePercentage`,`haircutProviderAmount`,`totalFeeCharged`) VALUES (:serviceId, :note,:haircutPrice,:execTime,:clientId,:barberId,:appointment,:isVip,:isSOS,:distance,:knowAreaId,:knownAreaFee,:sosPercentage,:odos,:arithmos,:polh,:xwra,:tk,:orofos,:lng,:lat,:applicationFee,:applicationFeePercentage,:knownAreaFeeAC,:knownAreaFeePercentage,:barberFee,:barberFeePercentage,:haircutProviderAmount,:totalFeeCharged)";
        $this->sdb->query($stmt);
        $this->sdb->bind(":serviceId", $serviceId);
        $this->sdb->bind(":note", $noteFinal);
        $this->sdb->bind(":haircutPrice", $haircutPrice);
        $this->sdb->bind(":execTime", $execTime);
        $this->sdb->bind(":clientId", $clientId);
        $this->sdb->bind(":barberId", $barberId);
        $this->sdb->bind(":appointment", $appointment);
        $this->sdb->bind(":isVip", $isOutcall);
        $this->sdb->bind(":isSOS", $isSOS);
        $this->sdb->bind(":sosPercentage", $sosPercentage);
        $this->sdb->bind(":distance", $distance);
        $this->sdb->bind(":odos", $odos);
        $this->sdb->bind(":arithmos", $arithmos);
        $this->sdb->bind(":polh", $polh);
        $this->sdb->bind(":xwra", $xwra);
        $this->sdb->bind(":tk", $tk);
        $this->sdb->bind(":orofos", $orofos);
        $this->sdb->bind(":lng", $lng);
        $this->sdb->bind(":lat", $lat);
        $this->sdb->bind(":knowAreaId", $knownAreaId);
        $this->sdb->bind(":knownAreaFee", $knownAreaFee);
        $this->sdb->bind(":applicationFee", $applicationFee);
        $this->sdb->bind(":applicationFeePercentage", $applicationFeePercentage);
        $this->sdb->bind(":knownAreaFeeAC", $knownAreaFeeAC);// AC = after calculations
        $this->sdb->bind(":knownAreaFeePercentage", $knownAreaFeePercentage);
        $this->sdb->bind(":barberFee", $barberFee);
        $this->sdb->bind(":barberFeePercentage", $barberFeePercentage);
        $this->sdb->bind(":haircutProviderAmount", $haircutProviderAmount);
        $this->sdb->bind(":totalFeeCharged", $applicationFeePercentage + $knownAreaFeePercentage + $barberFeePercentage);
        $add = $this->sdb->execute();
        $haircutId = $this->sdb->lastInsertId();
        if ($add) {
            $serviceInfos = $servicesClass->show_services_by_haircut_id($haircutId);
            $constructedAppointmentType = '';
            $sumServices = array();
            foreach ($serviceInfos as $service =>$value){
                $constructedAppointmentType .= $serviceInfos[$service]['name'].' ';
                array_push($sumServices,$serviceInfos[$service]['name']);
            }
            $executionTime = 'PT'.$appointmentTimeAndPrice['sumTime'].'M';
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
            $notifications->push_appointment($haircutId,$constructedAppointmentType,$barberInfos['id'],$clientId,$startTime,$endTime,$startTimeText,$endDateText,'created');
            return $sosInfos['serviceNote'] ?? 'no msg';
        }
        return false;
    }

    public function saveAppointment($barberId, $clientEmail, $clientName, $clientPhone, $clientSurname, $appointmentInfos, $services,$odos,$arithmos,$polh,$xwra,$tk,$orofos,$lng,$lat,$keyl,$notes,$lang){ //haircut
        $user = new User();
        $settings = new Settings();
        $clientInfos = $user->is_customer_by_phone($clientPhone);
        if(!$clientInfos){
            $clientInfos = $user->add_customer($clientName,$clientSurname,$clientPhone,'Client created through Barbreon',$clientEmail,'');//todo: referral
        }else{
            if($clientInfos['email'] != $clientEmail){
               $user->update_customer_email($clientInfos['id'],$clientEmail);
            }
        }
        if($this->checkIfSelectedRangeIsAvailable($appointmentInfos['onDate'].' '.$appointmentInfos['startTime'].':00',$appointmentInfos['onDate'].' '.$appointmentInfos['endTime'].':00',$barberId)){
            $response = $this->add_haircut($barberId,$clientInfos['id'],$appointmentInfos,$services,$odos,$arithmos,$polh,$xwra,$tk,$orofos,$lng,$lat,$keyl,$notes,$lang);
            if($response){
                return $response;
            }else{
                $this->error = $this->error;
                return false;
            }
        }
        $this->error =  $settings->render_lang('Η επιλεγμένη θέση ραντεβού είναι ήδη κλεισμένη. Παρακαλώ προσπαθήστε ξανά και επιλέξτε μια άλλη ώρα','The selected appointment slot is already booked. Please try again and select a different hour',$lang);
       return false;
	}

	public function checkIfSelectedRangeIsAvailable($startDateTime,$endDateTime,$barberId){
        $stmt = "SELECT * FROM  sym_haircuts WHERE active = 1 AND hairCutterId =:barberId AND dateTimeExecuted >=:startTime AND dateTimeExecuted < :endTime";
        $this->sdb->query($stmt);
        $this->sdb->bind(":barberId", $barberId);
        $this->sdb->bind(":startTime", $startDateTime);
        $this->sdb->bind(":endTime", $endDateTime);
        $result = $this->sdb->resultset();
        if($result){
            return false;
        }
        return true;
    }

    public function gather_sos_by_clientId($clientId){
        $stmt = "SELECT * FROM  sym_sos_approval_list WHERE active = 1 AND customerId =:clientId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":clientId", $clientId);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }

    public function gather_pending_sos_by_clientId($clientId){
        $stmt = "SELECT * FROM  sym_sos_approval_list WHERE active = 1 AND accepted = 0 AND declined = 0 AND customerId >=:clientId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":clientId", $clientId);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }


    public function update_sos_request($id, $hairCutterId, $customerId, $commission, $onDate, $slot, $serviceId, $knownAreaId, $address, $lng, $lat, $clientNote) {
        $stmt = "UPDATE sym_sos_approval_list 
             SET `clientNote` = :clientNote,
                 `onDate` = :onDate,
                 `slot` = :slot,
                 `knownAreaId` = :knownAreaId,
                 `address` = :address,
                 `serviceId` = :serviceId,
                 `commission` = :commission,
                 `customerId` = :customerId,
                 `hairCutterId` = :hairCutterId,
                 `lng` = :lng,
                 `lat` = :lat
             WHERE `id` = :id";

        $this->sdb->query($stmt);
        $this->sdb->bind(":id", $id);
        $this->sdb->bind(":clientNote", $clientNote);
        $this->sdb->bind(":onDate", $onDate);
        $this->sdb->bind(":slot", $slot);
        $this->sdb->bind(":knownAreaId", (int)$knownAreaId);
        $this->sdb->bind(":address", $address);
        $this->sdb->bind(":serviceId", $serviceId);
        $this->sdb->bind(":commission", $commission);
        $this->sdb->bind(":customerId", (int)$customerId);
        $this->sdb->bind(":hairCutterId", (int)$hairCutterId);
        $this->sdb->bind(":lng", $lng);
        $this->sdb->bind(":lat", $lat);

        $update = $this->sdb->execute();
        if ($update) {
            return true;
        }
        return false;
    }


    public function add_sos_request($hairCutterId,$customerId,$commission,$onDate,$slot,$serviceId,$knownAreaId,$address,$lng,$lat,$clientNote){
        $stmt = "INSERT INTO sym_sos_approval_list (`clientNote`,`onDate`,`slot`,`knownAreaId`,`address`,`serviceId`, `commission`,`customerId`,`hairCutterId`,`lng`,`lat`) VALUES (:clientNote,:onDate,:slot,:knowAreaId,:address,:serviceId,:commission,:customerId,:hairCutterId,:lng,:lat)";
        $this->sdb->query($stmt);
        $this->sdb->bind(":clientNote", $clientNote);
        $this->sdb->bind(":onDate", $onDate);
        $this->sdb->bind(":slot", $slot);
        $this->sdb->bind(":knowAreaId", (int)$knownAreaId);
        $this->sdb->bind(":address", $address);
        $this->sdb->bind(":serviceId", $serviceId);
        $this->sdb->bind(":commission", $commission);
        $this->sdb->bind(":customerId", (int)$customerId);
        $this->sdb->bind(":hairCutterId", (int)$hairCutterId);
        $this->sdb->bind(":lng", $lng);
        $this->sdb->bind(":lat", $lat);
        $add = $this->sdb->execute();
        if ($add) {
            return true;
        }
        return false;
    }

    public function deactivate_sos($id){
        $stmt = "UPDATE sym_sos_approval_list  SET `active`= 0 WHERE `id` =:id";
        $this->sdb->query($stmt);
        $this->sdb->bind(":id", $id);
        $update = $this->sdb->execute();
        if ($update) {
            return true;
        }
        return false;
    }

}

?>