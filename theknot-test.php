<?php
include('header.html');
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
require 'simple_html_dom.php';
require 'Model/Scraper.php';
$scraper = new Scraper();
$url = 'https://www.theknot.com/marketplace/ardens-posh-events-los-angeles-ca-1083894';
echo $url;
$html = file_get_html($url, false);
$json = array();
$scripts = $html->find('script');
if(count($scripts) > 0) {
    for ($s = 0; $s < count($scripts); $s++) {
        if($scripts[$s]->getAttribute('data-component-name') == 'Storefront'){
            $scriptInner = $scripts[$s]->innertext;
            $json = json_decode($scriptInner, true);
        }
    }
}
//$vendor = $json['vendor'];
//$locationAddress = $vendor['location']['address'];
//$address = ($locationAddress['address1'] != null ? $locationAddress['address1'] : $locationAddress['address2']);
//$city = $locationAddress['city'];
//$state = $locationAddress['state'];
//$zip = $locationAddress['postalCode'];
//
//
//// facets
//// Amenities
//
//$facets = $vendor['facets'];
//$photoShootTypes = NULL;
//$photoVideo = NULL;
//$photoVideoStyles = NULL;
//$weddingActivities = NULL;
//foreach ($facets as $row) {
//    switch ($row['name']) {
//        case 'Photo Shoot Types':
//            $photoShootTypes = $scraper->getFacets($row['facets']);
//            break;
//        case 'Photo & Video':
//            $photoVideo = $scraper->getFacets($row['facets']);
//            break;
//        case 'Photo & Video Styles':
//            $photoVideoStyles = $scraper->getFacets($row['facets']);
//            break;
//        case 'Wedding Activities':
//            $weddingActivities = $scraper->getFacets($row['facets']);
//            break;
//        case 'Venue Service Offerings':
//            $venueServiceOfferings = $scraper->getFacets($row['facets']);
//            break;
//    }
//}
//
//// social media
//$socialMedia = $vendor['socialMedia'];
//$facebook = NULL;
//$twitter = NULL;
//$instagram = NULL;
//$pinterest = NULL;
//$yelp = NULL;
//$file = fopen("social.txt","a");
//foreach ($socialMedia as $row) {
//    echo fwrite($file, $row['code']."\n");
//    if(strpos($row['code'], 'YELP') !== false){
//        $yelp = $row['value'];
//    }
//    switch ($row['code']) {
//        case 'FBURL':
//            $facebook = $row['value'];
//            break;
//        case 'TWITTERUSERNAME':
//            $twitter = $row['value'];
//            break;
//        case 'INSTAGRAMUSERNAME':
//            $instagram = $row['value'];
//            break;
//        case 'PINTERESTUSERNAME':
//            $pinterest = $row['value'];
//            break;
//    }
//}
//fclose($file);
//
//$data = array(
//    'vendor_id' => $vendor['id'],
//    'Business_Name' => addslashes($vendor['name']),
//    'Business_Phone' => (isset($vendor['phones'][0]['number']) ? $vendor['phones'][0]['number'] : null),
//    'Business_Website' => (isset($vendor['displayWebsiteUrl']) ? $vendor['displayWebsiteUrl'] : null),
//    'Business_Address' => addslashes($address),
//    'Business_City' => $city,
//    'Business_State' => $state,
//    'Business_Zip_Code' => $zip,
//    'About_This_Vendor' => addslashes($vendor['headline'] . "\n" . $vendor['description']),
//    'Details' => NULL,
//    'Photo_Shoot_Types' => addslashes(rtrim($photoShootTypes, ',')),
//    'Photo_Video' => addslashes($photoVideo),
//    'Photo_Video_Styles' => addslashes(rtrim($photoVideoStyles, ',')),
//    'Wedding_Activities' => addslashes(rtrim($weddingActivities, ',')),
//    'Business_Facebook' => $facebook,
//    'Business_Instagram' => $instagram,
//    'Business_Twitter' => $twitter,
//    'Business_Pinterest' => $pinterest,
//    'Business_Yelp' => $yelp
//);

?>
<pre>
<?php var_dump($json) ?>;
</pre>