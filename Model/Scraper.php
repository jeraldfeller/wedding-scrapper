<?php

/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 10/11/2018
 * Time: 2:05 AM
 */
class Scraper
{
    public $debug = TRUE;
    protected $db_pdo;


    public function getTheKnotLocations(){
        $locationApiUrl = 'https://no-services.theknot.com/geo/locations/city/?apiKey=vkq9ckuqn9c7jprn92uwbsjkzmtbk6pdxh9&limit=100';
        $apiKey = 'vkq9ckuqn9c7jprn92uwbsjkzmtbk6pdxh9';
        $html = file_get_html($locationApiUrl, false);

        return json_decode($html, true);

    }

    public function getTotalTheKnotOffset($url){
        $html = file_get_html($url, false);
        $scripts = $html->find('script');
        if (count($scripts) > 0) {
            for ($s = 0; $s < count($scripts); $s++) {
                if ($scripts[$s]->getAttribute('data-js-react-on-rails-store') == 'vendorsSearchStore') {
                    $scriptInner = $scripts[$s]->innertext;
                    $json = json_decode($scriptInner, true);
                }
            }
        }
        return $json['search']['total'];
    }

    public function getVendors($url, $action){
        $html = file_get_html($url, false);
        $scripts = $html->find('script');
        if (count($scripts) > 0) {
            for ($s = 0; $s < count($scripts); $s++) {
                if ($scripts[$s]->getAttribute('data-js-react-on-rails-store') == 'vendorsSearchStore') {
                    $scriptInner = $scripts[$s]->innertext;
                    $json = json_decode($scriptInner, true);
                }
            }
        }
        $vendors = $json['search']['vendors'];
        for($v = 0; $v < count($vendors); $v++){
            $name = $vendors[$v]['name'];
            switch ($action){
                case 'venue':
                    $this->scrapeDataTheKnotVenue($vendors[$v]);
                    break;
                case 'photography':
                    $this->scrapeDataPhotography($vendors[$v]);
                    break;
                case 'videography':
                    $this->scrapeDataVideoGraphy($vendors[$v]);
                    break;
                case 'dj':
                    $this->scrapeDataDj($vendors[$v]);
                    break;
                case 'salon':
                    $this->scrapeDataSalon($vendors[$v]);
                    break;
                case 'band':
                    $this->scrapeDataBand($vendors[$v]);
                    break;
                case 'florist':
                    $this->scrapeDataFlorist($vendors[$v]);
                    break;
                case 'planners':
                    $this->scrapeDataPlanners($vendors[$v]);
                    break;
                case 'jewelers':
                    $this->scrapeDataJewelers($vendors[$v]);
                    break;
                case 'cakes':
                    $this->scrapeDataCakes($vendors[$v]);
                    break;
                case 'accessory':
                    $this->scrapeDataAccessory($vendors[$v]);
                    break;
                case 'alterations':
                    $this->scrapeDataAlterations($vendors[$v]);
                    break;
                case 'bar':
                    $this->scrapeDataBar($vendors[$v]);
                    break;
                case 'catering':
                    $this->scrapeDataCatering($vendors[$v]);
                    break;
                case 'ceremony-venues':
                    $this->scrapeDataCeremonyVenues($vendors[$v]);
                    break;
                case 'dance':
                    $this->scrapeDataDanceLessons($vendors[$v]);
                    break;
                case 'decor':
                    $this->scrapeDataDecors($vendors[$v]);
                    break;
                case 'dessert':
                    $this->scrapeDataDesserts($vendors[$v]);
                    break;
                case 'soloists':
                    $this->scrapeDataSoloists($vendors[$v]);
                    break;
                case 'favors':
                    $this->scrapeDataFavors($vendors[$v]);
                    break;
                case 'invitations':
                    $this->scrapeDataInvitations($vendors[$v]);
                    break;
                case 'lighting':
                    $this->scrapeDataLightings($vendors[$v]);
                    break;
                case 'menswear':
                    $this->scrapeDataMenswear($vendors[$v]);
                    break;
                case 'officiants':
                    $this->scrapeDataOfficiants($vendors[$v]);
                    break;
                case 'photobooth':
                    $this->scrapeDataPhotobooths($vendors[$v]);
                    break;
                case 'rentals':
                    $this->scrapeDataRentals($vendors[$v]);
                    break;
                case 'staff':
                    $this->scrapeDataStaff($vendors[$v]);
                    break;
                case 'transportation':
                    $this->scrapeDataTransportations($vendors[$v]);
                    break;
                case 'designers':
                    $this->scrapeDataDesigners($vendors[$v]);
                    break;
            }
        }
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
        $sql = "SELECT count(`id`) AS rowCount FROM `$table` WHERE `vendor_id` = '" . $data['vendor_id'] . "'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $return = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($return['rowCount'] == 0) {
            try{
                $sql = "INSERT INTO `$table` SET $insertQry";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            }catch (ErrorException $e){
                $file = fopen("mysql_error.txt","a");
                fwrite($file, $e."\n".$sql."\n");
                fclose($file);
            }

        }
        $pdo = null;
    }

    public function getFacets($facets)
    {
        $list = '';
        $subFacets = $facets;
        foreach ($subFacets as $subRow) {
            $list .= $subRow['name'] . ',';
        }
        return $list;
    }

    public function scrapeDataDesigners($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $weddingActivities = NULL;
        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Wedding Activities':
                    $weddingActivities = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Wedding_Activities' => addslashes(rtrim($weddingActivities, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_designers');
    }

    public function scrapeDataTransportations($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $transportation = NULL;
        $serviceStaff = NULL;
        $weddingActivities = NULL;
        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Transportation':
                    $transportation = $this->getFacets($row['facets']);
                    break;
                case 'Service Staff':
                    $serviceStaff = $this->getFacets($row['facets']);
                    break;
                case 'Wedding Activities':
                    $weddingActivities = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Transportation' => addslashes(rtrim($transportation, ',')),
            'Wedding_Activities' => addslashes(rtrim($weddingActivities, ',')),
            'Service_Staff' => addslashes(rtrim($serviceStaff, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_transportations');
    }

    public function scrapeDataStaff($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $serviceStaff = NULL;
        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Service Staff':
                    $serviceStaff = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Service_Staff' => addslashes(rtrim($serviceStaff, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_staff');
    }

    public function scrapeDataRentals($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $rentalsEquipment = NULL;
        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Rentals & Equipment':
                    $rentalsEquipment = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Rentals_and_Equipment' => addslashes(rtrim($rentalsEquipment, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_rentals');
    }

    public function scrapeDataPhotobooths($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $photoVideo = NULL;
        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Photo & Video':
                    $photoVideo = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Photo_and_Video' => addslashes(rtrim($photoVideo, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_photobooths');
    }



    public function scrapeDataOfficiants($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $ceremonyTypes = NULL;
        $planning = NULL;
        $religiousAffiliations = NULL;
        $weddingActivities = NULL;
        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Planning':
                    $planning = $this->getFacets($row['facets']);
                    break;
                case 'Ceremony Types':
                    $ceremonyTypes = $this->getFacets($row['facets']);
                    break;
                case 'Religious Affiliations':
                    $religiousAffiliations = $this->getFacets($row['facets']);
                    break;
                case 'Wedding Activities':
                    $weddingActivities = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Ceremony_Types' => addslashes(rtrim($ceremonyTypes, ',')),
            'Planning' => addslashes(rtrim($planning, ',')),
            'Religious_Affiliations' => addslashes(rtrim($religiousAffiliations, ',')),
            'Wedding_Activities' => addslashes(rtrim($weddingActivities, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_officiants');
    }

    public function scrapeDataMenswear($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $fashionServices = NULL;
        $menswear = NULL;
        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Fashion Services':
                    $fashionServices = $this->getFacets($row['facets']);
                    break;
                case 'Menswear':
                    $menswear = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Fashion_Services' => addslashes(rtrim($fashionServices, ',')),
            'Menswear' => addslashes(rtrim($menswear, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_menswear');
    }

    public function scrapeDataLightings($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $lighting = NULL;
        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Lighting':
                    $lighting = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Lighting' => addslashes(rtrim($lighting, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_lightings');
    }

    public function scrapeDataInvitations($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $invitationsAndPaperGoods = NULL;


        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Invitations & Paper Goods':
                    $invitationsAndPaperGoods = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Invitations_and_Paper_Goods' => addslashes(rtrim($invitationsAndPaperGoods, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_invitations');
    }

    public function scrapeDataFavors($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $giftsAndFavors = NULL;


        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Gifts & Favors':
                    $instruments = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Gifts_and_Favors' => addslashes(rtrim($giftsAndFavors, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_favors');
    }

    public function scrapeDataSoloists($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $instruments = NULL;
        $musicGenres = NULL;
        $musicServices = NULL;
        $weddingActivities = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Instruments':
                    $instruments = $this->getFacets($row['facets']);
                    break;
                case 'Music Genres':
                    $musicGenres = $this->getFacets($row['facets']);
                    break;
                case 'Music Services':
                    $musicServices = $this->getFacets($row['facets']);
                    break;
                case 'Wedding Activities':
                    $weddingActivities = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Instruments' => addslashes(rtrim($instruments, ',')),
            'Music_Genres' => addslashes(rtrim($musicGenres, ',')),
            'Music_Services' => addslashes(rtrim($musicServices, ',')),
            'Wedding_Activities' => addslashes(rtrim($weddingActivities, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_soloists');
    }



    public function scrapeDataDesserts($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $dietaryOptions = NULL;
        $desserts = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Dietary Options':
                    $dietaryOptions = $this->getFacets($row['facets']);
                    break;
                case 'Desserts':
                    $desserts = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
           fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Dietary_Options' => addslashes(rtrim($dietaryOptions, ',')),
            'Desserts' => addslashes(rtrim($desserts, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_desserts');
    }

    public function scrapeDataDecors($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $decorationsAccents = NULL;
        $rentalsEquipment = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Decorations & Accents':
                    $decorationsAccents = $this->getFacets($row['facets']);
                    break;
                case 'Rentals & Equipment':
                    $rentalsEquipment = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Decorations_Accents' => addslashes(rtrim($decorationsAccents, ',')),
            'Rentals_Equipment' => addslashes(rtrim($rentalsEquipment, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_decors');
    }

    public function scrapeDataDanceLessons($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $musicGenres = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Music Genres':
                    $musicGenres = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Music_Genres' => addslashes(rtrim($musicGenres, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_dance_lessons');
    }


    public function scrapeDataCeremonyVenues($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $guestCapacity = NULL;
        $settings = NULL;
        $ceremonyTypes = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Guest Capacity':
                    $guestCapacity = $this->getFacets($row['facets']);
                    break;
                case 'Settings':
                    $settings = $this->getFacets($row['facets']);
                    break;
                case 'Ceremony Types':
                    $ceremonyTypes = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Guest_Capacity' => addslashes(rtrim($guestCapacity, ',')),
            'Settings' => addslashes(rtrim($settings, ',')),
            'Ceremony_Types' => addslashes(rtrim($ceremonyTypes, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_ceremony_venues');
    }

    public function scrapeDataCatering($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $cuisine = NULL;
        $dietaryOptions = NULL;
        $foodCatering = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Cuisine':
                    $cuisine = $this->getFacets($row['facets']);
                    break;
                case 'Food & Catering':
                    $foodCatering = $this->getFacets($row['facets']);
                    break;
                case 'Dietary Options':
                    $dietaryOptions = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Cuisine' => addslashes(rtrim($cuisine, ',')),
            'Dietary_Options' => addslashes(rtrim($dietaryOptions, ',')),
            'Foods_Catering' => addslashes(rtrim($foodCatering, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_catering');
    }

    public function scrapeDataBar($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $barsDrinks = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Bar & Drinks':
                    $barsDrinks = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Bars_Drinks' => addslashes(rtrim($barsDrinks, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_bars');
    }

    public function scrapeDataAlterations($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $fashionServices = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Fashion Services':
                    $fashionServices = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Fashion_Services' => addslashes(rtrim($fashionServices, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_alterations');
    }

    public function scrapeDataAccessory($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $accessories = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Accessories':
                    $accessories = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Accessories' => addslashes(rtrim($accessories, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_accessory');
    }


    public function scrapeDataCakes($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $cakeDesserts = NULL;
        $dietaryOptions = NULL;


        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Cakes & Desserts':
                    $cakeDesserts = $this->getFacets($row['facets']);
                    break;
                case 'Dietary Options':
                    $dietaryOptions = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Cakes_Desserts' => addslashes(rtrim($cakeDesserts, ',')),
            'Dietary_Options' => addslashes(rtrim($dietaryOptions, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_cakes');
    }

    public function scrapeDataJewelers($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $rings = NULL;
        $weddingJewelry = NULL;
        $jewelryCollections = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Rings':
                    $rings = $this->getFacets($row['facets']);
                    break;
                case 'Wedding Jewelry':
                    $weddingJewelry = $this->getFacets($row['facets']);
                    break;
                case 'Jewelry Collections':
                    $jewelryCollections = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Rings' => addslashes(rtrim($rings, ',')),
            'Wedding_Jewelry' => addslashes(rtrim($weddingJewelry, ',')),
            'Jewelry_Collections' => addslashes(rtrim($jewelryCollections, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_jewelry');
    }

    public function scrapeDataPlanners($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $destinationWeddings = NULL;
        $planning = NULL;
        $weddingActivities = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Destination Weddings':
                    $destinationWeddings = $this->getFacets($row['facets']);
                    break;
                case 'Planning':
                    $planning = $this->getFacets($row['facets']);
                    break;
                case 'Wedding Activities':
                    $weddingActivities = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Destination_Weddings' => addslashes(rtrim($destinationWeddings, ',')),
            'Planning' => addslashes(rtrim($planning, ',')),
            'Wedding_Activities' => addslashes(rtrim($weddingActivities, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_planner');
    }

    public function scrapeDataFlorist($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $decorationsAccents = NULL;
        $flowerArrangements = NULL;
        $weddingActivities = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Decorations & Accents':
                    $decorationsAccents = $this->getFacets($row['facets']);
                    break;
                case 'Flower Arrangements':
                    $flowerArrangements = $this->getFacets($row['facets']);
                    break;
                case 'Wedding Activities':
                    $weddingActivities = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Decorations_Accents' => addslashes(rtrim($decorationsAccents, ',')),
            'Flower_Arrangements' => addslashes(rtrim($flowerArrangements, ',')),
            'Wedding_Activities' => addslashes(rtrim($weddingActivities, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_florist');
    }

    public function scrapeDataBand($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $instruments = NULL;
        $musicGenres = NULL;
        $musicServices = NULL;
        $weddingActivities = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Instruments':
                    $instruments = $this->getFacets($row['facets']);
                    break;
                case 'Music Genres':
                    $musicGenres = $this->getFacets($row['facets']);
                    break;
                case 'Music Services':
                    $musicServices = $this->getFacets($row['facets']);
                    break;
                case 'Wedding Activities':
                    $weddingActivities = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Instruments' => addslashes(rtrim($instruments, ',')),
            'Music_Genres' => addslashes(rtrim($musicGenres, ',')),
            'Music_Services' => addslashes(rtrim($musicServices, ',')),
            'Wedding_Activities' => addslashes(rtrim($weddingActivities, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_band');
    }

    public function scrapeDataSalon($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $beauty = NULL;
        $fashionServices = NULL;
        $gownCollections = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Beauty':
                    $beauty = $this->getFacets($row['facets']);
                    break;
                case 'Fashion Services':
                    $fashionServices = $this->getFacets($row['facets']);
                    break;
                case 'Gown Collections':
                    $gownCollections = $this->getFacets($row['facets']);;
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Beauty' => addslashes(rtrim($beauty, ',')),
            'Fashion_Services' => addslashes(rtrim($fashionServices, ',')),
            'Gown_Collections' => addslashes(rtrim($gownCollections, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_salon');
    }

    public function scrapeDataDj($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $equipment = NULL;
        $musicGenres = NULL;
        $musicServices = NULL;
        $weddingActivities = NULL;

        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Equipment':
                    $equipment = $this->getFacets($row['facets']);
                    break;
                case 'Music Genres':
                    $musicGenres = $this->getFacets($row['facets']);
                    break;
                case 'Music Services':
                    $musicServices = $this->getFacets($row['facets']);;
                    break;
                case 'Wedding Activities':
                    $weddingActivities = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Equipment' => addslashes(rtrim($equipment, ',')),
            'Music_Genres' => addslashes(rtrim($musicGenres, ',')),
            'Music_Services' => addslashes(rtrim($musicServices, ',')),
            'Wedding_Activities' => addslashes(rtrim($weddingActivities, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_dj');
    }


    public function scrapeDataVideoGraphy($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];


        // facets
// Amenities

        $facets = $vendor['facets'];
        $photoShootTypes = NULL;
        $photoVideo = NULL;
        $photoVideoStyles = NULL;
        $weddingActivities = NULL;
        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Photo Shoot Types':
                    $photoShootTypes = $this->getFacets($row['facets']);
                    break;
                case 'Photo & Video':
                    $photoVideo = $this->getFacets($row['facets']);
                    break;
                case 'Photo & Video Styles':
                    $photoVideoStyles = $this->getFacets($row['facets']);
                    break;
                case 'Wedding Activities':
                    $weddingActivities = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Photo_Shoot_Types' => addslashes(rtrim($photoShootTypes, ',')),
            'Photo_Video' => addslashes(rtrim($photoVideo, ',')),
            'Photo_Video_Styles' => addslashes(rtrim($photoVideoStyles, ',')),
            'Wedding_Activities' => addslashes(rtrim($weddingActivities, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_videography');
    }

    public function scrapeDataPhotography($vendor){
        // get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];

        // facets
// Amenities

        $facets = $vendor['facets'];
        $photoShootTypes = NULL;
        $photoVideo = NULL;
        $photoVideoStyles = NULL;
        $weddingActivities = NULL;
        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Photo Shoot Types':
                    $photoShootTypes = $this->getFacets($row['facets']);
                    break;
                case 'Photo & Video':
                    $photoVideo = $this->getFacets($row['facets']);
                    break;
                case 'Photo & Video Styles':
                    $photoVideoStyles = $this->getFacets($row['facets']);
                    break;
                case 'Wedding Activities':
                    $weddingActivities = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Details' => NULL,
            'Photo_Shoot_Types' => addslashes(rtrim($photoShootTypes, ',')),
            'Photo_Video' => addslashes($photoVideo),
            'Photo_Video_Styles' => addslashes(rtrim($photoVideoStyles, ',')),
            'Wedding_Activities' => addslashes(rtrim($weddingActivities, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_photography');
    }

    public function scrapeDataTheKnotVenue($vendor)
    {
// get vendor address
        $locationAddress = $vendor['location']['address'];
        $address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
        $city = $locationAddress['city'];
        $state = $locationAddress['state'];
        $zip = $locationAddress['postalCode'];

// facets
// Amenities

        $facets = $vendor['facets'];
        $amenities = NULL;
        $ceremonyTypes = NULL;
        $settings = NULL;
        $venueServiceOfferings = NULL;
        $guestCapacity = NULL;
        foreach ($facets as $row) {
            switch ($row['name']) {
                case 'Wedding Venue Amenities':
                    $amenities = $this->getFacets($row['facets']);
                    break;
                case 'Ceremony Types':
                    $ceremonyTypes = $this->getFacets($row['facets']);
                    break;
                case 'Guest Capacity':
                    $guestCapacity = $this->getFacets($row['facets']);
                    break;
                case 'Settings':
                    $settings = $this->getFacets($row['facets']);
                    break;
                case 'Venue Service Offerings':
                    $venueServiceOfferings = $this->getFacets($row['facets']);
                    break;
            }
        }

// social media
        $socialMedia = $vendor['socialMedia'];
        $facebook = NULL;
        $twitter = NULL;
        $instagram = NULL;
        $pinterest = NULL;
        $yelp = NULL;
        $file = fopen("social.txt","a");
        foreach ($socialMedia as $row) {
            fwrite($file, $row['code']."\n");
            if(strpos($row['code'], 'YELP') !== false){
                $yelp = $row['value'];
            }
            switch ($row['code']) {
                case 'FBURL':
                    $facebook = $row['value'];
                    break;
                case 'TWITTERUSERNAME':
                    $twitter = $row['value'];
                    break;
                case 'INSTAGRAMUSERNAME':
                    $instagram = $row['value'];
                    break;
                case 'PINTERESTUSERNAME':
                    $pinterest = $row['value'];
                    break;
            }
        }
        fclose($file);

        $data = array(
            'vendor_id' => $vendor['id'],
            'Business_Name' => addslashes($vendor['name']),
            'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
            'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
            'Business_Address' => addslashes($address),
            'Business_City' => addslashes($city),
            'Business_State' => $state,
            'Business_Zip_Code' => $zip,
            'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
            'Amenities_Details' => addslashes(rtrim($amenities, ',')),
            'Ceremony_Types' => addslashes(rtrim($ceremonyTypes, ',')),
            'Guest_Capacity' => addslashes($guestCapacity),
            'Settings' => addslashes(rtrim($settings, ',')),
            'Venue_Service_Offerings' => addslashes(rtrim($venueServiceOfferings, ',')),
            'Business_Facebook' => str_replace("'",'',$facebook),
            'Business_Instagram' => addslashes($instagram),
            'Business_Twitter' => addslashes($twitter),
            'Business_Pinterest' => addslashes($pinterest),
            'Business_Yelp' => addslashes($yelp)
        );

        $this->insertData($data, 'the_knot_venue');

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