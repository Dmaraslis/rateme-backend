<?php
class notifications
{
    var $error = '';
    var $msg = '';
    public $sdb;

    public function __construct()
    {
        global $db;
        $this->sdb = $db;
        $this->baseUrl = 'https://notifications.barbreon.com';
        $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $parsedUrl = parse_url($currentUrl);
        $host = $parsedUrl['host'];
        $this->connection = substr($host, 0, strpos($host, '.'));
    }

    public function check_if_sms_sended($externalId,$appointmentType,$barberId,$clientId,$startTime,$endTime){
        $barbers = new barbers();
        $clients = new user();
        $barberInfos = $barbers->show_barbers_by_id($barberId);
        $clientInfos = $clients->show_customer_by_id($clientId);
        $fromUser =  USERID;
        $notificationType = 'appointment';
        $data = array(
            "data" => array(
                "patient" => array(
                    "id" => $clientInfos['id'],
                    "name" => $clientInfos['name'].' '.$clientInfos['surname'],
                    "email" => $clientInfos['email'],
                    "mobile" => $clientInfos['phone']
                ),
                "doctor" => array(
                    "id" => $barberInfos['id'],
                    "name" => $barberInfos['name'],
                    "email" => $barberInfos['email'],
                    "mobile" => $barberInfos['phone']
                ),
                "appointmentType" => $appointmentType,
                "id" => $externalId,
                "start" => $startTime,
                "end" => $endTime,
            ),
            "creatorID" => $fromUser,
            "notificationType" => $notificationType,
            "sendInstantNotice" => true
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl.'/check_notification_sms.php?connectionName='.$this->connection,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data,true),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $decodedResponse = json_decode($response,true);
        if($decodedResponse['canSendInstantNotification'] === true){
            return true;
        }else{
            return false;
        }
    }

    public function force_sms_notify($externalId,$appointmentType,$barberId,$clientId,$startTime,$endTime){
        $barbers = new barbers();
        $clients = new user();
        $barberInfos = $barbers->show_barbers_by_id($barberId);
        $clientInfos = $clients->show_customer_by_id($clientId);
        $fromUser =  USERID;
        $notificationType = 'appointment';
        $data = array(
            "data" => array(
                "patient" => array(
                    "id" => $clientInfos['id'],
                    "name" => $clientInfos['name'].' '.$clientInfos['surname'],
                    "email" => $clientInfos['email'],
                    "mobile" => $clientInfos['phone']
                ),
                "doctor" => array(
                    "id" => $barberInfos['id'],
                    "name" => $barberInfos['name'],
                    "email" => $barberInfos['email'],
                    "mobile" => $barberInfos['phone']
                ),
                "appointmentType" => $appointmentType,
                "id" => $externalId,
                "start" => $startTime,
                "end" => $endTime,
            ),
            "creatorID" => $fromUser,
            "notificationType" => $notificationType,
            "sendInstantNotice" => true
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl.'/send_notification_sms.php?connectionName='.$this->connection,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data,true),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if($httpCode === 200){
            return true;
        }else{
            $this->error = $response;
            return false;
        }
    }

    public function delete_notification($externalId,$appointmentType,$barberId,$clientId,$startTime,$endTime){
        $barbers = new barbers();
        $clients = new user();
        $barberInfos = $barbers->show_barbers_by_id($barberId);
        $clientInfos = $clients->show_customer_by_id($clientId);
        $data = array(
            "data" => array(
                "patient" => array(
                    "id" => $clientInfos['id'],
                    "name" => $clientInfos['name'].' '.$clientInfos['surname'],
                    "email" => $clientInfos['email'],
                    "mobile" => $clientInfos['phone']
                ),
                "doctor" => array(
                    "id" => $barberInfos['id'],
                    "name" => $barberInfos['name'],
                    "email" => $barberInfos['email'],
                    "mobile" => $barberInfos['phone']
                ),
                "appointmentType" => $appointmentType,
                "id" => $externalId,
                "start" => $startTime.'000',
                "end" => $endTime.'000',
            ),
            "action" => 'deleted',
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl.'/push.php?connectionName='.$this->connection,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data,true),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function push_appointment($externalId,$appointmentType,$barberId,$clientId,$startTime,$endTime,$startDateText,$endDateText,$actionType){
        $barbers = new barbers();
        $clients = new user();
        $barberInfos = $barbers->show_barbers_by_id($barberId);
        $clientInfos = $clients->show_customer_by_id($clientId);
        $fromUser =  USERID;
        $notificationType = 'appointment';
        if($actionType == 'created' || $actionType == 'updated'){
            $data = array(
                "data" => array(
                    "patient" => array(
                        "id" => $clientInfos['id'],
                        "name" => $clientInfos['name'].' '.$clientInfos['surname'],
                        "email" => $clientInfos['email'],
                        "mobile" => $clientInfos['phone']
                    ),
                    "doctor" => array(
                        "id" => $barberInfos['id'],
                        "name" => $barberInfos['name'],
                        "email" => $barberInfos['email'],
                        "mobile" => $barberInfos['phone']
                    ),
                    "appointmentType" => $appointmentType,
                    "id" => $externalId,
                    "start" => $startTime,
                    "end" => $endTime,
                    "startDate" => $startDateText,
                    "endDate" => $endDateText,
                ),
                "creatorID" => $fromUser,
                "notificationType" => $notificationType,
                "action" => $actionType
            );
        }else if($actionType == 'deleted'){
            $data = array(
                "data" => $externalId,
                "notificationType" => $notificationType,
                "action" => 'deleted'
            );
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl.'/push.php?connectionName='.$this->connection,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data,true),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function gatherNotifications($type){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl.'/get_notifications.php?type='.$type.'&userid='.USERID.'&connectionName='.$this->connection);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $decodedResponse = json_decode($server_output,true);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        foreach ($decodedResponse as $resp =>$value){
            $decodedResponse[$resp]['state'] = $this->check_notification_state($decodedResponse[$resp]['data']['id']);
        }
        $jsonReturn = $decodedResponse;
        if($status === 200){
            return $jsonReturn;
        }else{
            return false;
        }
    }

    public function check_notification_state($haircutId=null,$executionTime=null,$dateTimeExecuted=null,$appointmentAccepted=null,$appointmentDeclined=null,$commission=null,$service=null){
     //   date_default_timezone_set('Africa/Cairo'); // edw exw kanei -3 wres pisw apo to timestamp is calculated
        date_default_timezone_set("Etc/GMT-3");
        $haircuts = new haircuts();
        $settings = new Settings();
        $selectedState = '';
        //edw vlepoume an mas dini mono to haircut id kai tipota allo opote vriskoume ta ypoloipa monoi mas allios ta pernoume apo ta ypoloipa func vars if exists
        if(!is_null($haircutId) && $haircutId !== ''){
            $haircutInfo = $haircuts->show_haircut_by_id_fix_exec_time($haircutId);
        }else if(!is_null($executionTime) && $executionTime !== '' && !is_null($dateTimeExecuted) && $dateTimeExecuted !== '' && !is_null($appointmentAccepted) && $appointmentAccepted !== '' && !is_null($appointmentDeclined) && $appointmentDeclined !== '' && !is_null($commission) && $commission !== ''){
            $haircutInfo['executionTime'] = $executionTime;
            $haircutInfo['dateTimeExecuted'] = $dateTimeExecuted;
            $haircutInfo['appointmentAccepted'] = $appointmentAccepted;
            $haircutInfo['appointmentDeclined'] = $appointmentDeclined;
            $haircutInfo['commission'] = $commission;
            if(json_decode($service,true)){
                $haircutInfo['executionTimeCalculated'] = $settings->calculate_services_sum_time($service);
            }else{
                $haircutInfo['executionTimeCalculated'] = $settings->calculate_services_sum_time_by_string($service);
            }
        }
        if($haircutInfo){
            $appointmentStartTime = strtotime($haircutInfo['dateTimeExecuted']).'000';
            $nowTime = time().'000';
            $executionTime = 'PT'.$haircutInfo['executionTimeCalculated'].'M';
            $date = new DateTime($haircutInfo['dateTimeExecuted']);
            $interval = new DateInterval($executionTime);
            $date->add($interval);
            $appointmentEndTime = $date->getTimestamp().'000';
            if($appointmentStartTime > $nowTime){
                // an to rantevou einai argotera apo to twra
                if($haircutInfo['appointmentAccepted'] == 1){
                    $selectedState = 'Accepted';
                    $selectedIcon = '<div style="width:15px;height:15px;border-radius: 100%;background-color: #139623;margin: auto;"></div>';
                    $selectedEmoji = '🟢';
                }else if($haircutInfo['appointmentDeclined'] == 1){
                    $selectedState = 'Declined';
                    $selectedIcon = '<div style="width:15px;height:15px;border-radius: 100%;background-color: #ff0900;margin: auto;"></div>';
                    $selectedEmoji = '🔴';
                }else{
                    $selectedState = 'Pending Approval';
                    $selectedIcon = '<div style="width:15px;height:15px;border-radius: 100%;background-color: orange;margin: auto;"></div>';
                    $selectedEmoji = '🟠';
                }
            }else if($nowTime > $appointmentStartTime && $nowTime > $appointmentEndTime){
                // an to twra einai argotera apo to rantevou
                if($haircutInfo['executionTime'] == '0' && $haircutInfo['commission'] == 0){
                    //appointment is not executed yet
                    $selectedState = 'Pending price and commission to close appointment';
                    $selectedIcon = '<div style="width:15px;height:15px;border-radius: 100%;background-color: #ff6500;margin: auto;"><i style="position:relative;top: -1px;left: 4px;font-size: 17px;color: #000000;" class="fa fa-info" aria-hidden="true"></i></div>';
                    $selectedEmoji = '⚠️';
                }else if($haircutInfo['executionTime'] > '0' && $haircutInfo['commission'] > 0){
                    //appointment is executed
                    $selectedState = 'Completed';
                    $selectedIcon = '<div style="width:15px;height:15px;border-radius: 100%;background-color: #f8fff4;margin: auto;"><i style="position:relative;top:-2px;left:-1px;font-size: 20px;color: #2d2d2d" class="fa fa-check-circle-o" aria-hidden="true"></i></div>';
                    $selectedEmoji = '✔️';
                }else{
                    $selectedState = 'unpaid';
                    $selectedIcon = '<div style="width:15px;height:15px;border-radius: 100%;background-color: #f8fff4;margin: auto;">🚨</div>';
                    $selectedEmoji = '🚨';
                }
            }else {
                $selectedState = 'On progress';
                $selectedIcon = '<div style="width:15px;height:15px;border-radius: 100%;background-color: rebeccapurple;margin: auto;"><i style="position:relative;top:-2px;left:-1px;font-size: 20px;" class="fa fa-clock-o" aria-hidden="true"></i></div>';
                $selectedEmoji = '⌛';
            }
            $export['name'] = $selectedState;
            $export['icon'] = $selectedIcon;
            $export['emoji'] = $selectedEmoji;
            return $export;
        }else{
            return false;
        }
    }

    public function gatherNotificationsCount($type){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl.'/count_notifications.php?type='.$type.'&userid='.USERID.'&connectionName='.$this->connection);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $decodedResponse = json_decode($server_output,true);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $jsonReturn = $decodedResponse;
        if($status === 200){
            return $jsonReturn;
        }else{
            return false;
        }
    }

    public function gather_last_notification_info(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl.'/get_notifications.php?type=full&userid='.USERID.'&connectionName='.$this->connection);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $decodedResponse = json_decode($server_output,true);
        return $decodedResponse[0]['action'];
    }

    public function gather_smslog_pagination($page,$amount){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl.'/sms_quantity.php?type=SMS&page='.$page.'&amount='.$amount.'&connectionName='.$this->connection);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $decodedResponse = json_decode($server_output,true);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $jsonReturn['data'] = $decodedResponse;
        if($status === 200){
            return $jsonReturn;
        }else{
            return false;
        }
    }


    public function gather_pending_sos_by_barber_id(){
        $settings = new Settings();
        $stmt = "SELECT * FROM  sym_sos_approval_list WHERE active = 1 AND accepted = 0 AND declined = 0 AND hairCutterId = :barberId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":barberId", BARBERID);
        $result = $this->sdb->resultset();
        if($result){
            $appointmentsByDate = [];
            foreach ($result as $res =>$value){
                $explodedSlot = explode(' - ', $result[$res]['slot']);
                $result[$res]['dateTimeExecuted'] = $result[$res]['onDate'].' '.$explodedSlot[0].':00';
                $execTime = $settings->getPriceAndTimeForServices(explode(', ',$result[$res]['serviceId']));
                $result[$res]['executionTime'] = $execTime['sumTime'];
                $result[$res]['newApp'] = true;
                $test[0] = $result[$res];
                $singleEvent = $settings->processResults($test);
                $date = $result[$res]['onDate'];
                $previewedEntries = $settings->getRecourcesForSchedule($date,'daily');
                if (!isset($appointmentsByDate[$date])) {
                    $appointmentsByDate[$date] = [
                        'date' => $date,
                        'data' => $previewedEntries['events'] ?? $singleEvent['events'] ?? false
                    ];
                }
            }
            // Convert the appointmentsByDate array to a numeric array
            $appointmentsByDate = array_values($appointmentsByDate);
            // Sort the array by date
            usort($appointmentsByDate, function($a, $b) {
                $dateA = new DateTime($a['date']);
                $dateB = new DateTime($b['date']);
                return $dateA > $dateB;
            });
            return $appointmentsByDate;
        }
        return false;
    }





}

?>