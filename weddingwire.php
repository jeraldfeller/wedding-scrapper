<?php
require('Model/Init.php');
require('Model/WeddingWire.php');
//include('header.html');
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
require 'simple_html_dom.php';
$scraper = new WeddingWire();//include('header.html');
$locations = $scraper->getLocations();
$cid = getopt("a:")['a'];

$rootUrl = 'https://www.weddingwire.com';
// get locations
$locations = $scraper->getLocations()['locations'];
//list of vendors
$categories = array();
// iterate categories
//for($c = 0; $c < count($categories); $c++) {
// $action = $categories[$c];
// build url by location
//for ($l = 0; $l < count($locations); $l++) {
for ($l = 0; $l < 1; $l++) {
    $geo = $locations[$l]['city'] . ', ' . $locations[$l]['stateCode'];
    $urlGeoP = 'https://www.weddingwire.com/shared/search?cid=' . $cid . '&geo=' . urlencode($geo) . '&geosr=all&page=1&sort=1&view_type=photo';
    $lastPage = $scraper->getLastPage($urlGeoP);

    for ($page = 1; $page <= $lastPage; $page++) {
        $urlGeo = 'https://www.weddingwire.com/shared/search?cid=' . $cid . '&geo=' . urlencode($geo) . '&geosr=all&page=' . $page . '&sort=1&view_type=photo';
        $htmlDataList = $scraper->curlTo($urlGeo);
        $htmlList = str_get_html($htmlDataList['html']);
        $lists = $htmlList->find('.js-catalog-click');
        echo $urlGeo . "\n";
        for ($a = 0; $a < count($lists); $a++) {
//    for ($a = 0; $a < 1; $a++) {
            $vendorLink = $rootUrl . $lists[$a]->getAttribute('href');
            echo $vendorLink . "\n";
            $htmlData = $scraper->curlTo($vendorLink);
            $html = str_get_html($htmlData['html']);
            switch ($cid) {
                case 11:
                    $scraper->scrapeVenue($html, $locations[$l]['city'], $locations[$l]['stateCode']);
                    break;
                case 1:
                    $scraper->scrapeBand($html, $locations[$l]['city'], $locations[$l]['stateCode']);
                    break;
            }
        }
    }

}
//}

?>
