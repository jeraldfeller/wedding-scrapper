<?php
require('Model/Init.php');
require('Model/WeddingWireNew.php');
//include('header.html');
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
require 'simple_html_dom.php';
$scraper = new WeddingWireNew();//include('header.html');
//$locations = $scraper->getLocations();
$cid = getopt("a:")['a'];
$rootUrl = 'https://www.weddingwire.com';
// get locations
//list of vendors
$categories = array();
$location = $scraper->getNextLocation();
//    $locations = array(
//      'al-alabama',
//      'ca-california',
//        'ga-georgia',
//        'il-illinois',
//        'ia-iowa',
//        'ky-kentucky',
//        'ma-massachusetts',
//        'nv-nevada',
//        'ny-new-york',
//        'oh-ohio',
//        'pa-pennsylvania',
//        'tn-tennessee',
//        'va-virginia',
//        'az-arizona',
//        'fl-florida',
//        'hi-hawaii',
//        'ks-kansas',
//        'la-louisiana',
//        'mi-michigan',
//        'mo-missouri',
//        'nj-new-jersey',
//        'nc-north-carolina',
//        'ok-oklahoma',
//        'or-oregon',
//        'sc-south-carolina',
//        'tx-texas',
//        'wa-washington',
//        'wi-wisconsin',
//        'ar-arkansas',
//        'id-idaho',
//        'me-maine',
//        'ms-mississippi',
//        'mt-montana',
//        'ne-nebraska',
//        'nm-new-mexico',
//        'sd-south-dakota',
//        'ut-utah',
//        'vt-vermont',
//        'wv-west-virginia',
//        'wy-wyoming',
//        'ak-alaska',
//        'co-colorado',
//        'ct-connecticut',
//        'de-delaware',
//        'dc-district-of-columbia',
//        'in-indiana',
//        'md-maryland',
//        'mn-minnesota',
//        'nh-new-hampshire',
//        'nd-north-dakota',
//        'ri-rhode-island'
//    );
    //for($l = 0; $l < count($locations); $l++){
        switch ($cid){
            case 'wedding-venues':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-venues/11-sca.html';
                break;
            case 'wedding-bands':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-bands/1-sca.html';
                break;
            case 'wedding-beauty-health':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-beauty-health/16-sca.html';
                break;
            case 'wedding-caterers':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-caterers/3-sca.html';
                break;
            case 'wedding-ceremony-music':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-ceremony-music/4-sca.html';
                break;
            case 'wedding-djs':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-djs/7-sca.html';
                break;
            case 'wedding-dresses':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-dresses/17-sca.html';
                break;
            case 'wedding-event-rentals':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-event-rentals/12-sca.html';
                break;
            case 'photo-booths':
                $url = 'https://www.weddingwire.com/c/'.$location.'/photo-booths/207-sca.html';
                break;
            case 'wedding-favors':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-favors/5-sca.html';
                break;
            case 'wedding-florists':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-florists/8-sca.html';
                break;
            case 'lighting-decor':
                $url = 'https://www.weddingwire.com/c/'.$location.'/lighting-decor/24-sca.html';
                break;
            case 'wedding-photographers':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-photographers/10-sca.html';
                break;
            case 'wedding-videographers':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-videographers/14-sca.html';
                break;
            case 'wedding-cakes':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-cakes/2-sca.html';
                break;
            case 'wedding-planners':
                $url = 'https://www.weddingwire.com/c/'.$location.'/wedding-planners/15-sca.html';
                break;
        }
        $totalItems = $scraper->getTotalItems($url);
        $totalPage = ceil($totalItems / 20);
        echo $totalItems . " | " . $totalPage . "\n";
        for ($page = 1; $page <= $totalPage; $page++) {
            if($page != 1){
                $listUrl = $url .'?page='.$page;
            }else{
                $listUrl = $url;
            }
            echo "Page: ".$listUrl . "\n";
            $htmlDataList = $scraper->curlTo($listUrl);
            $htmlList = str_get_html($htmlDataList['html']);
            $listContainer = $htmlList->find('.directory-list', 0);
            $lists = $listContainer->find('.item-title');
            for ($a = 0; $a < count($lists); $a++) {
            //for ($a = 0; $a < 1; $a++) {
                $vendorLink = $lists[$a]->getAttribute('href');
                echo $vendorLink . "\n";
                $htmlData = $scraper->curlTo($vendorLink);
                $html = str_get_html($htmlData['html']);
                switch ($cid) {
                    case 'wedding-venues':
                        $scraper->scrapeVenue($html, '', '');
                        break;
                    case 'wedding-bands':
                        $scraper->scrapeBand($html, '', '');
                        break;
                    case 'wedding-beauty-health':
                        $scraper->scrapeHealthBeauty($html, '', '');
                        break;
                    case 'wedding-caterers':
                        $scraper->scrapeCaterers($html, '', '');
                        break;
                    case 'wedding-ceremony-music':
                        $scraper->scrapeCeremonyMusic($html, '', '');
                        break;
                    case 'wedding-djs':
                        $scraper->scrapeDj($html, '', '');
                        break;
                    case 'wedding-dresses':
                        $scraper->scrapeDresses($html, '', '');
                        break;
                    case 'wedding-event-rentals':
                        $scraper->scrapeEventRentals($html, '', '');
                        break;
                    case 'photo-booths':
                        $scraper->scrapePhotoBooths($html, '', '');
                        break;
                    case 'wedding-favors':
                        $scraper->scrapeFavors($html, '', '');
                        break;
                    case 'wedding-florists':
                        $scraper->scrapeFlorists($html, '', '');
                        break;
                    case 'lighting-decor':
                        $scraper->scrapeLightingDecor($html, '', '');
                        break;
                    case 'wedding-officiants':
                        $scraper->scrapeOfficiants($html, '', '');
                        break;
                    case 'wedding-photographers':
                        $scraper->scrapePhotographer($html, '', '');
                        break;
                    case 'wedding-videographers':
                        $scraper->scrapeVideographer($html, '', '');
                        break;
                    case 'wedding-cakes':
                        $scraper->scrapeCakes($html, '', '');
                        break;
                    case 'wedding-planners':
                        $scraper->scrapePlanners($html, '', '');
                        break;
                }
            }
        }
   // }
//}

?>
