<?php 

class calendarific
{
    var $error = '';
    var $key = 'SYMBIOTIC';
    public $sdb;

    public function __construct()
    {
        global $db;
        $this->sdb = $db;
    }



    public function gather_holidays($location,$selectedDate){
        //TODO na figei apo edw to key
        $url = 'https://calendarific.com/api/v2/holidays?&api_key=ad00b2d1e1868c45212a47d37d32860465f94757&country='.$location.'&year='.date('Y');
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl);
            // Handle the error
            echo 'cURL error: ' . $error;
        }
        curl_close($curl);
        // Process the response
        if ($response) {
            $data = json_decode($response, true);
            if($data['meta']['code'] == 200 && !isset($data['meta']['error_type'])){
                // Handle the response data
                return $data;
            }
        }
        return false;
    }


    public function save_holidays_to_json($location,$selectedDate) {
        //TODO: finish saving on json
        return false;
        // Get the holidays data
        $holidaysData = $this->gather_holidays($location,$selectedDate);

        // Check if we got the holidays data
        if ($holidaysData !== false) {
            // Get the current year
            $currentYear = date('Y');

            // Create the filename
            $filename = "holidays_".$currentYear.".json";

            // Save the data to the file
            file_put_contents($filename, json_encode($holidaysData));
        } else {
            echo "Error: Unable to fetch the holidays data.";
        }
    }




}

?>