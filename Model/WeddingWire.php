<?php

/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 10/15/2018
 * Time: 9:09 AM
 */
class WeddingWire
{
    public $debug = TRUE;
    protected $db_pdo;

    public function getLocations(){
        $locationApiUrl = 'https://no-services.theknot.com/geo/locations/city/?apiKey=vkq9ckuqn9c7jprn92uwbsjkzmtbk6pdxh9&limit=100';
        $apiKey = 'vkq9ckuqn9c7jprn92uwbsjkzmtbk6pdxh9';
        $html = file_get_html($locationApiUrl, false);

        return json_decode($html, true);

    }

    public function scrapeBand($html, $city, $state){
        $header = $html->find('#storefront-header-info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = $header->find('.testing-location-header', 0)->plaintext;
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if($header->find('.testing-phone-number', 0)){
            $phoneNumber = $header->find('.testing-phone-number', 0)->plaintext;
        }else{
            $phoneNumber = '';
        }
        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit Website') {
                $website = $aLinks[$a]->getAttribute('href');
            }
        }

        $about = $html->find('#about-us-read-more', 0);
        if ($about) {
            $about = $about->plaintext;
        } else {
            $about = '';
        }

        // social icons
        $facebook = '';
        $instagram = '';
        $twitter = '';
        $pinterest = '';
        $yelp = '';
        $socialIcons = $html->find('.vendor-social-icons', 0);
        if ($socialIcons) {
            $socialLinks = $socialIcons->find('a');
            for ($s = 0; $s < count($socialLinks); $s++) {
                $sLink = $socialLinks[$s]->getAttribute('href');
                if (strpos($sLink, 'facebook') !== false) {
                    $facebook = $sLink;
                }
                if (strpos($sLink, 'instagram') !== false) {
                    $instagram = $sLink;
                }
                if (strpos($sLink, 'twitter') !== false) {
                    $twitter = $sLink;
                }
                if (strpos($sLink, 'pinterest') !== false) {
                    $pinterest = $sLink;
                }
                if (strpos($sLink, 'yelp') !== false) {
                    $yelp = $sLink;
                }
            }
        }

        $faq = $html->find('#storefront-section-faq', 0);
        $pricing = $html->find('#storefront-section-pricing', 0);
        $panels = $faq->find('.faq-panel');
        if($pricing){
            $pricingPanels = $pricing->find('.faq-panel');
        }else{
            $pricingPanels = null;
        }

        $receptionPriceRange = '';
        $peakSeason = '';
        $offPeakSeason = '';
        $priceRangeIncludes = array();
        $maxCapacity = '';
        $eventSpaces = '';
        $type = '';
        $style = '';
        $setting = '';
        $receptionSiteFee = array();
        $weddingCateringAvgPricePerPerson = array();
        $rehearsalDinnerCateringAvgPricePerPerson = array();
        $rehearsalDinnerBarServicePricePerPerson = '';
        $guestMinimum = '';

        // PRICING
        for ($p = 0; $p < count($pricingPanels); $p++) {
            $title = trim($pricingPanels[$p]->find('.panel-heading', 0)->plaintext);
            echo $title ."\n";
            if (strpos($title, 'Reception Price Range') !== false) {
                $receptionPriceRange = $title;


                $items = $pricingPanels[$p]->find('.faq-item');
                for ($i = 0; $i < count($items); $i++) {
                    $faqItem = trim($items[$i]->plaintext);
                    $faqItemValue = trim($items[$i]->find('.type', 0)->plaintext);
                    if (strpos($faqItem, 'Peak Season') === false) {
                        $peakSeason = $faqItemValue;
                    }
                    if (strpos($faqItem, 'Off-Peak Season') === false) {
                        $offPeakSeason = $faqItemValue;
                    }
                }
            }

            if ($title == 'Wedding Catering Avg. Price Per Person') {
                $items = $pricingPanels[$p]->find('.faq-item');
                for ($i = 0; $i < count($items); $i++) {
                    $faqItem = trim($items[$i]->find('text', 0)->plaintext);
                    if($faqItem != ''){
                        $weddingCateringAvgPricePerPerson[] = $faqItem . '=' . trim($items[$i]->find('.type', 0)->plaintext);
                    }
                }
            }

            if ($title == 'Rehearsal Dinner Catering Avg. Price Per Person') {
                $items = $pricingPanels[$p]->find('.faq-item');
                for ($i = 0; $i < count($items); $i++) {
                    $faqItem = trim($items[$i]->find('text', 0)->plaintext);
                    $faqItemValue = trim($items[$i]->find('.type', 0)->plaintext);
                    if (strpos($faqItem, 'Rehearsal Dinner Catering Prices Include') === false) {
                        $faqItemTitle = trim($items[$i]->find('text', 0)->plaintext);
                        if($faqItemTitle != ''){
                            $rehearsalDinnerCateringAvgPricePerPerson[] = $faqItemTitle . '=' . $faqItemValue;
                        }
                    }

                }
            }

            if ($title == 'Rehearsal Dinner Bar Service Price Per Person') {
                $items = $pricingPanels[$p]->find('.faq-item');
                for ($i = 0; $i < count($items); $i++) {
                    $faqItem = trim($items[$i]->plaintext);
                    if (strpos($faqItem, 'Bar Service Price Per Person') !== false) {
                        $rehearsalDinnerBarServicePricePerPerson = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                }
            }


        }

        // FAQ
        for ($p = 0; $p < count($panels); $p++) {
            $title = trim($panels[$p]->find('.panel-heading', 0)->plaintext);
            $file = fopen("weddingwire-fields.txt","a");
            fwrite($file, $title."\n");
            fclose($file);

            if ($title == 'Event Services') {
                $items = $panels[$p]->find('.faq-item');
                for ($i = 0; $i < count($items); $i++) {
                    $isOffered = $items[$i]->find('.fa-check', 0);
                    if ($isOffered) {
                        $services[] = trim($items[$i]->find('.core-ellipsis', 0)->plaintext);
                    }
                }

            }

            if ($title == 'Venue Highlights') {
                $items = $panels[$p]->find('.faq-item');
                for ($i = 0; $i < count($items); $i++) {
                    $faqItem = trim($items[$i]->plaintext);
                    if (strpos($faqItem, 'Maximum Capacity') !== false) {
                        $maxCapacity = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                    if (strpos($faqItem, 'Event Spaces') !== false) {
                        $eventSpaces = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                    if (strpos($faqItem, 'Type') !== false) {
                        $type = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                    if (strpos($faqItem, 'Style') !== false) {
                        $style = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                    if (strpos($faqItem, 'Setting') !== false) {
                        $setting = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                    if (strpos($faqItem, 'Guest Minimum') !== false) {
                        $guestMinimum = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                    //  echo $items[$i]->plaintext."\n";
                }
            }

        }

        $data = array(
            'Business_Type' => 'Band',
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Reception_Price_Range' => addslashes(implode(',', $receptionPriceRange)),
            'Maximum_Capacity' => addslashes($maxCapacity),
            'Event_Spaces' => addslashes($eventSpaces),
            'Type' => addslashes($type),
            'Style' => addslashes($style),
            'Setting' => addslashes($setting),
            'Reception_Site_Fee' => addslashes(implode(',', $receptionSiteFee)),
            'Wedding_Catering_Avg_Price_Per_Person' => addslashes(implode(',', $weddingCateringAvgPricePerPerson)),
            'Rehearsal_Dinner_Catering_Avg_Price_Per_Person' => addslashes(implode(',', $rehearsalDinnerCateringAvgPricePerPerson)),
            'Rehearsal_Dinner_Bar_Service_Price_Per_Person' => addslashes($rehearsalDinnerBarServicePricePerPerson),
            'Guest_Minimum' => addslashes($guestMinimum),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => str_replace("'",'',$instagram),
            'Business_Pinterest' => str_replace("'",'',$pinterest),
            'Business_Twitter' => str_replace("'",'',$twitter),
            'Business_Yelp' => str_replace("'",'',$yelp)
        );

   //     $this->insertData($data, 'wedding_wire_band');
    }

    public function scrapeVenue($html, $city, $state){
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = $header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext;
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if($header->find('.testing-phone-number', 0)){
            $phoneNumber = $header->find('.testing-phone-number', 0)->plaintext;
        }else{
            $phoneNumber = '';
        }
        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit Website') {
                $website = $aLinks[$a]->getAttribute('href');
            }
        }

        $about = $html->find('#about-us-read-more', 0);
        if ($about) {
            $about = $about->plaintext;
        } else {
            $about = '';
        }

        // social icons
        $facebook = '';
        $instagram = '';
        $twitter = '';
        $pinterest = '';
        $yelp = '';
        $socialIcons = $html->find('.vendor-social-icons', 0);
        if ($socialIcons) {
            $socialLinks = $socialIcons->find('a');
            for ($s = 0; $s < count($socialLinks); $s++) {
                $sLink = $socialLinks[$s]->getAttribute('href');
                if (strpos($sLink, 'facebook') !== false) {
                    $facebook = $sLink;
                }
                if (strpos($sLink, 'instagram') !== false) {
                    $instagram = $sLink;
                }
                if (strpos($sLink, 'twitter') !== false) {
                    $twitter = $sLink;
                }
                if (strpos($sLink, 'pinterest') !== false) {
                    $pinterest = $sLink;
                }
                if (strpos($sLink, 'yelp') !== false) {
                    $yelp = $sLink;
                }
            }
        }

        $faq = $html->find('#storefront-section-faq', 0);
        $pricing = $html->find('#storefront-section-pricing', 0);
        $panels = $faq->find('.faq-panel');
        if($pricing){
            $pricingPanels = $pricing->find('.faq-panel');
        }else{
            $pricingPanels = null;
        }

        $services = array();
        $maxCapacity = '';
        $eventSpaces = '';
        $type = '';
        $style = '';
        $setting = '';
        $receptionSiteFee = array();
        $weddingCateringAvgPricePerPerson = array();
        $rehearsalDinnerCateringAvgPricePerPerson = array();
        $rehearsalDinnerBarServicePricePerPerson = '';
        $guestMinimum = '';

        // PRICING
        for ($p = 0; $p < count($pricingPanels); $p++) {
            $title = trim($pricingPanels[$p]->find('.panel-heading', 0)->plaintext);
            if ($title == 'Reception Site Fee') {
                $items = $pricingPanels[$p]->find('.faq-item');
                for ($i = 0; $i < count($items); $i++) {
                    $faqItem = trim($items[$i]->plaintext);
                    $faqItemValue = trim($items[$i]->find('.type', 0)->plaintext);
                    if (strpos($faqItem, 'Site Fees Include') === false) {
                        $faqItemTitle = trim($items[$i]->find('text', 0)->plaintext);
                        if($faqItemTitle != ''){
                            $receptionSiteFee[] = trim($faqItemTitle) . '=' . $faqItemValue;
                        }
                    }
                }
            }

            if ($title == 'Wedding Catering Avg. Price Per Person') {
                $items = $pricingPanels[$p]->find('.faq-item');
                for ($i = 0; $i < count($items); $i++) {
                    $faqItem = trim($items[$i]->find('text', 0)->plaintext);
                    if($faqItem != ''){
                        $weddingCateringAvgPricePerPerson[] = $faqItem . '=' . trim($items[$i]->find('.type', 0)->plaintext);
                    }
                }
            }

            if ($title == 'Rehearsal Dinner Catering Avg. Price Per Person') {
                $items = $pricingPanels[$p]->find('.faq-item');
                for ($i = 0; $i < count($items); $i++) {
                    $faqItem = trim($items[$i]->find('text', 0)->plaintext);
                    $faqItemValue = trim($items[$i]->find('.type', 0)->plaintext);
                    if (strpos($faqItem, 'Rehearsal Dinner Catering Prices Include') === false) {
                        $faqItemTitle = trim($items[$i]->find('text', 0)->plaintext);
                        if($faqItemTitle != ''){
                            $rehearsalDinnerCateringAvgPricePerPerson[] = $faqItemTitle . '=' . $faqItemValue;
                        }
                    }

                }
            }

            if ($title == 'Rehearsal Dinner Bar Service Price Per Person') {
                $items = $pricingPanels[$p]->find('.faq-item');
                for ($i = 0; $i < count($items); $i++) {
                    $faqItem = trim($items[$i]->plaintext);
                    if (strpos($faqItem, 'Bar Service Price Per Person') !== false) {
                        $rehearsalDinnerBarServicePricePerPerson = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                }
            }


        }

        // FAQ
        for ($p = 0; $p < count($panels); $p++) {
            $title = trim($panels[$p]->find('.panel-heading', 0)->plaintext);
            $file = fopen("weddingwire-fields.txt","a");
            fwrite($file, $title."\n");
            fclose($file);

            if ($title == 'Event Services') {
                $items = $panels[$p]->find('.faq-item');
                for ($i = 0; $i < count($items); $i++) {
                    $isOffered = $items[$i]->find('.fa-check', 0);
                    if ($isOffered) {
                        $services[] = trim($items[$i]->find('.core-ellipsis', 0)->plaintext);
                    }
                }

            }

            if ($title == 'Venue Highlights') {
                $items = $panels[$p]->find('.faq-item');
                for ($i = 0; $i < count($items); $i++) {
                    $faqItem = trim($items[$i]->plaintext);
                    if (strpos($faqItem, 'Maximum Capacity') !== false) {
                        $maxCapacity = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                    if (strpos($faqItem, 'Event Spaces') !== false) {
                        $eventSpaces = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                    if (strpos($faqItem, 'Type') !== false) {
                        $type = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                    if (strpos($faqItem, 'Style') !== false) {
                        $style = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                    if (strpos($faqItem, 'Setting') !== false) {
                        $setting = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                    if (strpos($faqItem, 'Guest Minimum') !== false) {
                        $guestMinimum = trim($items[$i]->find('.type', 0)->plaintext);
                    }
                    //  echo $items[$i]->plaintext."\n";
                }
            }

        }

        $data = array(
            'Business_Type' => 'Reception Venues',
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Facilities_and_Capacity' => '',
            'Services_Offered' => addslashes(implode(',', $services)),
            'Maximum_Capacity' => addslashes($maxCapacity),
            'Event_Spaces' => addslashes($eventSpaces),
            'Type' => addslashes($type),
            'Style' => addslashes($style),
            'Setting' => addslashes($setting),
            'Reception_Site_Fee' => addslashes(implode(',', $receptionSiteFee)),
            'Wedding_Catering_Avg_Price_Per_Person' => addslashes(implode(',', $weddingCateringAvgPricePerPerson)),
            'Rehearsal_Dinner_Catering_Avg_Price_Per_Person' => addslashes(implode(',', $rehearsalDinnerCateringAvgPricePerPerson)),
            'Rehearsal_Dinner_Bar_Service_Price_Per_Person' => addslashes($rehearsalDinnerBarServicePricePerPerson),
            'Guest_Minimum' => addslashes($guestMinimum),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => str_replace("'",'',$instagram),
            'Business_Pinterest' => str_replace("'",'',$pinterest),
            'Business_Twitter' => str_replace("'",'',$twitter),
            'Business_Yelp' => str_replace("'",'',$yelp)
        );

        $this->insertData($data, 'wedding_wire_venue');
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
            try{
                $sql = "INSERT INTO `$table` SET $insertQry";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            }catch (ErrorException $e){
                $file = fopen("mysql_error_weddingwire.txt","a");
                fwrite($file, $e."\n".$sql."\n");
                fclose($file);
            }

        }
        $pdo = null;
    }


    public function getLastPage($urlGeo){
        $htmlDataList = $this->curlTo($urlGeo);
        $htmlList = str_get_html($htmlDataList['html']);
        $paginationCount = $htmlList->find('.testing-catalog-pagination-links', 1);
        $pagination = $paginationCount->find('.pagination', 0);
        $paginationLi = $pagination->find('li');
        $lastPage = trim($paginationLi[count($paginationLi)-2]->plaintext);
        return $lastPage;
    }

    public function getTotalItems($urlGeo){
        $htmlDataList = $this->curlTo($urlGeo);
        $html = str_get_html($htmlDataList['html']);
        $totalItems = trim($html->find('.directory-results-bar-results', 0)->plaintext);
        $totalItems = preg_replace("/[^0-9]/", "", $totalItems );
        return $totalItems;
    }

    public function curlTo($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36"));
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