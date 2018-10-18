<?php

/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 10/15/2018
 * Time: 9:09 AM
 */
class WeddingWireNew
{
    public $debug = TRUE;
    protected $db_pdo;

    public function getLocations()
    {
        $locationApiUrl = 'https://no-services.theknot.com/geo/locations/city/?apiKey=vkq9ckuqn9c7jprn92uwbsjkzmtbk6pdxh9&limit=100';
        $apiKey = 'vkq9ckuqn9c7jprn92uwbsjkzmtbk6pdxh9';
        $html = file_get_html($locationApiUrl, false);

        return json_decode($html, true);

    }


    public function scrapePlanners($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $weddingEvents = '';
        $planningServices = '';
        $weddingTypes = '';
        $numberOfPlanners = '';
        $pricing = array();
        $dayOfWeddingCoordinationPricing = '';
        $weddingPlanningPricing = '';




        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);

                if (strpos($question, 'price for') !== false) {
                    if (strpos($question, 'starting price for day-of wedding coordination') === false && strpos($question, 'starting price for full wedding planning') === false) {
                        $pricing[] = $question . ' = ' . $questionValue;
                    }
                }

                if (strpos($question, 'wedding events do you provide') !== false) {
                    $weddingEvents = $questionValue;
                }
                if (strpos($question, 'wedding planning services') !== false) {
                    $planningServices = $questionValue;
                }
                if (strpos($question, 'How many planners are on your team') !== false) {
                    $numberOfPlanners = $questionValue;
                }

                if (strpos($question, 'starting price for day-of wedding coordination') !== false) {
                    $dayOfWeddingCoordinationPricing = $questionValue;
                }
                if (strpos($question, 'starting price for full wedding planning') !== false) {
                    $weddingPlanningPricing = $questionValue;
                }
                if (strpos($question, 'weddings to you have experience planning') !== false) {
                    $weddingTypes = $questionValue;
                }
            }
        }

        $data = array(
            'Business_Type' => 'Wedding Planners',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Wedding_Events' => addslashes($weddingEvents),
            'Planning_Services' => addslashes($planningServices),
            'Wedding_Types' => addslashes($weddingTypes),
            'Numbers_Of_Planners' => addslashes($numberOfPlanners),
            'Pricing' => implode(',', $pricing),
            'Day_Of_Wedding_Coordination_Pricing' => addslashes($dayOfWeddingCoordinationPricing),
            'Wedding_Planning_Pricing' => addslashes($weddingPlanningPricing),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_wedding_planners');

    }

    public function scrapeCakes($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $weddingCakesPricing = array();
        $desserts = '';
        $cakeServices = '';
        $cakeItems = '';
        $dietaryNeeds = '';
        $specialIngredients = '';
        $stateLicenses = '';


        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'starting price for') !== false) {
                    $weddingCakesPricing[] = $question . ' = ' . $questionValue;
                }


                if (strpos($question, 'types of desserts') !== false) {
                    $desserts = $questionValue;
                }
                if (strpos($question, 'What services do you provide') !== false) {
                    $cakeServices = $questionValue;
                }
                if (strpos($question, 'cake items') !== false) {
                    $cakeItems = $questionValue;
                }

                if (strpos($question, 'dietary needs') !== false) {
                    $dietaryNeeds = $questionValue;
                }
                if (strpos($question, 'special ingredients') !== false) {
                    $specialIngredients = $questionValue;
                }
                if (strpos($question, 'Are you licensed by the state health department') !== false) {
                    $stateLicenses = $questionValue;
                }
            }
        }

        $data = array(
            'Business_Type' => 'Cakes',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Wedding_Cakes_Pricing' => implode(',',$weddingCakesPricing),
            'Desserts' => addslashes($desserts),
            'Cake_Services' => addslashes($cakeServices),
            'Cake_Items' => addslashes($cakeItems),
            'Dietary_Needs' => addslashes($dietaryNeeds),
            'Special_Ingredients' => addslashes($specialIngredients),
            'State_Licenses' => addslashes($stateLicenses),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_cakes');

    }

    public function scrapeVideographer($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $weddingVideographyPricing = '';
        $hasPackagePrice = false;
        $weddingVideographyPackagePricing = '';
        $weddingVideographyServices = '';
        $videographyStyle = '';
        $weddingEvents = '';
        $videographyServices = '';
        $finalVideoFormats = '';
        $s = 1;

        $weddingPhotographyPricing = '';
        $weddingPhotographyPackagePricing = '';
        $photographyStyle = '';
        $photographyServices = '';
        $photographyItems = '';

        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'starting price for') !== false) {
                    if (strpos($question, 'starting price for your wedding photography services') === false && strpos($question, 'What is the price of your most popular wedding package') === false) {
                        $pricing[] = $question . ' = ' . $questionValue;
                    }
                }


                if (strpos($question, 'What is the starting price for your wedding videography services') !== false) {
                    $weddingVideographyPricing = $questionValue;
                }
                if (strpos($question, 'What is the price of your most popular wedding package') !== false) {
                    if($hasPackagePrice == false){
                        $weddingVideographyPackagePricing = $questionValue;
                        $hasPackagePrice = true;
                        $s++;
                    }
                }
                if (strpos($question, 'Which of the following are included in the price of your most popular wedding package') !== false) {
                    $weddingVideographyServices = $questionValue;
                }

                if (strpos($question, 'primary style') !== false) {
                    $videographyStyle = $questionValue;
                }
                if (strpos($question, 'videography services') !== false) {
                    $videographyServices = $questionValue;
                }
                if (strpos($question, 'final video formats') !== false) {
                    $finalVideoFormats = $questionValue;
                }
                if (strpos($question, 'wedding events') !== false) {
                    $weddingEvents = $questionValue;
                }


                if (strpos($question, 'starting price for your wedding photography services') !== false) {
                    $weddingPhotographyPricing = $questionValue;
                }
                if (strpos($question, 'What is the price of your most popular wedding package') !== false) {
                    if($s == 2){
                        $weddingPhotographyPackagePricing = $questionValue;
                    }
                }
                if (strpos($question, 'photographic style') !== false) {
                    $photographyStyle = $questionValue;
                }
                if (strpos($question, 'photography services') !== false) {
                    $photographyServices = $questionValue;
                }
                if (strpos($question, 'photography items') !== false) {
                    $photographyItems = $questionValue;
                }

            }
        }

        $data = array(
            'Business_Type' => 'Videography',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Wedding_Videography_Services' => addslashes($weddingVideographyServices),
            'Wedding_Videography_Pricing' => addslashes($weddingVideographyPricing),
            'Wedding_Videography_Package_Pricing' => addslashes($weddingVideographyPackagePricing),
            'Videography_Styles' => addslashes($videographyStyle),
            'Wedding_Events' => addslashes($weddingEvents),
            'Videography_Services' => addslashes($videographyServices),
            'Final_Video_Formats' => addslashes($finalVideoFormats),
            'Wedding_Photography_Pricing' => addslashes($weddingPhotographyPricing),
            'Wedding_Photography_Package_Pricing' => addslashes($weddingPhotographyPackagePricing),
            'Photography_Styles' => addslashes($photographyStyle),
            'Photography_Services' => addslashes($photographyServices),
            'Photography_Items' => addslashes($photographyItems),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_videography');

    }


    public function scrapePhotographer($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $pricing = array();
        $weddingPhotographyPricing = '';
        $weddingPhotographyPackagePricing = '';
        $photographyStyle = '';
        $photographyServices = '';
        $photographyItems = '';
        $hasPackagePrice = false;

        $weddingVideographyPricing = '';
        $weddingVideographyPackagePricing = '';
        $weddingVideographyServices = '';
        $videographyStyle = '';
        $videographyServices = '';
        $finalVideoFormats = '';

        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            $s = 1;
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'starting price for') !== false) {
                    if (strpos($question, 'starting price for your wedding photography services') === false && strpos($question, 'What is the price of your most popular wedding package') === false) {
                        $pricing[] = $question . ' = ' . $questionValue;
                    }
                }

                if (strpos($question, 'starting price for your wedding photography services') !== false) {
                    $weddingPhotographyPricing = $questionValue;
                }
                if (strpos($question, 'What is the price of your most popular wedding package') !== false) {
                    if($hasPackagePrice == false){
                        $weddingPhotographyPackagePricing = $questionValue;
                        $hasPackagePrice = true;
                        $s++;
                    }
                }
                if (strpos($question, 'photographic style') !== false) {
                    $photographyStyle = $questionValue;
                }
                if (strpos($question, 'photography services') !== false) {
                    $photographyServices = $questionValue;
                }
                if (strpos($question, 'photography items') !== false) {
                    $photographyItems = $questionValue;
                }


                if (strpos($question, 'What is the starting price for your wedding videography services') !== false) {
                    $weddingVideographyPricing = $questionValue;
                }
                if (strpos($question, 'What is the price of your most popular wedding package') !== false) {
                    if($s == 2){
                        $weddingVideographyPackagePricing = $questionValue;
                    }
                }
                if (strpos($question, 'Which of the following are included in the price of your most popular wedding package') !== false) {
                    $weddingVideographyServices = $questionValue;
                }

                if (strpos($question, 'primary style') !== false) {
                    $videographyStyle = $questionValue;
                }
                if (strpos($question, 'videography services') !== false) {
                    $videographyServices = $questionValue;
                }
                if (strpos($question, 'final video formats') !== false) {
                    $finalVideoFormats = $questionValue;
                }

            }
        }

        $data = array(
            'Business_Type' => 'Photography',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Pricing' => implode(',', $pricing),
            'Wedding_Photography_Pricing' => addslashes($weddingPhotographyPricing),
            'Wedding_Photography_Package_Pricing' => addslashes($weddingPhotographyPackagePricing),
            'Photography_Styles' => addslashes($photographyStyle),
            'Photography_Services' => addslashes($photographyServices),
            'Photography_Items' => addslashes($photographyItems),
            'Wedding_Videography_Services' => addslashes($weddingVideographyServices),
            'Wedding_Videography_Pricing' => addslashes($weddingVideographyPricing),
            'Wedding_Videography_Package_Pricing' => addslashes($weddingVideographyPackagePricing),
            'Videography_Styles' => addslashes($videographyStyle),
            'Videography_Services' => addslashes($videographyServices),
            'Final_Video_Formats' => addslashes($finalVideoFormats),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_photography');

    }


    public function scrapeOfficiants($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);


        $officiantServices = '';//
        $services = '';
        $religions = '';//
        $avgCeremonyLength = '';//
        $stateLicenses = '';//
        $languageSpoken = '';//
        $pricing = array();
        $weddingServicesPricing = ''; //
        $ceremonyRehearsalPricing = ''; //


        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'types of weddings and events') !== false) {
                    $officiantServices = $questionValue;
                }
                if (strpos($question, 'religious affiliations') !== false) {
                    $religions = $questionValue;
                }
                if (strpos($question, 'how long are wedding ceremonies') !== false) {
                    $avgCeremonyLength = $questionValue;
                }
                if (strpos($question, 'states are you licensed') !== false) {
                    $stateLicenses = $questionValue;
                }
                if (strpos($question, 'languages do you speak') !== false) {
                    $languageSpoken = $questionValue;
                }
                if (strpos($question, 'starting price for your wedding services') !== false) {
                    $weddingServicesPricing = $questionValue;
                }
                if (strpos($question, 'starting price for ceremony rehearsals') !== false) {
                    $ceremonyRehearsalPricing = $questionValue;
                }
                if (strpos($question, 'services do you provide') !== false) {
                    $services = $questionValue;
                }
                if (strpos($question, 'starting price for') !== false) {
                    if (strpos($question, 'What is the starting price for your wedding services') === false && strpos($question, 'What is the starting price for ceremony rehearsals') === false) {
                        $pricing[] = $question . ' = ' . $questionValue;
                    }
                }

            }
        }

        $data = array(
            'Business_Type' => 'Officiant',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Officiant_Services' => addslashes($officiantServices),
            'Services' => addslashes($services),
            'Religions' => addslashes($religions),
            'Avg_Ceremony_Length' => addslashes($avgCeremonyLength),
            'State_Licenses' => addslashes($stateLicenses),
            'Language_Spoken' => addslashes($languageSpoken),
            'Pricing' => implode(',', $pricing),
            'Wedding_Services_Pricing' => addslashes($weddingServicesPricing),
            'Ceremony_Rehearsal_Pricing' => addslashes($ceremonyRehearsalPricing),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_officiants');

    }

    public function scrapeLightingDecor($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $weddingLightingPrice = array();
        $weddingLightingPackagePrice = '';
        $lightingServices = '';
        $decorServices = '';
        $decorItems = '';


        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'price of your most popular wedding lighting package') !== false) {
                    $weddingLightingPackagePrice = $questionValue;
                }
                if (strpos($question, 'starting price') !== false) {
                    $weddingLightingPrice[] = $question . ' = ' . $questionValue;
                }
                if (strpos($question, 'lighting services') !== false) {
                    $lightingServices = $questionValue;
                }
                if (strpos($question, 'decor services') !== false) {
                    $decorServices = $questionValue;
                }
                if (strpos($question, 'decor items') !== false) {
                    $decorItems = $questionValue;
                }
            }
        }

        $data = array(
            'Business_Type' => 'Lighting and Decor',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Wedding_Lighting_Price' => implode(',', $weddingLightingPrice),
            'Wedding_Lighting_Package_Pricing' => addslashes($weddingLightingPackagePrice),
            'Lighting_Services' => addslashes($lightingServices),
            'Decor_Services' => addslashes($decorServices),
            'Decor_Items' => addslashes($decorItems),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_lighting_decor');

    }

    public function scrapeFlorists($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $tableArrangementPricing = array();
        $weddingFloralPricing = '';
        $floralStyle = '';
        $floralServices = '';
        $floralArrangements = '';
        $bouquetPricing = array();
        $boutonnierePricing = '';

        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'table arrangement') !== false) {
                    $tableArrangementPricing[] = $question . ' = ' . $questionValue;
                }

                if (strpos($question, 'price for a bridal bouquet') !== false) {
                    $bouquetPricing[] = 'Bridal Bouquet = ' . $questionValue;
                }
                if (strpos($question, 'price for a bridal bouquet') !== false) {
                    $bouquetPricing[] = 'Bridesmaid Bouquet = ' . $questionValue;
                }
                if (strpos($question, 'price for a boutonniere') !== false) {
                    $boutonnierePricing = $questionValue;
                }
                if (strpos($question, 'types of arrangements') !== false) {
                    $floralArrangements = $questionValue;
                }
                if (strpos($question, 'pay for your wedding floral services') !== false) {
                    $weddingFloralPricing = $questionValue;
                }
                if (strpos($question, 'services do you provide') !== false) {
                    $floralServices = $questionValue;
                }
                if (strpos($question, 'floral designs') !== false) {
                    $floralStyle = $questionValue;
                }

            }
        }

        $data = array(
            'Business_Type' => 'Flowers',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Table_Arrangements_Pricing' => implode(',', $tableArrangementPricing),
            'Wedding_Floral_Pricing' => addslashes($weddingFloralPricing),
            'Floral_Style' => addslashes($floralStyle),
            'Floral_Services' => addslashes($floralServices),
            'Floral_Arrangements' => addslashes($floralArrangements),
            'Bouquet_Pricing' => implode(',', $bouquetPricing),
            'Boutonniere_Pricing' => addslashes($boutonnierePricing),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_flowers');

    }

    public function scrapeFavors($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $productTypes = '';
        $favorsGiftsServices = '';
        $avgTurnAroundTime = '';
        $orderInformation = array();
        $returnPolicy = '';
        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'order') !== false) {
                    $orderInformation[] = $question . ' = ' . $questionValue;
                }

                if (strpos($question, 'product offerings') !== false) {
                    $productTypes = $questionValue;
                }
                if (strpos($question, 'services do you provide') !== false) {
                    $favorsGiftsServices = $questionValue;
                }
                if (strpos($question, 'average turnaround time') !== false) {
                    $avgTurnAroundTime = $questionValue;
                }
                if (strpos($question, 'return policy') !== false) {
                    $returnPolicy = $questionValue;
                }
            }
        }

        $data = array(
            'Business_Type' => 'Favors and Gifts',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Product_Types' => addslashes($productTypes),
            'Favors_Gift_Services' => addslashes($favorsGiftsServices),
            'Avg_Turnaround_Time' => addslashes($avgTurnAroundTime),
            'Order_Information' => implode(',', $orderInformation),
            'Return_Policy' => addslashes($returnPolicy),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_favors_gifts');

    }

    public function scrapePhotoBooths($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $eventRentalsPricing = array();
        $eventServices = '';
        $eventItems = '';
        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'starting price for') !== false) {
                    $eventRentalsPricing[] = $question . ' = ' . $questionValue;
                }

                if (strpos($question, 'event items are available') !== false) {
                    $eventItems = $questionValue;
                }
                if (strpos($question, 'event rental services') !== false) {
                    $eventServices = $questionValue;
                }
            }
        }

        $data = array(
            'Business_Type' => 'Photo Booths',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Event_Rentals_Pricing' => implode(',', $eventRentalsPricing),
            'Event_Services' => addslashes($eventServices),
            'Event_Items' => addslashes($eventItems),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_photo_booths');

    }

    public function scrapeEventRentals($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $eventRentalsPricing = array();
        $eventServices = '';
        $eventItems = '';
        $foodBeverageItems = '';
        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'starting price') !== false) {
                    $eventRentalsPricing[] = $question . ' = ' . $questionValue;
                }

                if (strpos($question, 'event items are available') !== false) {
                    $eventItems = $questionValue;
                }
                if (strpos($question, 'event rental services') !== false) {
                    $eventServices = $questionValue;
                }
                if (strpos($question, 'food and beverage items') !== false) {
                    $foodBeverageItems = $questionValue;
                }
            }
        }

        $data = array(
            'Business_Type' => 'Event Rentals',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Event_Rentals_Pricing' => implode(',', $eventRentalsPricing),
            'Event_Services' => addslashes($eventServices),
            'Event_Items' => addslashes($eventItems),
            'Food_Beverage_Items' => addslashes($foodBeverageItems),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_event_rentals');

    }

    public function scrapeDresses($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $pricing = array();
        $fullPriceWeddingGowns = '';
        $alterationsPricing = '';
        $clientele = '';
        $dressAndAtireItems = '';
        $avgTurnAroundTime = '';
        $newInventory = '';
        $returnPolicy = '';
        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'price ranges') !== false) {
                    if (strpos($question, 'full-priced wedding gowns') === false) {
                        $pricing[] = $question . ' = ' . $questionValue;
                    }
                }

                if (strpos($question, 'Clientele') !== false) {
                    $clientele = $questionValue;
                }
                if (strpos($question, 'full-priced wedding gowns') !== false) {
                    $fullPriceWeddingGowns = $questionValue;
                }
                if (strpos($question, 'average turnaround time for a bridal gown') !== false) {
                    $avgTurnAroundTime = $questionValue;
                }
                if (strpos($question, 'new bridal gowns') !== false) {
                    $newInventory = $questionValue;
                }
                if (strpos($question, 'return policy') !== false) {
                    $returnPolicy = $questionValue;
                }
                if (strpos($question, 'following items') !== false) {
                    $dressAndAtireItems = $questionValue;
                }
                if (strpos($question, 'price dress alterations') !== false) {
                    $alterationsPricing = $questionValue;
                }


            }
        }

        $data = array(
            'Business_Type' => 'Dress & Attire',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Pricing' => implode(',', $pricing),
            'Full_Price_Wedding_Gowns' => addslashes($fullPriceWeddingGowns),
            'Alterations_Pricing' => addslashes($alterationsPricing),
            'Clientele' => addslashes($clientele),
            'Dress_And_Attire_Items' => addslashes($dressAndAtireItems),
            'Avg_Turnaround_Time_Bridal_Gown' => addslashes($avgTurnAroundTime),
            'New_Inventory' => addslashes($newInventory),
            'Return_Policy' => addslashes($returnPolicy),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_dress_attire');

    }

    public function scrapeDj($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $servicesOffered = '';
        $djPricing = '';
        $djPackagePricing = '';
        $musicGenres = '';
        $additionalEquipment = '';
        $lightingServices = '';
        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);

                if (strpos($question, 'What is the price of your most popular wedding package') !== false) {
                    $djPackagePricing = $questionValue;
                }
                if (strpos($question, 'starting price for wedding DJ services') !== false) {
                    $djPricing = $questionValue;
                }
                if (strpos($question, 'What services') !== false) {
                    $servicesOffered = $questionValue;
                }
                if (strpos($question, 'genres') !== false) {
                    $musicGenres = $questionValue;
                }
                if (strpos($question, 'additional equipment') !== false) {
                    $additionalEquipment = $questionValue;
                }
                if (strpos($question, 'starting price for uplighting') !== false) {
                    $lightingServices = $questionValue;
                }


            }
        }

        $data = array(
            'Business_Type' => 'Dj',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Services_Offered' => addslashes($servicesOffered),
            'Dj_Pricing' => addslashes($djPricing),
            'Dj_Package_Pricing' => addslashes($djPackagePricing),
            'Music_Genres' => addslashes($musicGenres),
            'Additional_Equipment' => addslashes($additionalEquipment),
            'Lighting_Services' => addslashes($lightingServices),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_dj');

    }

    public function scrapeCeremonyMusic($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }


        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $pricing = array();
        $ceremonyMusicPricing = '';
        $ceremonyMusicPackagePricing = '';
        $arrangements = '';
        $numberOfMusicians = '';
        $instruments = '';
        $musicServices = '';
        $musicGenres = '';
        $bandItems = array();


        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);

                if (strpos($question, 'starting price for') !== false && strpos($question, 'What is the starting price for wedding ceremony music') === false && strpos($question, 'What is the price of your most popular wedding package') === false) {
                    $pricing[] = $question . ' = ' . $questionValue;
                }
                if (strpos($question, 'What is the starting price for wedding ceremony music') !== false) {
                    $ceremonyMusicPricing = $questionValue;
                }
                if (strpos($question, 'What is the price of your most popular wedding package') !== false) {
                    $ceremonyMusicPackagePricing = $questionValue;
                }
                if (strpos($question, 'ceremony music arrangements') !== false) {
                    $arrangements = $questionValue;
                }
                if (strpos($question, 'many musicians are in your band(s)') !== false) {
                    $numberOfMusicians = $questionValue;
                }
                if (strpos($question, 'What instruments') !== false) {
                    $instruments = $questionValue;
                }
                if (strpos($question, "What services do you provide") !== false) {
                    $musicServices = $questionValue;
                }
                if (strpos($question, 'genres') !== false) {
                    $musicGenres = $questionValue;
                }
                if (strpos($question, 'additional equipment') !== false) {
                    $bandItems[] = $questionValue;
                }
                if (strpos($question, 'What items are available') !== false) {
                    $bandItems[] = $questionValue;
                }


            }
        }

        $data = array(
            'Business_Type' => 'Ceremony Music',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Pricing' => implode(',', $pricing),
            'Ceremony_Music_Pricing' => addslashes($ceremonyMusicPricing),
            'Ceremony_Music_Package_Pricing' => addslashes($ceremonyMusicPackagePricing),
            'Arrangements' => addslashes($arrangements),
            'Number_Of_Musicians' => addslashes($numberOfMusicians),
            'Instruments' => addslashes($instruments),
            'Music_Services' => addslashes($musicServices),
            'Music_Genres' => addslashes($musicGenres),
            'Band_Items' => implode(',', $bandItems),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_ceremony_music');

    }

    public function scrapeCaterers($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }

        $history = '';
        $about = $html->find('.storefrontInfo__text', 0);
        if ($about) {
            for ($a = 0; $a < count($about->find('text')); $a++) {
                $aboutTitle = trim($about->find('text', $a)->plaintext);
                if ($aboutTitle == 'History') {
                    $history = trim($about->find('text', $a + 1)->plaintext);
                }
            }
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $cuisine = '';
        $pricing = array();
        $plated = '';
        $buffet = '';
        $stations = '';
        $horsdOeuvres = '';
        $cateringPriceIncludes = '';
        $eventServices = '';
        $cateringServices = '';
        $barServices = '';
        $eventItems = '';
        $foodBeverageItems = array();

        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'types of cuisine') !== false) {
                    $cuisine = $questionValue;
                }
                if (strpos($question, 'average catering price per person for plated') !== false) {
                    $plated = $questionValue;
                }
                if (strpos($question, 'average catering price per person for buffet') !== false) {
                    $buffet = $questionValue;
                }
                if (strpos($question, 'average catering price per person for stations') !== false) {
                    $stations = $questionValue;
                }
                if (strpos($question, "average catering price per person for hors d'ouevres") !== false) {
                    $horsdOeuvres = $questionValue;
                }
                if (strpos($question, 'following are included in the cost of your full service wedding catering') !== false) {
                    $cateringPriceIncludes = $questionValue;
                }
                if (strpos($question, 'event services do you provide') !== false) {
                    $eventServices = $questionValue;
                }
                if (strpos($question, 'catering services do you provide') !== false) {
                    $cateringServices = $questionValue;
                }
                if (strpos($question, 'bar services do you provide') !== false) {
                    $barServices = $questionValue;
                }
                if (strpos($question, 'following items can you provide') !== false) {
                    $foodBeverageItems[] = $questionValue;
                }
                if (strpos($question, 'event items are available') !== false) {
                    $eventItems = $questionValue;
                }
                if (strpos($question, 'What is the starting price for') !== false) {
                    $pricing[] = $question . ' = ' . $questionValue;
                }


            }
        }

        $data = array(
            'Business_Type' => 'Caterer',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Cuisine' => addslashes($cuisine),
            'History' => addslashes($history),
            'Pricing' => implode(',', $pricing),
            'Plated' => addslashes($plated),
            'Buffet' => addslashes($buffet),
            'Stations' => addslashes($stations),
            'Hors_dOeuvres' => addslashes($horsdOeuvres),
            'Catering_Price_Includes' => addslashes($cateringPriceIncludes),
            'Event_Services' => addslashes($eventServices),
            'Catering_Services' => addslashes($cateringServices),
            'Bar_Services' => addslashes($barServices),
            'Event_Items' => addslashes($eventItems),
            'Food_Beverage_Items' => implode(',', $foodBeverageItems),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_catering');

    }


    public function scrapeHealthBeauty($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }
        $experience = '';
        $about = $html->find('.storefrontInfo__text', 0);
        if ($about) {
            for ($a = 0; $a < count($about->find('text')); $a++) {
                $aboutTitle = trim($about->find('text', $a)->plaintext);
                if ($aboutTitle == 'Experience') {
                    $experience = trim($about->find('text', $a + 1)->plaintext);
                }
            }
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);


        $servicesOffered = '';
        $pricing = array();
        $beatyServices = '';
        $hairServices = '';
        $makeupServices = '';
        $tanningServices = '';
        $businessInfo = '';


        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'beauty services') !== false) {
                    $servicesOffered = $questionValue;
                }
                if (strpos($question, 'price') !== false) {
                    $pricing[] = $question . ' = ' . $questionValue;
                }
                if (strpos($question, 'beauty services') !== false) {
                    $beatyServices = $questionValue;
                }
                if (strpos($question, 'hair services') !== false) {
                    $hairServices = $questionValue;
                }
                if (strpos($question, 'makeup services') !== false) {
                    $makeupServices = $questionValue;
                }
                if (strpos($question, 'tanning services') !== false) {
                    $tanningServices = $questionValue;
                }
            }
        }
        $data = array(
            'Business_Type' => 'Band',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Experience' => addslashes($experience),
            'Services_Offered' => addslashes($servicesOffered),
            'Pricing' => implode(',', $pricing),
            'Beauty_Services' => addslashes($beatyServices),
            'Hair_Services' => addslashes($hairServices),
            'Makeup_Services' => addslashes($makeupServices),
            'Tanning_Services' => addslashes($tanningServices),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_beauty_health');

    }

    public function scrapeBand($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }

        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $receptionPriceRange = '';
        $peakSeason = '';
        $offPeakSeason = '';
        $priceRangeIncludes = '';
        $bandPackagePricing = '';
        $musicGenres = '';
        $musicServices = '';
        $bandItems = '';

        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'wedding reception during peak season') !== false) {
                    $peakSeason = $questionValue;
                }
                if (strpos($question, 'genres') !== false) {
                    $musicGenres = $questionValue;
                }
                if (strpos($question, 'services') !== false) {
                    $musicServices = $questionValue;
                }
                if (strpos($question, 'items') !== false) {
                    $bandItems = $questionValue;
                }
                if (strpos($question, 'following are included in the price') !== false) {
                    $priceRangeIncludes = $questionValue;
                }
                if (strpos($question, 'price of your popular wedding package') !== false) {
                    $receptionPriceRange = $questionValue;
                }


            }
        }


        $data = array(
            'Business_Type' => 'Band',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Reception_Price_Range' => addslashes($receptionPriceRange),
            'Peak_Season' => addslashes($peakSeason),
            'Off_Peak_Season' => addslashes($offPeakSeason),
            'Price_Range_Includes' => addslashes($priceRangeIncludes),
            'Band_Package_Pricing' => addslashes($bandPackagePricing),
            'Music_Genres' => addslashes($musicGenres),
            'Music_Services' => addslashes($musicServices),
            'Band_Items' => addslashes($bandItems),
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_band');

    }

    public function scrapeVenue($html, $city, $state)
    {
        $vendorId = $html->find('body', 0)->getAttribute('data-id-empresa');
        $header = $html->find('.storefrontHeader__info', 0);
        $businessName = $header->find('h1', 0)->plaintext;
        $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 1)->plaintext);
        if ($address == 'Map') {
            $address = trim($header->find('.storefrontHeaderOnepage__address', 0)->find('text', 0)->plaintext);
        }
        // get city, state, zip from address
        $addressArray = explode(',', $address);
        if (isset($addressArray[count($addressArray) - 1])) {
            $stateZipArray = explode(' ', trim($addressArray[count($addressArray) - 1]));
            if (isset($addressArray[count($addressArray) - 2])) {
                $city = $addressArray[count($addressArray) - 2];
                $state = $stateZipArray[0];
                $zip = $stateZipArray[count($stateZipArray) - 1];
                $address = str_replace(array($city, $state, $zip, ','), array('', '', '', ''), $address);
            } else {
                $zip = '';
            }

        } else {
            $zip = '';
        }


        if ($header->find('.app-emp-phone-txt', 0)) {
            $phoneNumberTemplate = $this->curlTo("https://www.weddingwire.com/emp-ShowTelefonoTrace.php?id_empresa=$vendorId&reduced=/vendors/item/profile")['html'];
            $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumberTemplate);
        } else {
            $phoneNumber = '';
        }

        $aLinks = $header->find('a');
        $website = '';
        for ($a = 0; $a < count($aLinks); $a++) {
            $textLink = trim($aLinks[$a]->plaintext);
            if ($textLink == 'Visit website' || $textLink == 'Visit Website') {
                $websiteContainer = $aLinks[$a]->getAttribute('onclick');
                $websiteDroplet = $this->get_string_between($websiteContainer, '(', ')');
                $websiteArray = explode(',', $websiteDroplet);
                $website = str_replace("'", "", $websiteArray[0]);
            }
        }

        $about = $html->find('.storefrontInfo__text', 0);
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
        $socialIcons = $html->find('.storefrontInfo__socialIcon');
        if ($socialIcons) {
            for ($s = 0; $s < count($socialIcons); $s++) {
                $sLink = $socialIcons[$s]->getAttribute('href');
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

        $faqs = $html->find('#faqs', 0);

        $services = '';
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

        if ($faqs) {
            $faqUl = $faqs->find('.storefrontFaqs__list', 0);
            $faqList = $faqUl->find('li');
            for ($x = 0; $x < count($faqList); $x++) {
                $question = trim($faqList[$x]->find('text', 1)->plaintext);
//            echo $question . "\n";
                $questionValue = trim($faqList[$x]->find('text', 2)->plaintext);
                if (strpos($question, 'wedding receptions during peak season') !== false) {
                    $receptionSiteFee[] = 'peak season = ' . $questionValue;
                }
                if (strpos($question, 'wedding receptions during off-peak season') !== false) {
                    $receptionSiteFee[] = 'off-peak season = ' . $questionValue;
                }
                if (strpos($question, 'starting price per person for bar service') !== false) {
                    $rehearsalDinnerBarServicePricePerPerson = $questionValue;
                }
                if (strpos($question, 'maximum capacity') !== false) {
                    $maxCapacity = $questionValue;
                }
                if (strpos($question, 'minimum number of guests') !== false) {
                    $guestMinimum = $questionValue;
                }
                if (strpos($question, 'event spaces') !== false) {
                    $eventSpaces = $questionValue;
                }
                if (strpos($question, 'Describe your venue') !== false) {
                    $type = $questionValue;
                }
                if (strpos($question, 'settings') !== false) {
                    $setting = $questionValue;
                }
                if (strpos($question, 'services') !== false) {
                    $services = $questionValue;
                }
                if (strpos($question, 'average catering price per person for plated service') !== false) {
                    $weddingCateringAvgPricePerPerson[] = 'plated=' . $questionValue;
                }
                if (strpos($question, 'average catering price per person for buffet service') !== false) {
                    $weddingCateringAvgPricePerPerson[] = 'buffet=' . $questionValue;
                }
                if (strpos($question, 'average catering price per person for stations') !== false) {
                    $weddingCateringAvgPricePerPerson[] = 'stations=' . $questionValue;
                }
                if (strpos($question, "average catering price per person for hors d'ouevres") !== false) {
                    $weddingCateringAvgPricePerPerson[] = 'douevres=' . $questionValue;
                }


                if (strpos($question, 'average dinner catering price per person for plated service') !== false) {
                    $rehearsalDinnerCateringAvgPricePerPerson[] = 'plated=' . $questionValue;
                }
                if (strpos($question, 'average dinner catering price per person for buffet service') !== false) {
                    $rehearsalDinnerCateringAvgPricePerPerson[] = 'buffet=' . $questionValue;
                }
                if (strpos($question, 'average dinner catering price per person for stations') !== false) {
                    $rehearsalDinnerCateringAvgPricePerPerson[] = 'stations=' . $questionValue;
                }
                if (strpos($question, "average dinner catering price per person for hors d'ouevres") !== false) {
                    $rehearsalDinnerCateringAvgPricePerPerson[] = 'douevres=' . $questionValue;
                }

                if (strpos($question, "dinner") !== false) {
                    $file = fopen("weddingwire-question.txt", "a");
                    fwrite($file, $question." = ".$questionValue . "\n");
                    fclose($file);
                }

                if (strpos($question, "rehearsal") !== false) {
                    $file = fopen("weddingwire-question.txt", "a");
                    fwrite($file, $question." = ".$questionValue . "\n");
                    fclose($file);
                }
                if (strpos($question, "style") !== false) {
                    $file = fopen("weddingwire-question.txt", "a");
                    fwrite($file, $question." = ".$questionValue . "\n");
                    fclose($file);
                }

            }
        }


        $data = array(
            'Business_Type' => 'Reception Venues',
            'vendor_id' => $vendorId,
            'Business_Name' => addslashes(trim($businessName)),
            'Business_Phone' => addslashes(trim($phoneNumber)),
            'Business_Website' => addslashes(trim($website)),
            'Business_Address' => addslashes(trim($address)),
            'Business_City' => addslashes(trim($city)),
            'Business_State' => trim($state),
            'Business_Zip_Code' => trim($zip),
            'About' => addslashes(trim($about)),
            'Services_Offered' => addslashes($services),
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
            'Business_Facebook' => str_replace("'", '', $facebook),
            'Business_Instagram' => str_replace("'", '', $instagram),
            'Business_Pinterest' => str_replace("'", '', $pinterest),
            'Business_Twitter' => str_replace("'", '', $twitter),
            'Business_Yelp' => str_replace("'", '', $yelp)
        );

        $this->insertData($data, 'wedding_wire_venue');
        //    var_dump($data);
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
            try {
                $sql = "INSERT INTO `$table` SET $insertQry";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            } catch (ErrorException $e) {
                $file = fopen("mysql_error_weddingwire.txt", "a");
                fwrite($file, $e . "\n" . $sql . "\n");
                fclose($file);
            }

        }
        $pdo = null;
    }


    public function getLastPage($urlGeo)
    {
        $htmlDataList = $this->curlTo($urlGeo);
        $htmlList = str_get_html($htmlDataList['html']);
        $paginationCount = $htmlList->find('.testing-catalog-pagination-links', 1);
        $pagination = $paginationCount->find('.pagination', 0);
        $paginationLi = $pagination->find('li');
        $lastPage = trim($paginationLi[count($paginationLi) - 2]->plaintext);
        return $lastPage;
    }

    public function getTotalItems($urlGeo)
    {
        $htmlDataList = $this->curlTo($urlGeo);
        $html = str_get_html($htmlDataList['html']);
        $totalItems = trim($html->find('.directory-results-bar-results', 0)->plaintext);
        $totalItems = preg_replace("/[^0-9]/", "", $totalItems);
        return $totalItems;
    }

    public function getNextLocation(){
        $pdo = $this->getPdo();
        $sql = 'SELECT `id`, `loc` FROM `location` WHERE `status` = 0 LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $return = $stmt->fetch(PDO::FETCH_ASSOC);

        if($return){
            $sql = 'UPDATE `location` SET `status` = 1 WHERE `id` = '.$return['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $pdo = null;
            return trim($return['loc']);
        }else{
            $pdo = null;
            return false;
        }
    }

    public function getCurrentCategory(){
        $pdo = $this->getPdo();
        $sql = 'SELECT * FROM `wire_categories` WHERE `status` = 0 LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $return = $stmt->fetch(PDO::FETCH_ASSOC);
        $pdo = null;

        return $return;
    }

    public function proceedNextCategory($catId){
        $pdo = $this->getPdo();
        $sql = 'UPDATE `wire_categories` SET `status` = 1 WHERE `category_id` = '.$catId;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        $this->reset();
        return true;
    }

    public function reset(){
        $pdo = $this->getPdo();
        $sql = 'UPDATE `location` SET `status` = 0';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
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
            "Referer: https://www.weddingwire.com"
        ));
        $contents = curl_exec($curl);
        curl_close($curl);
        return array('html' => $contents);
    }


    function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
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