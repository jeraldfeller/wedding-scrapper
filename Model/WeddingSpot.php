<?php

/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 10/18/2018
 * Time: 4:54 AM
 */
class WeddingSpot
{
    public $debug = TRUE;
    protected $db_pdo;

    public function getTheKnotLocations(){
//        $locationApiUrl = 'https://no-services.theknot.com/geo/locations/city/?apiKey=vkq9ckuqn9c7jprn92uwbsjkzmtbk6pdxh9&limit=100';
//        $apiKey = 'vkq9ckuqn9c7jprn92uwbsjkzmtbk6pdxh9';
//        $html = file_get_html($locationApiUrl, false);
//        $locs = json_decode($html, true);

//        for($x = 0; $x < count($locs['locations']); $x++){
//            $location = str_replace(' ', '-', $locs['locations'][$x]['state']);
//            $pdo = $this->getPdo();
//            $sql = 'INSERT INTO `location_spot` SET `loc` = "'.$location.'"';
//            $stmt = $pdo->prepare($sql);
//            $stmt->execute();
//            $pdo = null;
//        }
        $pdo = $this->getPdo();
        $sql = 'SELECT `loc` FROM `location`';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $locArray = explode('-', $row['loc']);
            if(count($locArray) == 3){
                $location = ucfirst($locArray[1])."-".ucfirst($locArray[2]);
            }else{
                $location = ucfirst($locArray[1]);
            }
            $sql1 = 'INSERT INTO `location_spot` SET `loc` = "'.$location.'"';
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute();
        }
        $pdo = null;
    }


    public function scrapeVenue($html){
        $titleContainer = $html->find('#venue-details-images', 0);
        $detailsContainer = $html->find('#venue-sidebar-affix', 0);
        if ($titleContainer) {
            $title = $titleContainer->find('h1', 0)->plaintext;
        } else {
            $title = '';
        }
        $contactNumber = '';
        $website = '';
        if ($detailsContainer) {
            $vendorAddressContainer = $detailsContainer->find('#vendor-address', 0);
            if ($vendorAddressContainer) {
                $spans = $vendorAddressContainer->find('span');
                if ($vendorAddressContainer->find('#show-phone-number', 0)) {
                    $contactNumber = $vendorAddressContainer->find('#show-phone-number', 0)->plaintext;
                }
                if ($vendorAddressContainer->find('#email-vendor-link', 0)) {
                    $website = $vendorAddressContainer->find('#email-vendor-link', 0)->getAttribute('href');
                }

                for ($x = 0; $x < count($spans); $x++) {
                    $s = $spans[$x];
                    if ($s->getAttribute('itemprop') == 'streetAddress') {
                        $address = $s->plaintext;
                    } else if ($s->getAttribute('itemprop') == 'addressLocality') {
                        $locale = $s->plaintext;
                    } else if ($s->getAttribute('itemprop') == 'addressRegion') {
                        $region = $s->plaintext;
                    } else if ($s->getAttribute('itemprop') == 'postalCode') {
                        $zip = $s->plaintext;
                    }
                }
            }

            $facebook = '';
            $instagram = '';
            $twitter = '';
            $pinterest = '';
            $yelp = '';

            // table
            $quickTable = $html->find('#quick-details-table', 0);
            $style = '';
            $maxCapacity = '';
            $ceremony = '';
            $reception = '';
            $cateringOptions = '';
            $alcoholOptions = '';
            $timeRestrictions = '';
            $setting = '';
            $description = '';
            $venueStyle = '';
            $services = '';
            $capacity = '';
            $rentalFees = '';
            $weddingCost = '';
            $catering = '';
            $alcohol = '';
            // $amenities = '';
            $specialRestrictions = '';
            if($quickTable){
                $row = $quickTable->find('tr');
                if($row){
                    for($r = 0; $r < count($row); $r++){
                        $td = $row[$r]->find('td', 0);
                        if($td){
                            $tTitle = trim($td->plaintext);
                            if (strpos($tTitle, 'Style') !== false) {
                                $tValue = $row[$r]->find('td', 1)->plaintext;
                                $style = $tValue;
                            }
                            if (strpos($tTitle, 'Max Capacity') !== false) {
                                $tValue = $row[$r]->find('td', 1)->plaintext;
                                $maxCapacity = $tValue;
                            }
                            if (strpos($tTitle, 'Ceremony') !== false) {
                                $tValue = $row[$r]->find('td', 1)->plaintext;
                                $ceremony = $tValue;
                            }
                            if (strpos($tTitle, 'Reception') !== false) {
                                $tValue = $row[$r]->find('td', 1)->plaintext;
                                $reception = $tValue;
                            }
                            if (strpos($tTitle, 'Catering Options') !== false) {
                                $tValue = $row[$r]->find('td', 1)->plaintext;
                                $cateringOptions = $tValue;
                            }
                            if (strpos($tTitle, 'Alcohol Options') !== false) {
                                $tValue = $row[$r]->find('td', 1)->plaintext;
                                $alcoholOptions = $tValue;
                            }
                            if (strpos($tTitle, 'Time Restrictions') !== false) {
                                $tValue = $row[$r]->find('td', 1)->plaintext;
                                $timeRestrictions = $tValue;
                            }
                            if (strpos($tTitle, 'Special Restrictions') !== false) {
                                $tValue = $row[$r]->find('td', 1)->plaintext;
                                $specialRestrictions = $tValue;
                            }
                        }
                    }
                }
            }

            $amenities = array();
            $amenitiesItem = $html->find('.amenity-item');
            for ($a = 0; $a < count($amenitiesItem); $a++) {
                $amenities[] = trim($amenitiesItem[$a]->plaintext);
            }

            $overview = $html->find('#overview', 0);
            $h4 = $overview->find('h4');
            $p = $overview->find('p');

            for ($h = 0; $h < count($h4); $h++) {
                $h4Title = trim($h4[$h]->plaintext);
                if (strpos($h4Title, 'Setting') !== false) {
                    $setting = trim($p[$h]->plaintext);
                }
                if (strpos($h4Title, 'Description') !== false) {
                    $description = trim($p[$h]->plaintext);
                }
                if (strpos($h4Title, 'Venue Style') !== false) {
                    $venueStyle = trim($p[$h]->plaintext);
                }
                if (strpos($h4Title, 'Services') !== false) {
                    $services = trim($p[$h]->plaintext);
                }
                if (strpos($h4Title, 'Capacity') !== false) {
                    $capacity = trim($p[$h]->plaintext);
                }
                if (strpos($h4Title, 'Rental Fees') !== false) {
                    $rentalFees = trim($p[$h]->plaintext);
                }
                if (strpos($h4Title, 'Wedding Cost') !== false) {
                    $weddingCost = trim($p[$h]->plaintext);
                }
                if (strpos($h4Title, 'Catering') !== false) {
                    $catering = trim($p[$h]->plaintext);
                }
                if (strpos($h4Title, 'Alcohol') !== false) {
                    $alcohol = trim($p[$h]->plaintext);
                }
                if (strpos($h4Title, 'Special Restrictions') !== false) {
                    $specialRestrictions = trim($p[$h]->plaintext);
                }
            }
        }

        $data = array(
            'Business_Type' => 'Wedding Venues',
            'Business_Name' => trim($title),
            'Business_Address' => trim($address),
            'Business_City' => trim($locale),
            'Business_State' => trim($region),
            'Business_Zip_Code' => trim($zip),
            'Business_Phone' => trim($contactNumber),
            'Business_Website' => trim($website),
            'Style' => addslashes($style),
            'Max_Capacity' => addslashes($maxCapacity),
            'Ceremony' => addslashes($ceremony),
            'Reception' => addslashes($reception),
            'Catering_Options' => addslashes($cateringOptions),
            'Alcohol_Options' => addslashes($alcoholOptions),
            'Time_Restrictions' => addslashes($timeRestrictions),
            'Setting' => addslashes($setting),
            'Description' => addslashes($description),
            'Venue_Style' => addslashes($venueStyle),
            'Services' => addslashes($services),
            'Capacity' => addslashes($capacity),
            'Rental_Fees' => addslashes($rentalFees),
            'Wedding_Cost' => addslashes($weddingCost),
            'Catering' => addslashes($catering),
            'Alcohol' => addslashes($alcohol),
            'Amenities' => implode(',', $amenities),
            'Special_Restrictions' => addslashes($specialRestrictions),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_spot_venue');
    }


    public function insertData($data, $table)
    {
        $insertQry = '';
        foreach ($data as $col => $val) {
            $insertQry .= "`$col` = '$val',";
        }
        $insertQry = rtrim($insertQry, ',');
        $pdo = $this->getPdo();
        // check if vendor exists
        $sql = "SELECT count(`id`) AS rowCount FROM `$table` WHERE `Business_Name` = '" . $data['Business_Name'] . "'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $return = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($return['rowCount'] == 0) {
            try {
                $sql = "INSERT INTO `$table` SET $insertQry";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            } catch (ErrorException $e) {
                $file = fopen("mysql_error_weddingspot.txt", "a");
                fwrite($file, $e . "\n" . $sql . "\n");
                fclose($file);
            }

        }
        $pdo = null;
    }

    public function getLinks(){
        $pdo = $this->getPdo();
        $sql = 'SELECT `id`,`url` FROM `spot_link` WHERE `status` = 0 LIMIT 10';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $content = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $sql2 = 'UPDATE `spot_link` SET `status` = 1 WHERE `id` = '.$row['id'];
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute();
            $content[] = $row;
        }
        return $content;


    }

    public function registerLink($url){
        $pdo = $this->getPdo();
        $sql = 'INSERT INTO `spot_link` SET `url` = "'.$url.'"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
    }

    public function getTotalItems($urlGeo)
    {
        $htmlDataList = $this->curlTo($urlGeo);
        $html = str_get_html($htmlDataList['html']);
        $totalItems = trim($html->find('#result-header-subtext', 0)->plaintext);
        $totalItems = preg_replace("/[^0-9]/", "", $totalItems);
        return $totalItems;
    }

    public function getNextLocation(){
        $pdo = $this->getPdo();
        $sql = 'SELECT `id`, `loc` FROM `location_spot` WHERE `status` = 0 LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $return = $stmt->fetch(PDO::FETCH_ASSOC);

        if($return){
            $sql = 'UPDATE `location_spot` SET `status` = 1 WHERE `id` = '.$return['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $pdo = null;
            return $return['loc'];
        }else{
            $pdo = null;
            return false;
        }
    }

    public function curlTo($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
            "Cookie: __utmz=78733387.1536827417.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); _ga=GA1.2.2050095233.1536827417; __qca=P0-316666466-1536827417631; wwa_cache=5103aadc-bb98-441f-b60f-d25dfbe83e94; ki_r=; ki_s=; _gcl_au=1.1.733368830.1537827395; __gads=ID=a88c70ebdf410077:T=1537827397:S=ALNI_Maq0gA_T6fIidV4uPHGq-ksfG9-Rw; __lc.visitor_id.6579241=S1537827425.d324d7d9d0; ww_analytics_uuid=5103aadc-bb98-441f-b60f-d25dfbe83e94; _gid=GA1.2.601036835.1539564995; split=%7B%22ARCT_2613_4%22%3A%22a%22%2C%22KS_ALG%22%3A%22v1%22%7D; recentlyViewedVendors=NjQ1NTktMTUzOTU3MTc0OHwxMTg2NDAtMTUzOTU3MjIzNXw5MTc3NTktMTUzOTU3MjM5Nnw3MzIyNTAtMTUzOTU3MjQ4M3w1OTc4MDMtMTUzOTU3MzQ3MXwxODkyNjQtMTUzOTU3NDA1OXwxNTE0LTE1Mzk1NzQzNTd8Mjg1NC0xNTM5NTc0Mzg4fDYwNTk4MC0xNTM5NTc0Mzg5fDU5NTE0Mi0xNTM5NTc0NTgxfDQ3NjI1My0xNTM5NTc0NTg0fDM4NDc0OC0xNTM5NTc0NjQxfDM2ODg1NS0xNTM5NTc0NzE5fDU3MjYxNC0xNTM5NTc0NzIzfDMyNDYtMTUzOTU3NDk0NHwzMDEwMjEtMTUzOTU3NDk0NnwxMTc3NzktMTUzOTU3NDk3OXw0MTc4OTMtMTUzOTU3NTIzNHw1MzkwMDAtMTUzOTU3NTI0MHw1ODYxMDctMTUzOTU3NTMwNXwxMDEzODEyLTE1Mzk1NzUzMDl8NjY3NzgxLTE1Mzk1NzUzODh8OTkyNjQ4LTE1Mzk1NzUzOTN8MTAxNDQ3My0xNTM5NTgzMDgwfDQ3NzA2Ni0xNTM5NTg0ODYyfDQ4NzE2My0xNTM5NTg1NDA3fDMxODEtMTUzOTU4NTc1Nw; ki_t=1536828435726%3B1539564998292%3B1539585775994%3B7%3B70; GEO_LOC=1%7C0%7C0%7C0%7C%7C%7C%7C%7C%7C%7C%7C%7C14.5955%7C120.9721; it-nfd=; GUID=ccbe0777-42cc-8b4d-e210-49c916177479-1539587478217; __utma=78733387.2050095233.1536827417.1539574391.1539585409.11; lc_sso6579241=1539638801275; PHPSESSID=p87pueh9e1uhmbod6n5eabmome; ie=430919; pti=%7B%22SE%22%3A%22510%22%2C%22RE%22%3A%2210078%22%7D; _gali=app-emp-phone; pt=%7B%22id%22%3A1%2C%22r%22%3A%22%22%2C%22rd%22%3Anull%2C%22rp%22%3Anull%2C%22e%22%3A%22https%3A//www.weddingwire.com/wedding-venues%22%2C%22ci%22%3Anull%2C%22o%22%3A1%2C%22lr%22%3A%22/vendors/list/group%22%2C%22or%22%3A%22/vendors/item/profile%22%2C%22lt%22%3A1%2C%22ll%22%3A0%7D; _gat=1; _dc_gtm_UA-692627-1=1",
            "Referer: https://www.wedding-spot.com"
        ));
        $contents = curl_exec($curl);
        curl_close($curl);
        return array('html' => $contents);
    }

    public function getPdo()
    {
        if (!$this->db_pdo) {
            if ($this->debug) {
                $this->db_pdo = new PDO(DB_DSN, DB_USER, DB_PWD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            } else {
                $this->db_pdo = new PDO(DB_DSN, DB_USER, DB_PWD);
            }
        }
        return $this->db_pdo;
    }
}