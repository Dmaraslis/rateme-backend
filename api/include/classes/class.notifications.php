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

    public function push_appointment($externalId,$appointmentType,$barberId,$clientId,$startTime,$endTime,$startDateText,$endDateText,$actionType){
        $barbers = new barbers();
        $clients = new user();
        $barberInfos = $barbers->show_barbers_by_id($barberId);
        $clientInfos = $clients->show_customer_by_id($clientId);
        $fromUser =  $clientInfos['id'];
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
                        "nameEN" => $barberInfos['nameEN'],
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

    public function appointmentUpdate($data){
        $data = json_decode($data,'true');
        if($data['confirmation'] == 1){
            $stmt = "UPDATE sym_haircuts SET appointmentAccepted = 1 , appointmentDeclined = 0 WHERE `id` =:haircutId";
        }else if($data['confirmation'] == 0){
            $stmt = "UPDATE sym_haircuts SET appointmentAccepted = 0 , appointmentDeclined = 1 WHERE `id` =:haircutId";
        }else if($data['confirmation'] == 2){
            $stmt = "UPDATE sym_haircuts SET appointmentAccepted = 0 , appointmentDeclined = 0 WHERE `id` =:haircutId";
        }
        $this->sdb->query($stmt);
        $this->sdb->bind(":haircutId", $data['appointmentId']);
        $update = $this->sdb->execute();
        if ($update) {
            return true;
        }
        return false;
    }
}

?>