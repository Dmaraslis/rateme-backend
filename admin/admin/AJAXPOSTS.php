<?php

require_once('./include/admin-load.php');

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])){
    $refererReport = $_SERVER['HTTP_REFERER'];
    $jsonReturn = array();

    if($_REQUEST['action'] == 'gather1stGraph'){
        $jsonReturn['response'] = $haircuts->gather_stats_for_graph($_REQUEST['date']);
    }

    if($_REQUEST['action'] == 'changeSettingState'){
        if($_REQUEST['settingName'] === 'hideBalances'){
            $jsonReturn['response'] = $settings->update_cms_balances_preview($_REQUEST['settingValue']);

        }else{
            $jsonReturn['response'] = $settings->update_setting($_REQUEST['settingName'],$_REQUEST['settingValue']);
        }
    }

    if($_REQUEST['action'] == 'gatherSettingsData'){
        $exportation['hideBalances'] = $settings->gather_cms_balances_preview();
        $jsonReturn['response'] = $exportation;
    }

    if($_REQUEST['action'] == 'gather_created_clients'){
        if(isset($_REQUEST['preSetId'])){
            $jsonReturn['selectedCustomer'] = $user->show_customer_by_id($_REQUEST['preSetId']);
        }
        $jsonReturn['customers'] = $user->show_customers();
    }

    if($_REQUEST['action'] == 'save_update_client'){
        if($_REQUEST['type'] == 'edit'){
            $jsonReturn['response'] = $user->update_customer($_REQUEST['preSetId'],$_REQUEST['name'],$_REQUEST['surname'],$_REQUEST['phone'],$_REQUEST['note'],$_REQUEST['email'],$_REQUEST['referer']);
        }else{
            $jsonReturn['response'] = $user->add_customer($_REQUEST['name'],$_REQUEST['surname'],$_REQUEST['phone'],$_REQUEST['note'],$_REQUEST['email'],$_REQUEST['referer']);
        }
    }

    if($_REQUEST['action'] == 'save_update_haircut'){
        if($_REQUEST['type'] == 'update_date'){
            $jsonReturn['response'] = $haircuts->update_date($_REQUEST['preSetId'],$_REQUEST['appointment']);
        }else if($_REQUEST['type'] == 'edit'){
            $jsonReturn['response'] = $haircuts->update_haircut($_REQUEST['preSetId'],json_encode($_REQUEST['serviceId']),$_REQUEST['note'],$_REQUEST['haircutPrice'],$_REQUEST['execTime'],$_REQUEST['haircutDiscount'],$_REQUEST['clientId'],$_REQUEST['barberId'],$_REQUEST['appointment']);
        }else{
            $jsonReturn['response'] = $haircuts->add_haircut(json_encode($_REQUEST['serviceId']),$_REQUEST['note'],$_REQUEST['haircutPrice'],$_REQUEST['execTime'],$_REQUEST['haircutDiscount'],$_REQUEST['clientId'],$_REQUEST['barberId'],$_REQUEST['appointment']);
        }
    }

    if($_REQUEST['action'] == 'deactivateCustomer'){
        $jsonReturn['response'] = $user->deactive_customer($_REQUEST['customerId']);
    }

    if($_REQUEST['action'] == 'deactivateHaircut'){
        $jsonReturn['response'] = $haircuts->deactive_haircut($_REQUEST['haircutId'],$_REQUEST['barberId'],$_REQUEST['clientId']);
    }

    if ($_REQUEST['action'] == 'gatherBulk') {
        $newArrayResult = array();
        if($_REQUEST['categoryType'] == 'clients'){
            $countedSwaps = $user->countCustomers();
        }else if ($_REQUEST['categoryType'] == 'barbers'){
            $countedSwaps = 0; //TODO fix it
        }else if ($_REQUEST['categoryType'] == 'haircuts'){
            $countedSwaps = $haircuts->count_haircuts();
        }else if($_REQUEST['categoryType'] == 'smslog'){
            $gatherData = $notifications->gather_smslog_pagination($_REQUEST['currentPage'],$_REQUEST['limit']);
            $swapRequests = $gatherData['data']['response'];
            $countedSwaps = $gatherData['data']['logSize'];
        }
        $extraExportation = round($countedSwaps / $_REQUEST['limit']);
        if(intval($_REQUEST['currentPage']) === 1){
            $newOffset = 0;
        }else if(intval($_REQUEST['currentPage']) <= $extraExportation){
            $newOffset = ($_REQUEST['currentPage'] * $_REQUEST['limit']) - $_REQUEST['limit'];
        }
        if($_REQUEST['categoryType'] == 'clients'){
            $swapRequests = $user->gather_clients_pagination($_REQUEST['limit'],$newOffset);
        }else if ($_REQUEST['categoryType'] == 'barbers'){
            $swapRequests = false;  //TODO fix it
        }else if ($_REQUEST['categoryType'] == 'haircuts'){
            $swapRequests = $haircuts->gather_haircuts_pagination($_REQUEST['limit'],$newOffset,$_REQUEST['previewType']);
        }
        foreach ($swapRequests as $request => $value) {
            if($_REQUEST['categoryType'] == 'clients'){
                if(empty($swapRequests[$request]['referrer'])){
                    $referer['name'] = 'none';
                    $referer['surname'] = '';
                }else{
                    $referer = $user->show_customer_by_id($swapRequests[$request]['referrer']);
                }
               $pushable = array(
                   'id' => $swapRequests[$request]['id'],
                   'name' => $swapRequests[$request]['name'],
                   'surname' => $swapRequests[$request]['surname'],
                   'phone' => $swapRequests[$request]['phone'],
                   'email' => $swapRequests[$request]['email'],
                   'referrerInfos' => $referer,
                   'note' => $swapRequests[$request]['note'],
                   'dateTimeCreated' => $swapRequests[$request]['dateTimeCreated'],
               );
            }else if($_REQUEST['categoryType'] == 'smslog'){
                $userInfos = $user->gather_admin_by_id($swapRequests[$request]['createdFromExternalUserId']);
                $swapRequests[$request]['userName'] = $userInfos['nickname'];
                $swapRequests[$request]['userPicture'] = $userInfos['image'];
                $pushable = $swapRequests[$request];
            }else if ($_REQUEST['categoryType'] == 'barbers'){
                $pushable = array();  //TODO fix it
            }else if ($_REQUEST['categoryType'] == 'haircuts'){$pushable = $swapRequests[$request];}
            array_push($newArrayResult, $pushable);
        }
        $jsonReturn['response'] = $newArrayResult;
        $jsonReturn['pages'] = $extraExportation;
        $jsonReturn['swaps'] = $countedSwaps;
        if($_REQUEST['categoryType'] == 'smslog') {
            $jsonReturn['smsLeft'] = $gatherData['data']['quantity'];
        }
    }

    if($_REQUEST['action'] == 'gather_haircut_infos'){
        if(isset($_REQUEST['preSetId']) && $_REQUEST['preSetId'] !== ''){
            $jsonReturn['selectedCustomer'] = $user->show_customer_by_haircut_id($_REQUEST['preSetId']);
            $jsonReturn['selectedService'] = $services->show_services_by_haircut_id($_REQUEST['preSetId']);
            $jsonReturn['selectedBarber'] = $barbers->show_barbers_by_haircut_id($_REQUEST['preSetId']);
            $jsonReturn['haircutInfo'] = $haircuts->show_haircut_by_id($_REQUEST['preSetId']);
            $constructedAppointmentType = '';
            $sumServices = [];
            foreach ($jsonReturn['selectedService'] as $service =>$value){
                $constructedAppointmentType .= $jsonReturn['selectedService'][$service]['name'].' ';
                $sumServices[] = $jsonReturn['selectedService'][$service]['name'];
            }
            $servicesInfosExtra = $settings->getPriceAndTimeForServices($sumServices);
            $executionTime = 'PT'.$servicesInfosExtra['sumTime'].'M';
            $date = new DateTime($jsonReturn['haircutInfo']['dateTimeExecuted']);
            $interval = new DateInterval($executionTime);
            $date->add($interval);
            $newTime = $date->getTimestamp();
            $startTime = strtotime($jsonReturn['haircutInfo']['dateTimeExecuted']).'000';
            $endTime = $newTime.'000';
            $jsonReturn['isSendedSMSManualNotification'] = $notifications->check_if_sms_sended($jsonReturn['haircutInfo']['id'],$constructedAppointmentType,$jsonReturn['selectedBarber'],$jsonReturn['selectedCustomer'],$startTime,$endTime);
        }
        $jsonReturn['barbers'] = $barbers->show_barbers();
        $jsonReturn['services'] = $services->show_services();
        $jsonReturn['customers'] = $user->show_customers();
    }

    if ($_REQUEST['action'] == 'getRecourcesForSchedule') {
        $previewedEntries = $settings->getRecourcesForSchedule($_REQUEST['from'],$_REQUEST['to']);
        $jsonReturn['response'] = $previewedEntries;
        $jsonReturn['businessHours'] = $settings->getSosHours('el');
        $jsonReturn['appointmentStep'] = $setting['appointmentStep'];
    }

    if($_REQUEST['action'] == 'getBusinessHours'){
        $businessHours = $settings->getBusinessHours();
        $jsonReturn = $businessHours;
    }

    if($_REQUEST['action'] == 'getBusinessAndSosHours'){
        $jsonReturn['businessHours'] = $settings->getBusinessHours();
        $jsonReturn['sosHours'] = $settings->getSosHours('el');
    }

    if($_REQUEST['action'] == 'saveBusinessHours'){

       // $businessHours = $settings->save_business_hours($_REQUEST['form']);
       // $jsonReturn = $businessHours;
        $jsonReturn = $_REQUEST['form'];
    }

    if($_REQUEST['action'] == 'chngamdps'){
        $jsonReturn = $user->changeadmpw($_REQUEST['newpw']);
    }

    if($_REQUEST['action'] == 'viewadps'){
        $jsonReturn = $user->viewadps($_REQUEST['newpw']);
    }

    if($_REQUEST['action'] == 'gather_sms_quantity'){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://notifications.barbreon.com/sms_quantity.php?type=SMS&page=".$_REQUEST['page']."&amount=".$_REQUEST['amount']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $decodedResponse = json_decode($server_output);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $jsonReturn['response'] = $decodedResponse;
    }

    if($_REQUEST['action'] == 'gatherNotifications'){
        $jsonReturn['response'] = $notifications->gatherNotifications('full'); //edw otan tha yparxoun poloi user tha epilegete to type depent on authorization access level of user
    }

    if($_REQUEST['action'] == 'gatherNotificationsCount'){
        $jsonReturn['response'] = $notifications->gatherNotificationsCount('full'); //edw otan tha yparxoun poloi user tha epilegete to type depent on authorization access level of user
        $jsonReturn['action'] = $notifications->gather_last_notification_info();
        $checkForSos = $notifications->gather_pending_sos_by_barber_id();
        if($checkForSos){
            $jsonReturn['responseSos'] = $checkForSos;
        }
        $jsonReturn['businessHours'] = $settings->getSosHours('el');
        $jsonReturn['appointmentStep'] = $setting['appointmentStep'];
    }

    if($_REQUEST['action'] == 'force_sms_notify'){
        date_default_timezone_set('Africa/Cairo'); // edw exw kanei -3 wres pisw apo to timestamp is calculated
        $customerInfos = $user->show_customer_by_id($user->show_customer_by_haircut_id($_REQUEST['preSetId']));
        $selectedServices = $services->show_services_by_haircut_id($_REQUEST['preSetId']);
        $selectedBarber = $barbers->show_barbers_by_haircut_id($_REQUEST['preSetId']);
        $haircutInfo = $haircuts->show_haircut_by_id($_REQUEST['preSetId']);
        $constructedAppointmentType = '';
        $sumServices = [];
        foreach ($selectedServices as $service =>$value){
            $constructedAppointmentType .= $selectedServices[$service]['name'].' ';
            $sumServices[] = $selectedServices[$service]['name'];
        }
        $servicesInfosExtra = $settings->getPriceAndTimeForServices($sumServices);
        $executionTime = 'PT'.$servicesInfosExtra['sumTime'].'M';
        $date = new DateTime($haircutInfo['dateTimeExecuted']);
        $interval = new DateInterval($executionTime);
        $date->add($interval);
        $newTime = $date->getTimestamp();
        $startTime = strtotime($haircutInfo['dateTimeExecuted']).'000';
        $endTime = $newTime.'000';
        $jsonReturn['customerInfos'] = $customerInfos; //todo fix edw me tis wres malon to exw kanei
        $jsonReturn['response'] = $notifications->force_sms_notify($haircutInfo['id'],$constructedAppointmentType,$selectedBarber,$customerInfos['id'],$startTime,$endTime);
    }

    if ($_REQUEST['action'] && $_REQUEST['action'] == 'topSearch') {
        $response = $settings->general_search($_REQUEST['typedText'],$_REQUEST['type']);
        $jsonReturn['response'] = $response;
    }

    if($_REQUEST['action'] && $_REQUEST['action'] == 'setupDiscountRule' && isset($_REQUEST['type'])){
        $_REQUEST['formData'] = htmlspecialchars_decode($_REQUEST['formData']);
        $jsonReturn = $_REQUEST['formData'];
        parse_str($_REQUEST['formData'], $data);
        if($_REQUEST['type'] == 'add'){
            $response = $settings->add_discount_rule($data);
            if($response){
                $jsonReturn['response'] = $settings->msg;
            }else{
                $jsonReturn['response'] = $settings->error;
            }
        }
        if($_REQUEST['type'] == 'edit'){
            $response = $settings->update_discount_rule($data);
            if($response){
                $jsonReturn['response'] = $settings->msg;
            }else{
                $jsonReturn['response'] = $settings->error;
            }
        }
    }

    if($_REQUEST['action'] == 'gather_hours_stats'){
        $businessHours = $settings->getBusinessHours();
        $appointments = $haircuts->get_appointments_current_month();
        $sumBusinessHours = 0;
        $workedHours = 0;
        $busyHours = 0;
        $currentDate = new DateTime();
        $firstDayOfMonth = (new DateTime())->modify('first day of this month');
        $daysInMonthTillNow = $currentDate->diff($firstDayOfMonth)->days;
        $weeks = intdiv($daysInMonthTillNow, 7);
        $remainingDays = $daysInMonthTillNow % 7;
        $businessHoursWeekly = 0;
        foreach ($businessHours as $day) {
            if ($day['active'] == '1') {
                $start = new DateTime($day['startTime']);
                $end = new DateTime($day['endTime']);
                $diff = $end->diff($start);
                $hours = $diff->h + ($diff->i / 60);
                $businessHoursWeekly += $hours;
            }
        }
        $sumBusinessHours = $businessHoursWeekly * $weeks;
        $daysProcessed = 0;
        $currentDayOfWeek = (int)$firstDayOfMonth->format('N') - 1;
        while ($daysProcessed < $remainingDays) {
            $day = $businessHours[$currentDayOfWeek];
            if ($day['active'] == '1') {
                $start = new DateTime($day['startTime']);
                $end = new DateTime($day['endTime']);
                $diff = $end->diff($start);
                $hours = $diff->h + ($diff->i / 60);
                $sumBusinessHours += $hours;
            }

            $currentDayOfWeek = ($currentDayOfWeek + 1) % 7;
            $daysProcessed++;
        }
        foreach ($appointments as $appointment) {
            if ($appointment['commission'] > 0 && $appointment['customerId'] > 0) {
                $workedHours += $appointment['executionTime'];
            } elseif ($appointment['commission'] == 0 && $appointment['customerId'] == 0) {
                $busyHours += 1;
            }
        }
        $workedHours = $workedHours / 60;
        $noClientHours = $sumBusinessHours - $workedHours - $busyHours;
        $jsonReturn['businessHours'] = $businessHours;
        $jsonReturn['noClientHours'] = $noClientHours;
        $jsonReturn['busyHours'] = $busyHours;
        $jsonReturn['workedHours'] = $workedHours;
    }

    if($_REQUEST['action'] == 'acceptSos'){
        $jsonReturn = $settings->accept_sos($_REQUEST['sosId'],$_REQUEST['message'] ?? '');
    }

    if($_REQUEST['action'] == 'declineSos'){
        $jsonReturn = $settings->decline_sos($_REQUEST['sosId'],$_REQUEST['message'] ?? '');
    }

    echo json_encode($jsonReturn);
}
?>