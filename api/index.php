<?php require_once('./include/client-load.php');
if (isset($_POST['action']) && !empty($_POST['action'])){
    $refererReport = $_SERVER['HTTP_REFERER'];
    $jsonReturn = array();
    if($_POST['action'] == 'saveAppointment') {
        $continue = true;
        $missingFields = array();
        if (isset($_REQUEST['barberId']) && empty($_REQUEST['barberId'])) {
            array_push($missingFields,'barberId');
            $continue = false;
        }
        if (isset($_REQUEST['clientPhone']) && empty($_REQUEST['clientPhone'])) {
            array_push($missingFields, 'clientPhone');
            $continue = false;
        }

        if (isset($_REQUEST['clientSurname']) && empty($_REQUEST['clientSurname']) && isset($_REQUEST['clientName']) && empty($_REQUEST['clientName'])) {
            array_push($missingFields, 'clientSurname');
            $continue = false;
        }

        if (isset($_REQUEST['services']) && empty($_REQUEST['services'])) {
            array_push($missingFields, 'services');
            $continue = false;
        }
        $missingFields['length'] = count($missingFields);
        if ($continue) {
            $saveAction = $haircuts->saveAppointment($_REQUEST['barberId'], $_REQUEST['clientEmail'], $_REQUEST['clientName'], $_REQUEST['clientPhone'], $_REQUEST['clientSurname'], $_REQUEST['appointmentInfos'], $_REQUEST['services'],$_REQUEST['odos'],$_REQUEST['arithmos'],$_REQUEST['polh'],$_REQUEST['xwra'],$_REQUEST['tk'],$_REQUEST['orofos'],$_REQUEST['lng'],$_REQUEST['lat'],$_REQUEST['keyl'],$_REQUEST['note'],$_REQUEST['lang']);
            if($saveAction){
                $jsonReturn['response'] = $saveAction;
            }else{
                $jsonReturn['errorMessage'] = $haircuts->error;
                $jsonReturn['missingFields'] = false;
                $jsonReturn['response'] = $haircuts->msg ?? false;
            }
        } else {
            $jsonReturn['response'] = false;
            $jsonReturn['errorMessage'] = 'Please fill all required fields';
            $jsonReturn['missingFields'] = $missingFields;
        }
    }
    if($_POST['action'] == 'firstRender'){
        $sosHours = $settings->getSosHours($_REQUEST['lang']);
        $businessHours = $settings->getBusinessHours($_REQUEST['lang']);
        $setting = $settings->get_all();
        if($setting['applicationBranding'] == '1'){
            $jsonReturn['branding'] = 1;
        }else{
            $jsonReturn['branding'] = 0;
        }
        $jsonReturn['screen'] = $settings->gather_screen_infos($_REQUEST['keyl'],$_REQUEST['type'],$_REQUEST['lang'],$_REQUEST['extraData']);
        $jsonReturn['businessHours'] = $businessHours;
        $jsonReturn['sosHours'] = $sosHours;
        $jsonReturn['businessInfos'] = $settings->gather_business_infos();
        $jsonReturn['gallery'] = $settings->gather_business_gallery('3');//3 for portfolio images
    }
    if ($_POST['action'] == 'getRecourcesForSchedule') {
        if (!isset($_REQUEST['clientLocationData'])) {
            $_REQUEST['clientLocationData'] = null;  // Or whatever default you want to use
        }

        $previewedEntries = $settings->getFreeAppointments($_REQUEST['date'],$_REQUEST['services'],$_REQUEST['categoryTypeSelected'],$_REQUEST['barber'],$_REQUEST['clientLocationData']);
        $jsonReturn = $previewedEntries;
    }
    if($_POST['action'] == 'screenInfosGathering'){
        $jsonReturn = $settings->gather_screen_infos($_REQUEST['keyl'],$_REQUEST['type'],$_REQUEST['lang'],$_REQUEST['extraData']);
    }
    if ($_POST['action'] == 'getPriceAndTimeForServices') {
        if (!isset($_REQUEST['location']['clientLocationData'])) {
            $_REQUEST['location'] = 'false';  // Or whatever default you want to use
        }else{
            $_REQUEST['location'] = $_REQUEST['location']['clientLocationData'];
        }
        $jsonReturn = $settings->getPriceAndTimeForServices($_REQUEST['services'],$_REQUEST['barber'],$_REQUEST['location']);
    }
    echo json_encode($jsonReturn);exit();
}
?>