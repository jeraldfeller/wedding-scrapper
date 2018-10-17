<?php
require('Model/Init.php');
require('Model/WeddingSpot.php');
//include('header.html');
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
require 'simple_html_dom.php';
$action = getopt("a:")['a'];
$scraper = new WeddingSpot();
switch ($action){
    case 'reg':
        $location = $scraper->getNextLocation();
        $urlGeo = 'https://www.wedding-spot.com/wedding-venues/'.$location.'/?page=1';
        $totalItems = $scraper->getTotalItems($urlGeo);
        $totalPage = ceil($totalItems / 25);
        $root = 'https://www.wedding-spot.com';
        echo $totalItems . " | " . $totalPage . "\n";
        for ($page = 1; $page <= $totalPage; $page++) {
            $url = 'https://www.wedding-spot.com/wedding-venues/'.$location.'/?page='.$page;
            $html = file_get_html($url, false);
            if ($html) {
                $container = $html->find('.search-results', 0);
                if($container){
                    $links = $container->find('.venue-link');
                    if($links){
                        for($l = 0; $l < count($links); $l++){
                            $urlLink = $root.$links[$l]->getAttribute('href');
                            echo $l.": ".$urlLink . "\n";
                            $scraper->registerLink($urlLink);
                        }
                    }
                }

            }
        }
        break;
    case 'scrape':
        $links = $scraper->getLinks();
        foreach($links as $row){
            $html = file_get_html($row['url'], false);
            if($html){
                $scraper->scrapeVenue($html);
            }
        }
        break;
}





?>