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

    public function getRecourcesForSchedule($date){
        $stmt ="SELECT * FROM sym_haircuts WHERE active = 1 AND dateTimeExecuted >= :dateStart AND dateTimeExecuted <= :dateEnd";
        $this->sdb->query($stmt);
        $this->sdb->bind(':dateStart',$date.' 00:00:00');
        $this->sdb->bind(':dateEnd',$date.' 23:59:00');
        $results = $this->sdb->resultset();
        if($results){
            $exported = array();
            $events = array();
            foreach ($results as $event =>$value3){
                $pushable['id'] = $results[$event]['id'];
                $pushable['start'] = $results[$event]['dateTimeExecuted'];
                if($results[$event]['executionTime'] == '0'){
                    $pushable['end'] = date('Y-m-d H:i:s',$this->addMinutesToTimestamp(strtotime($results[$event]['dateTimeExecuted']), $this->calculate_services_sum_time($results[$event]['serviceId'])));
                }else{
                    $pushable['end'] = date('Y-m-d H:i:s',$this->addMinutesToTimestamp(strtotime($results[$event]['dateTimeExecuted']), $results[$event]['executionTime']));
                }
                array_push($events,$pushable);
            }
            $exported['events'] = $events;
            return $exported;
        }
        return false;
    }


    function getFreeAppointments($selectedDate,$services,$categoryTypeSelected,$barber,$clientLocationData)
    {
        date_default_timezone_set('Europe/Athens');
        // Load settings, workingPlan, bookedAppointments based on the providerId
        $id = $barber['selectedBarberData']['id'];
        $settings = $this->get_all();
        $bookedAppointments = $this->getBookedAppointments($id, $selectedDate);
        if($categoryTypeSelected == 'outcall'){
            $selectedServicesInfos = $this->getPriceAndTimeForServices($services,$barber,$clientLocationData);
            $timeForTravel = $selectedServicesInfos['extraData']['singleDistanceTime']; //this is the time for one way round trip not that we have to consider also the travel back to base time that is the same
            $selectedServicesDuration = $selectedServicesInfos['extraData']['servicesTime'];
        }else{
            $selectedServicesInfos = $this->getPriceAndTimeForServices($services,$barber,'false');
            $timeForTravel = 0;
            $selectedServicesDuration = $selectedServicesInfos['sumTime'];
        }
        if((int)$settings['appointmentStep'] >= $selectedServicesDuration){
            $duration = $settings['appointmentStep'];
        }else{
            $duration = $selectedServicesDuration;
        }
        return $this->getSlots($duration, $bookedAppointments, $selectedDate,$timeForTravel);
    }

    function getSlots($duration, $bookedAppointments, $selectedDate,$timeForTravel)
    {
        //$setting = $this->get_all();
        $sosHours = $this->getSosHours_by_date($selectedDate);
        $businessHours = $this->getBusinessHours_by_date($selectedDate);
        $timezone = new DateTimeZone('Europe/Athens');
        $date = new DateTime();
        $date->setTimezone($timezone);
        $slots = array();
        $isHoliday = false;
        if($businessHours['active'] == '0' && $sosHours['active'] == '1'){
            $isHoliday = true;
            $hours = array(
                array("startTime" => $sosHours['startTime'], "endTime" => $sosHours['startTime']),
                $sosHours,
                array("startTime" => $sosHours['endTime'], "endTime" => $sosHours['endTime'])
            );
        }else{
            $hours = array(
                array("startTime" => $sosHours['startTime'], "endTime" => $businessHours['startTime']),
                $businessHours,
                array("startTime" => $businessHours['endTime'], "endTime" => $sosHours['endTime'])
            );
        }
        /* if($setting['autoHolidaysSOS'] == '1'){
             //$selectedDate = '2023-05-01';
             $holidays = new calendarific();
             $holidaysInGreece = $holidays->gather_holidays($setting['storeLocation'],$selectedDate);
             echo json_encode($holidaysInGreece);exit();
             //TODO: $selectedDate me afto kai me to is holiday mporw na kanw force ta appointments an einai holiday
             //$isHoliday = true; kai na ftiaxtei sta settings an thelei o allos ta holidays na ginontai aftomata SOS autoHolidaysSOS
         }*/
        if ($timeForTravel > 0) {
            $cachedTravelTime = $timeForTravel * 60; // turn minutes to seconds for epoch;
        } else {
            $cachedTravelTime = 0;
        }
        $durationSeconds = $duration * 60; // Convert duration from minutes to seconds
        foreach ($hours as $period) {
            $startTime = strtotime($selectedDate . ' ' . $period['startTime']);
            $endTime = strtotime($selectedDate . ' ' . $period['endTime']);
            $current = $startTime;
            while ($current + $durationSeconds + $cachedTravelTime <= $endTime) {
                $timeForTravel = $cachedTravelTime;
                $isSos = false;
                if ($period !== $businessHours || $isHoliday) {
                    $isSos = true;
                }
                $overlap = false;
                if ($bookedAppointments) {
                    foreach ($bookedAppointments['events'] as $appointment) {
                        $appointmentStart = strtotime($appointment['startTime']);
                        $appointmentEnd = strtotime($appointment['endTime']);
                        $end = $current + $durationSeconds + $cachedTravelTime;
                        if (($appointmentStart >= $current && $appointmentStart < $end + $timeForTravel) ||
                            ($appointmentEnd > $current && $appointmentEnd <= $end + $timeForTravel) ||
                            ($appointmentStart <= $current && $appointmentEnd >= $end + $timeForTravel)) {
                            $overlap = true;
                            break;
                        }
                    }
                }
                $startPreview = 0;
                $endPreview = 0;
                if ($timeForTravel > 0) {
                    $timeForTravelPreview = $timeForTravel;
                    $date->setTimestamp((int)$current);
                    $start = $date->format('H:i');
                    $date->setTimestamp((int)($current + $timeForTravel + $durationSeconds + $timeForTravel));
                    $end = $date->format('H:i');
                    $date->setTimestamp((int)($current + $timeForTravel));
                    $startPreview = $this->roundToNextXMinutes($date,10);
                    $date->setTimestamp((int)($current + $timeForTravel + $durationSeconds));
                    $endPreview = $this->roundToNextXMinutes($date,10);
                } else {
                    $timeForTravelPreview = 0;
                    $date->setTimestamp((int)$current);
                    $start = $this->roundToNextXMinutes($date,10);
                    $date->setTimestamp((int)($current + $durationSeconds));
                    $end = $this->roundToNextXMinutes($date,10);
                }
                $date->setTimestamp(time());
                $nowEpochTime = strtotime($date->format('Y-m-d H:i:s'));
                if (!$overlap && $nowEpochTime < $current) {
                        $slots[] = array(
                                        "start" => $start,
                                        "end" => $end,
                                        "free" => !$overlap,
                                        "isSos" => $isSos,
                                        "travelTime" => $timeForTravelPreview,
                                        "startPreview" => $startPreview,
                                        "endPreview" => $endPreview,
                                     );
                }
                $current += $durationSeconds;
            }
        }
        return $slots;
    }

    function roundToNearest($number, $multiple) {
        return round($number / $multiple) * $multiple;
    }

    function roundToNextXMinutes(DateTime $time, $interval) {
        $interval = $interval * 60; // Convert interval to seconds
        $currentSeconds = $time->getTimestamp();
        $remainder = $currentSeconds % $interval;

        // If there's any remainder, round up to the next interval
        if ($remainder > 0) {
            $roundedSeconds = $currentSeconds + ($interval - $remainder);
        } else {
            // If there's no remainder, no need to round
            $roundedSeconds = $currentSeconds;
        }

        $roundedTime = new DateTime();
        $roundedTime->setTimestamp($roundedSeconds);

        return $roundedTime->format('H:i');
    }











    public function getBookedAppointments($hairCutterId, $date)
    {
        $stmt = "SELECT * FROM sym_haircuts WHERE active = 1 AND hairCutterId = :hairCutterId AND dateTimeExecuted >= :dateStart AND dateTimeExecuted <= :dateEnd";
        $this->sdb->query($stmt);
        $this->sdb->bind(':hairCutterId', $hairCutterId);
        $this->sdb->bind(':dateStart', $date . ' 00:00:00');
        $this->sdb->bind(':dateEnd', $date . ' 23:59:59');

        $results = $this->sdb->resultset();

        if ($results) {
            $exported = array();
            $events = array();

            foreach ($results as $event => $value3) {
                $pushable['id'] = $results[$event]['id'];
                $pushable['startTime'] = $results[$event]['dateTimeExecuted'];

                if ($results[$event]['executionTime'] == '0') {
                    $pushable['endTime'] = date('Y-m-d H:i:s', $this->addMinutesToTimestamp(strtotime($results[$event]['dateTimeExecuted']), $this->calculate_services_sum_time($results[$event]['serviceId'])));
                } else {
                    $pushable['endTime'] = date('Y-m-d H:i:s', $this->addMinutesToTimestamp(strtotime($results[$event]['dateTimeExecuted']), $results[$event]['executionTime']));
                }

                array_push($events, $pushable);
            }

            $exported['events'] = $events;

            return $exported;
        }

        return false;
    }

    public function getSosHours_by_date($date){
        $dayNames = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        $dayOfWeek = date('w',strtotime($date));
        $stmt ="SELECT startTime,endTime,active FROM sym_sos_business_hours WHERE nameEn=:name";
        $this->sdb->query($stmt);
        $this->sdb->bind(':name',$dayNames[$dayOfWeek]);
        $result = $this->sdb->single();
        if($result) {
            return $result;
        }
        return false;
    }

    public function getBusinessHours_by_date($date){
        //todo: edw prepei na ftiaxtei ena function pou na elenxei apo 2o table sto database an yparxoun business hours se argies
        $dayNames = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        $dayOfWeek = date('w',strtotime($date));
        $stmt ="SELECT startTime,endTime,active FROM sym_business_hours WHERE nameEn=:name";
        $this->sdb->query($stmt);
        $this->sdb->bind(':name',$dayNames[$dayOfWeek]);
        $result = $this->sdb->single();
        if($result) {
            return $result;
        }
        return false;
    }

    function addMinutesToTimestamp($timestamp, $minutesToAdd) {
        $newTimestamp = $timestamp + ($minutesToAdd * 60);
        return $newTimestamp;
    }

    public function calculate_services_sum_time($activeServices){
        $services = new services();
        $activeServices = json_decode($activeServices);
        $execTime = 0;
        foreach ($activeServices as $service){
            $serviceInfos = $services->show_services_by_id($service);
            $execTime = $execTime + $serviceInfos['avExecution'];
        }
        return $execTime;
    }

    public function getBusinessHours($lang){
        $stmt ="SELECT id,active,endTime,startTime,name,nameEn FROM  sym_business_hours WHERE 1";
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

    public function getDistance($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        $distance = $earth_radius * $c;

        return $distance;
    }

    public function isLocationInIsland($haircutterlat, $haircutterlng, $lat, $lon) {
        $islands = $this->greek_islands();
        $haircutterIsland = '';
        $customerIsland = '';

        // First, determine which island the haircutter is on.
        foreach($islands as $island) {
            $distance = $this->getDistance($haircutterlat, $haircutterlng, $island['lat'], $island['lon']);
            if($distance <= $island['radius']) {
                $haircutterIsland = $island['name'];
                break;  // No need to check further if the haircutter's island is found.
            }
        }

        // Next, determine which island the customer is on.
        foreach($islands as $island) {
            $distance = $this->getDistance($lat, $lon, $island['lat'], $island['lon']);
            if($distance <= $island['radius']) {
                $customerIsland = $island['name'];
                break;  // No need to check further if the customer's island is found.
            }
        }

        // Apply your conditions
        if($haircutterIsland != '' && $customerIsland != '') {
            // Both are on an island, but not the same one
            return $haircutterIsland != $customerIsland;
        } elseif($haircutterIsland != '' && $customerIsland == '') {
            // Haircutter is on an island, customer is not
            return true;
        } elseif($haircutterIsland == '' && $customerIsland != '') {
            // Haircutter is not on an island, customer is
            return true;
        } else {
            // Both are not on an island
            return false;
        }
    }

    public function greek_islands() {
        $islands = [
            ['name' => 'Crete', 'lat' => 35.2401, 'lon' => 24.8093, 'radius' => 166.045],
            ['name' => 'Euboea', 'lat' => 38.5233, 'lon' => 23.8585, 'radius' => 80.778],
            ['name' => 'Lesbos', 'lat' => 39.2645, 'lon' => 26.2777, 'radius' => 42.447],
            ['name' => 'Rhodes', 'lat' => 36.4341, 'lon' => 28.2176, 'radius' => 39.771],
            ['name' => 'Chios', 'lat' => 38.3682, 'lon' => 26.1358, 'radius' => 25.721],
            ['name' => 'Kefalonia', 'lat' => 38.1754, 'lon' => 20.5692, 'radius' => 40.033],
            ['name' => 'Corfu', 'lat' => 39.6243, 'lon' => 19.9217, 'radius' => 22.500],
            ['name' => 'Samos', 'lat' => 37.7541, 'lon' => 26.9781, 'radius' => 19.779],
            ['name' => 'Naxos', 'lat' => 37.1021, 'lon' => 25.3764, 'radius' => 16.596],
            ['name' => 'Zakynthos', 'lat' => 37.7870, 'lon' => 20.8999, 'radius' => 20.000],
            ['name' => 'Andros', 'lat' => 37.8251, 'lon' => 24.9360, 'radius' => 16.874],
            ['name' => 'Thasos', 'lat' => 40.7700, 'lon' => 24.7000, 'radius' => 19.179],
            ['name' => 'Lemnos', 'lat' => 39.9200, 'lon' => 25.1450, 'radius' => 23.818],
            ['name' => 'Ikaria', 'lat' => 37.6039, 'lon' => 26.1403, 'radius' => 14.714],
            ['name' => 'Skyros', 'lat' => 38.9047, 'lon' => 24.5307, 'radius' => 12.577],
            ['name' => 'Paros', 'lat' => 37.0853, 'lon' => 25.1489, 'radius' => 10.500],
            ['name' => 'Milos', 'lat' => 36.7468, 'lon' => 24.4279, 'radius' => 9.833],
            ['name' => 'Amorgos', 'lat' => 36.8256, 'lon' => 25.8872, 'radius' => 10.500],
            ['name' => 'Tinos', 'lat' => 37.5502, 'lon' => 25.1662, 'radius' => 11.000],
            ['name' => 'Lefkada', 'lat' => 38.7062, 'lon' => 20.6408, 'radius' => 15.000],
            ['name' => 'Karpathos', 'lat' => 35.5079, 'lon' => 27.2132, 'radius' => 18.000],
            ['name' => 'Patmos', 'lat' => 37.3086, 'lon' => 26.5469, 'radius' => 8.500],
            ['name' => 'Kythnos', 'lat' => 37.3936, 'lon' => 24.4171, 'radius' => 8.000],
            ['name' => 'Sifnos', 'lat' => 36.9403, 'lon' => 24.7028, 'radius' => 7.500],
            ['name' => 'Anafi', 'lat' => 36.3638, 'lon' => 25.7693, 'radius' => 6.000],
            ['name' => 'Sporades', 'lat' => 39.1207, 'lon' => 23.6796, 'radius' => 20.000],
            ['name' => 'Skiathos', 'lat' => 39.1632, 'lon' => 23.4901, 'radius' => 7.500],
            ['name' => 'Paxi', 'lat' => 39.1982, 'lon' => 20.1835, 'radius' => 5.000],
            ['name' => 'Symi', 'lat' => 36.5853, 'lon' => 27.8428, 'radius' => 7.000],
            ['name' => 'Serifos', 'lat' => 37.1503, 'lon' => 24.4887, 'radius' => 6.500],
            ['name' => 'Folegandros', 'lat' => 36.6282, 'lon' => 24.9236, 'radius' => 5.000],
            ['name' => 'Antiparos', 'lat' => 37.0242, 'lon' => 25.0776, 'radius' => 5.000],
            ['name' => 'Sikinos', 'lat' => 36.6863, 'lon' => 25.1270, 'radius' => 4.500],
            ['name' => 'Alonnisos', 'lat' => 39.1468, 'lon' => 23.8733, 'radius' => 9.500],
            ['name' => 'Hydra', 'lat' => 37.3500, 'lon' => 23.4667, 'radius' => 5.500],
            ['name' => 'Spetses', 'lat' => 37.2632, 'lon' => 23.1542, 'radius' => 4.000],
            ['name' => 'Poros', 'lat' => 37.4982, 'lon' => 23.4556, 'radius' => 5.000],
            ['name' => 'Lipsi', 'lat' => 37.3000, 'lon' => 26.7667, 'radius' => 4.500],
            ['name' => 'Koufonisia', 'lat' => 36.9333, 'lon' => 25.6333, 'radius' => 3.500],
            ['name' => 'Agistri', 'lat' => 37.7039, 'lon' => 23.3417, 'radius' => 3.000],
            ['name' => 'Ithaki', 'lat' => 38.3640, 'lon' => 20.7205, 'radius' => 9.000],
            ['name' => 'Kimolos', 'lat' => 36.8103, 'lon' => 24.5597, 'radius' => 4.000],
            ['name' => 'Halki', 'lat' => 36.2167, 'lon' => 27.6333, 'radius' => 3.500],
            ['name' => 'Iraklia', 'lat' => 36.8500, 'lon' => 25.4667, 'radius' => 3.000],
            ['name' => 'Schinoussa', 'lat' => 36.8333, 'lon' => 25.5167, 'radius' => 2.500],
            ['name' => 'Donousa', 'lat' => 37.1000, 'lon' => 25.8167, 'radius' => 3.500],
            ['name' => 'Kasos', 'lat' => 35.4167, 'lon' => 26.9167, 'radius' => 5.500],
            ['name' => 'Gavdos', 'lat' => 34.8167, 'lon' => 24.0833, 'radius' => 4.500],
            ['name' => 'Kastellorizo', 'lat' => 36.1500, 'lon' => 29.5833, 'radius' => 2.500],
            ['name' => 'Othonoi', 'lat' => 39.8500, 'lon' => 19.4167, 'radius' => 4.000],
            ['name' => 'Salamis', 'lat' => 37.9411, 'lon' => 23.5043, 'radius' => 7.500],
            ['name' => 'Kos', 'lat' => 36.8915, 'lon' => 27.2878, 'radius' => 20.000],
            ['name' => 'Leros', 'lat' => 37.1339, 'lon' => 26.8525, 'radius' => 7.500],
            ['name' => 'Aegina', 'lat' => 37.7409, 'lon' => 23.5014, 'radius' => 7.000],
            ['name' => 'Korčula', 'lat' => 42.9623, 'lon' => 17.1366, 'radius' => 20.000],
            ['name' => 'Astypalaia', 'lat' => 36.5436, 'lon' => 26.3508, 'radius' => 7.500],
            ['name' => 'Kea', 'lat' => 37.6134, 'lon' => 24.3224, 'radius' => 8.000],
            ['name' => 'Ios', 'lat' => 36.7234, 'lon' => 25.2823, 'radius' => 8.000],
            ['name' => 'Kythira', 'lat' => 36.1528, 'lon' => 22.9209, 'radius' => 14.000],
            ['name' => 'Skopelos', 'lat' => 39.1243, 'lon' => 23.7238, 'radius' => 9.000],
            ['name' => 'Syros', 'lat' => 37.4636, 'lon' => 24.9426, 'radius' => 8.000],
            ['name' => 'Tilos', 'lat' => 36.4472, 'lon' => 27.3488, 'radius' => 7.500],
            ['name' => 'Kalymnos', 'lat' => 36.9630, 'lon' => 26.9807, 'radius' => 10.000],
            ['name' => 'Chalki', 'lat' => 36.2208, 'lon' => 27.6022, 'radius' => 4.000],
            ['name' => 'Paxos', 'lat' => 39.1982, 'lon' => 20.1835, 'radius' => 5.000],
            ['name' => 'Samothrace', 'lat' => 40.4747, 'lon' => 25.5251, 'radius' => 8.000],
            ['name' => 'Nisyros', 'lat' => 36.5864, 'lon' => 27.1658, 'radius' => 4.000],
            ['name' => 'Pserimos', 'lat' => 36.9978, 'lon' => 27.0900, 'radius' => 3.000],
            ['name' => 'Antipaxos', 'lat' => 39.1517, 'lon' => 20.2233, 'radius' => 2.000],
            ['name' => 'Ano Koufonisi', 'lat' => 36.9336, 'lon' => 25.6340, 'radius' => 2.500],
            ['name' => 'Othoni', 'lat' => 39.8500, 'lon' => 19.4167, 'radius' => 4.000],
            ['name' => 'Antikythera', 'lat' => 35.8667, 'lon' => 23.3000, 'radius' => 5.000],
            ['name' => 'Agathonisi', 'lat' => 37.4575, 'lon' => 26.9681, 'radius' => 3.000],
            ['name' => 'Fourni Korseon', 'lat' => 37.5833, 'lon' => 26.5167, 'radius' => 5.000],
            ['name' => 'Erikousa', 'lat' => 39.8333, 'lon' => 19.6000, 'radius' => 3.000],
            ['name' => 'Mathraki', 'lat' => 39.8000, 'lon' => 19.5333, 'radius' => 2.000],
            ['name' => 'Paximadia', 'lat' => 34.7833, 'lon' => 24.9833, 'radius' => 2.000],
            ['name' => 'Arkoi', 'lat' => 37.3761, 'lon' => 26.7361, 'radius' => 3.000],
            ['name' => 'Santorini', 'lat' => 36.3932, 'lon' => 25.4615, 'radius' => 5.5],
            ['name' => 'Dia', 'lat' => 35.3144, 'lon' => 25.1633, 'radius' => 4.000],
            ['name' => 'Paximadia', 'lat' => 34.8606, 'lon' => 24.8733, 'radius' => 1.0],
            ['name' => 'Elafonisi', 'lat' => 35.2694, 'lon' => 23.5444, 'radius' => 0.6],
            ['name' => 'Chrissi', 'lat' => 34.9289, 'lon' => 25.7417, 'radius' => 2.2],
        ];
        return $islands;
    }

    public function show_available_barbers_by_location($selectedServices,$lng,$lat,$lang){
        $barbers = new barbers();
        $services = new services();
        $sumPrice = 0;
        $sumTime = 0;
        $countedSelectedServices = count($selectedServices);
        foreach ($selectedServices as $service) {
            $selectedServiceInfos = $services->show_services_by_id($service['id']);
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
        $haircuttersAvailable = $barbers->show_barbers_available_for_outcall($lang);
        if($haircuttersAvailable){
            $availableOutCallHairCuttersByDistance = [];
            foreach ($haircuttersAvailable as $haircutter =>$value){
                // For each barber, calculate distance and check if it's within their max range
                if(!$this->isLocationInIsland($haircuttersAvailable[$haircutter]['lat'], $haircuttersAvailable[$haircutter]['lng'],$lat, $lng)) {
                    $distance = $this->getDistance_map_box($haircuttersAvailable[$haircutter]['lat'], $haircuttersAvailable[$haircutter]['lng'], $lat, $lng);
                    // Check if customer is within barber's max range
                    if($distance['distance'] <= $haircuttersAvailable[$haircutter]['maxDistance']) {
                        unset($haircuttersAvailable[$haircutter]['active']);
                        unset($haircuttersAvailable[$haircutter]['catIdsExecuted']);
                        unset($haircuttersAvailable[$haircutter]['dateTimeCreated']);
                        unset($haircuttersAvailable[$haircutter]['email']);
                        unset($haircuttersAvailable[$haircutter]['incall']);
                        unset($haircuttersAvailable[$haircutter]['lat']);
                        unset($haircuttersAvailable[$haircutter]['lng']);
                        unset($haircuttersAvailable[$haircutter]['maxDistance']);
                        unset($haircuttersAvailable[$haircutter]['outcall']);
                        unset($haircuttersAvailable[$haircutter]['phone']);
                        unset($haircuttersAvailable[$haircutter]['pricePerKm']);
                        unset($haircuttersAvailable[$haircutter]['nameEN']);
                        array_push($availableOutCallHairCuttersByDistance,$haircuttersAvailable[$haircutter]);
                    }
                }
            }
            if(count($availableOutCallHairCuttersByDistance) == 0){
                return 'noOneInRange';
            }else{
                return $availableOutCallHairCuttersByDistance;
            }
        }else{
            // If no barber is within range, return an error message or similar
            return 'noOutcallServicePpl';
        }
    }

    public function getPriceAndTimeForServices($selectedServices,$barberSelected,$location){
        $services = new services();
        $barbers = new barbers();
        $setting = $this->get_all();
        $response = [];
        $sumPrice = 0;
        $sumTime = 0;
        $extraData = false;
        $selectedBarberInfos = $barbers->show_barbers_by_id($barberSelected['selectedBarberData']['id']);
        if($selectedBarberInfos){
            $countedSelectedServices = count($selectedServices);
            foreach ($selectedServices as $service) {
                $selectedServiceInfos = $services->show_services_by_id($service['id']);
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
            if($location !== 'false'){
                $distance = $this->getDistance_map_box($selectedBarberInfos['lat'], $selectedBarberInfos['lng'], $location['latitude'], $location['longitude']);
                // Check if customer is within barber's max range
                if($distance['distance'] <= $selectedBarberInfos['maxDistance']) {
                    // Calculate price
                    $price = ($distance['distance'] * $selectedBarberInfos['pricePerKm']) * 2;//both sides calculation from a to b and from b to a
                    // Return price and time
                    $time = $distance['duration'] * 2;
                    $servicesSumTime = $sumTime;
                    $servicesPrice = $sumPrice;
                    $sumPrice = round($sumPrice + ($price),0);
                    $sumTime = round($sumTime + ($time),0);
                    $selectedSumTransportTime = round($time,0);
                    $selectedDistancePrice = round($price,0);
                    $selectedDistance = $distance['distance'];
                    $extraData = [
                        'servicesTime' => round($servicesSumTime, 0),
                        'servicesPrice' => round($servicesPrice,0),
                        'distanceTime' => round($selectedSumTransportTime,0),
                        'singleDistanceTime' => round($distance['duration'],0),
                        'distancePrice' => round($selectedDistancePrice,0),
                        'distance' => round($selectedDistance,0)
                    ];
                }else{
                    return array('error' => 'The distance is too far for a VIP appointment.');
                }
            }
            $response['sumPrice'] = $sumPrice;
            $response['sumTime'] = $sumTime;
            $response['extraData'] = $extraData;
            $response['sosPercentage'] = (int)$setting['sosAddPercentage'];
            return $response;
        }
        return false;
    }

    public function getDistance_map_box($lat1, $lon1, $lat2, $lon2) {
        $origin = $lon1.",".$lat1;
        $destination = $lon2.",".$lat2;
        $accessToken = "pk.eyJ1IjoiZG1hcmFzbGlzIiwiYSI6ImNsM3J3ZHFyaDBjcngzam8wMDh3ZXBseWEifQ.h9BeCL5d_iHIGwcJllndqw";
        $url = "https://api.mapbox.com/directions/v5/mapbox/driving/{$origin};{$destination}?access_token={$accessToken}";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set Here Your Requesred Headers
                'Content-Type: application/json',
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $responseArray = json_decode($response, true);
            $distance = $responseArray['routes'][0]['distance'] / 1000; // distance in kilometers
            $duration = $responseArray['routes'][0]['duration'] / 60; // duration in minutes
            return [
                'distance' => $distance,
                'duration' => $this->roundToNearest($duration,5),
            ];
        }
    }

    public function gather_screen_infos($selectedArea,$screenName,$lang,$extraData=null){
        $setting = $this->get_all();
        $services = new services();
        if($screenName === 'serviceTypeSelection'){
            $categoriesAndServices = $services->gather_categories_with_services_and_barbers($selectedArea,$lang,$extraData);
            $hasIncall = false;
            $hasOutcall = false;
            foreach($categoriesAndServices as $category) {
                foreach($category['services'] as $service) {
                    if($service['type'] == 'incall') {
                        $hasIncall = true;
                    }
                    if($service['type'] == 'outcall') {
                        $hasOutcall = true;
                    }
                }
            }
            if($hasIncall && $hasOutcall){
                $export['enabled'] = true;
                $export['enabledErrorScreen'] = false;
                $export['data']['incall'] = $this->render_lang('Ραντεβού στον χώρο μας','Appointment at our place',$lang);
                $export['data']['outcall'] = $this->render_lang('Ραντεβού στην τοποθεσία σας','Appointment at your place',$lang);
                $export['data']['outcallIcon'] = '<i class="fa-solid fa-route"></i>';
                $export['data']['incallIcon'] = '<i class="fa-solid fa-shop"></i>';
            }else if ($hasIncall){
                $export['enabled'] = false;
                $export['enabledErrorScreen'] = false;
                $export['data']['selectedType'] = 'incall';
            }else if ($hasOutcall){
                $export['enabled'] = false;
                $export['enabledErrorScreen'] = false;
                $export['data']['selectedType'] = 'outcall';
            }else{
                $export['enabled'] = false;
                $export['enabledErrorScreen'] = true;
                $export['data']['message'] = $this->render_lang('Πρέπει να δημιουργήσετε υπηρεσίες από το περιβάλλον διαχείρισης για να μπορούν οι χρήστες να επιλέγουν και να κλείνουν ραντεβού.','You need to create services from the management environment in order for users to be able to have to choose and close appointment',$lang);
            }
        }
        if($screenName === 'barberSelection'){
            $categoriesAndServices = $services->gather_categories_with_services_and_barbers($selectedArea,$lang,$extraData);
            $hasSingleBarber = false;
            $hasMultipleBarbers = false;
            $hasNoneBarber = false;
            $serviceFound = false;
            foreach($categoriesAndServices as $category) {
                if($category['id'] == $extraData['selectedServiceCategoryId']){
                    if($category['barbers'] && count($category['barbers']) > 1) {
                        $hasMultipleBarbers = true;
                    }else if ($category['barbers'] && count($category['barbers']) == 1){
                        $hasSingleBarber = true;
                    }else{
                        $hasNoneBarber = true;
                    }
                    $serviceFound = true;
                    $availableBarbers = $category['barbers'];
                    break;
                }
            }
            if($serviceFound){
                $notMeetLocation = false;
                if(isset($extraData['clientLocationData']['latitude'])){
                    $availableBarbersByLocation = $this->show_available_barbers_by_location($extraData['servicesSelected'],$extraData['clientLocationData']['longitude'],$extraData['clientLocationData']['latitude'],$lang);
                    if($availableBarbersByLocation == 'noOutcallServicePpl'){
                        $notMeetLocation = false;
                        $hasNoneBarber = true;
                        $hasSingleBarber = false;
                        $hasMultipleBarbers = false;
                    } else if($availableBarbersByLocation == 'noOneInRange'){
                        $notMeetLocation = true;
                        $hasNoneBarber = true;
                        $hasSingleBarber = false;
                        $hasMultipleBarbers = false;
                    }else {
                        if (count($availableBarbersByLocation) == 1) {
                            $notMeetLocation = false;
                            $hasNoneBarber = false;
                            $hasSingleBarber = true;
                            $hasMultipleBarbers = false;
                        } else if (count($availableBarbersByLocation) > 1) {
                            $notMeetLocation = false;
                            $hasNoneBarber = false;
                            $hasSingleBarber = false;
                            $hasMultipleBarbers = true;
                        }
                        $availableBarbers = $availableBarbersByLocation;
                    }
                }
                if($hasNoneBarber) {
                    $export['enabled'] = false;
                    if($notMeetLocation){
                        $export['enabledErrorScreen'] = false;
                        $data = [];
                    }else{
                        if ($lang == 'el') {
                            $data['message'] = 'Πρέπει να προσθέσετε έναν εργαζόμενο σε αυτή την κατηγορία υπηρεσιών';
                        } else {
                            $data['message'] = 'You must add a worker on that service category';
                        }
                        $export['enabledErrorScreen'] = true;
                    }
                    $export['data'] = $data;
                }
                if($hasSingleBarber){
                    $export['enabled'] = false;
                    $export['enabledErrorScreen'] = false;
                    $export['data'] = $availableBarbers;
                }
                if($hasMultipleBarbers){
                    $export['enabled'] = true;
                    $export['enabledErrorScreen'] = false;
                    $export['data'] = $availableBarbers;
                }
            }else{
                if($lang == 'el'){
                    $data['message'] = 'Πρέπει πρώτα να επιλέξετε μια υπηρεσία';
                }else{
                    $data['message'] = 'You must select an service first';
                }
                $export['enabled'] = false;
                $export['enabledErrorScreen'] = true;
                $export['data'] = $data;
            }
        }
        if($screenName === 'locationTrack'){
            $settings = new Settings();
            $setting = $settings->get_all();
            if($setting['locationTrackAppointment']){
                $export['enabled'] = true;
                $export['enabledErrorScreen'] = false;
                $export['data'] = array();
            }else{
                if($lang == 'el'){
                    $data['message'] = 'Οι υπηρεσίες τοποθεσίας είναι απενεργοποιημένες από το σύστημα';
                }else{
                    $data['message'] = 'Location services are disabled by system';
                }
                $export['enabled'] = false;
                $export['enabledErrorScreen'] = true;
                $export['data'] = $data;
            }
        }
        if($screenName === 'categoryAndServicesSelection'){
            $categoriesAndServices = $services->gather_categories_with_services($selectedArea,$lang,$extraData);
            if($categoriesAndServices){
                $export['enabledErrorScreen'] = false;
                if(count($categoriesAndServices) == 1){
                    $export['data']['categoriesTab']['enabled'] = false;
                    if(count($categoriesAndServices[0]['services']) <= 1){
                        $export['enabled'] = false;
                    }else{
                        $export['enabled'] = true;
                    }
                }
                if(count($categoriesAndServices) > 1){
                    $export['enabled'] = true;
                    $export['data']['categoriesTab']['enabled'] = true;
                }
                $export['data']['categoriesData'] = $categoriesAndServices;
            }else{
                $data['message'] = $this->render_lang('Πρέπει πρώτα να επιλέξετε έναν τύπο υπηρεσίας','You must select a service type first',$lang);
                $export['enabled'] = false;
                $export['enabledErrorScreen'] = true;
                $export['data'] = $data;
            }
        }
        if($screenName === 'bookScreen'){
            $export = $this->checkout_fields_and_options($lang);
        }
        return $export ?? false;
    }

    public function checkout_fields_and_options($lang){
        $setting = $this->get_all();
        //whetever exists here its previewed
        $fields['name']['fieldName'] = $this->render_lang('Όνομα','Name',$lang);
        $fields['name']['required'] = 1;
        $fields['surname']['fieldName'] = $this->render_lang('Επώνυμο','Surname',$lang);
        $fields['surname']['required'] = 1;
        $fields['phone']['fieldName'] = $this->render_lang('Τηλέφωνο','Phone',$lang);
        $fields['phone']['required'] = 1;
        if($setting['emailPreview'] == '1' || $setting['emailIsRequired'] == '1'){
            $fields['email']['fieldName'] = $this->render_lang('Email','Email',$lang);
            $fields['email']['required'] = (int)$setting['emailIsRequired'];
        }
        if($setting['addressPreview'] == '1' || $setting['addressIsRequired'] == '1') {
            $fields['address']['fieldName'] = $this->render_lang('Διεύθυνση', 'Address', $lang);
            $fields['address']['required'] = (int)$setting['addressIsRequired'];
        }
        if($setting['cityPreview'] == '1' || $setting['cityIsRequired'] == '1') {
            $fields['city']['fieldName'] = $this->render_lang('Πόλη', 'City', $lang);
            $fields['city']['required'] = (int)$setting['cityIsRequired'];
        }
        if($setting['notePreview'] == '1' || $setting['noteRequired'] == '1') {
            $fields['note']['fieldName'] = $this->render_lang('Σημείωση', 'Note', $lang);
            $fields['note']['required'] = (int)$setting['noteRequired'];
        }
        //        $fields[6]['fieldName'] = $this->render_lang('','Referrer',$lang);
        //        $fields[6]['required'] = 0;

        $checkoutOptions['guestCheckout'] = $setting['guestCheckout'];
        $checkoutOptions['userCheckout'] = $setting['userCheckout'];

        $export['inputFields'] = $fields;
        $export['checkoutOptions'] = $checkoutOptions;
        return $export;
    }

    public function render_lang($greekInput,$enInput,$selectedLang){
        if($greekInput && $selectedLang == 'el'){
            return $greekInput;
        }else if($enInput && $selectedLang == 'en'){
            return $enInput;
        }else{
            return $enInput ?? $greekInput;
        }
    }

    public function gather_business_infos(){
        $setting = $this->get_all();
        $export['businessName'] = $setting['businessName'];
        $export['businessAddress'] = $setting['businessAddress'];
        $export['businessPhone'] = $setting['businessPhone'];
        $export['longitude'] = $setting['businessLng'];
        $export['latitude'] = $setting['businessLat'];
        $export['businessLogo'] = $setting['businessLogo'];
        return $export;
    }

    public function gather_business_gallery($type){
        $stmt ="SELECT * FROM sym_business_images WHERE typeId = :typeId AND active = 1";
        $this->sdb->query($stmt);
        $this->sdb->bind(':typeId',$type);
        $results = $this->sdb->resultset();
        if ($results) {
            return $results;
        }
        return false;
    }
}

?>