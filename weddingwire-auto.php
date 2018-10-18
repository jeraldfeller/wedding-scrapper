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
$rootUrl = 'https://www.weddingwire.com';
// get locations
//list of vendors

$location = $scraper->getNextLocation();
$category = $scraper->getCurrentCategory();
if($location){

    $url = 'https://www.weddingwire.com/c/'.$location.'/'.$category['category'].'/'.$category['category_id'].'-sca.html';
    echo $url . "\n";
//for($l = 0; $l < count($locations); $l++){
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
            switch ($category['category']) {
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

}else{
    $scraper->proceedNextCategory($category['category_id']);
}

?>
