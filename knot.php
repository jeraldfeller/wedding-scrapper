<?php
require('Model/Init.php');
require('Model/Scraper.php');
//include('header.html');
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
require 'simple_html_dom.php';
$scraper = new Scraper();

//$action = getopt("a:")['a'];

// get locations
$locations = $scraper->getTheKnotLocations()['locations'];
//list of vendors
$categories = array(
    'venue',
    'photography',
    'videography',
    'dj',
    'salon',
    'band',
    'florist',
    'planners',
    'jewelers',
    'cakes',
    'accessory',
    'alterations',
    'bar',
    'catering',
    'ceremony-venues',
    'dance',
    'decor',
    'dessert',
    'soloists',
    'favors',
    'invitations',
    'lighting',
    'menswear',
    'officiants',
    'photobooth',
    'rentals',
    'staff',
    'transportation',
    'designers'
);
// iterate categories
for($c = 0; $c < count($categories); $c++){
    $action = $categories[$c];
    // build url by location
    for($l = 0; $l < count($locations); $l++){
//for ($l = 0; $l < 1; $l++) {
        $city = str_replace(' ', '-', strtolower($locations[$l]['city']));
        $state = str_replace(' ', '-', strtolower($locations[$l]['state']));
        switch ($action){
            case 'venue':
                $url = "https://www.theknot.com/marketplace/wedding-reception-venues-$city-$state";
                break;
            case 'photography':
                $url = "https://www.theknot.com/marketplace/wedding-photographers-$city-$state";
                break;
            case 'videography':
                $url = "https://www.theknot.com/marketplace/wedding-videographers-$city-$state";
                break;
            case 'dj':
                $url = "https://www.theknot.com/marketplace/wedding-djs-$city-$state";
                break;
            case 'salon':
                $url = "https://www.theknot.com/marketplace/bridal-salons-$city-$state";
                break;
            case 'band':
                $url = "https://www.theknot.com/marketplace/live-wedding-bands-$city-$state";
                break;
            case 'florist':
                $url = "https://www.theknot.com/marketplace/florists-$city-$state";
                break;
            case 'planners':
                $url = "https://www.theknot.com/marketplace/wedding-planners-$city-$state";
                break;
            case 'jewelers':
                $url = "https://www.theknot.com/marketplace/jewelers-$city-$state";
                break;
            case 'cakes':
                $url = "https://www.theknot.com/marketplace/wedding-cake-bakeries-$city-$state";
                break;
            case 'accessory':
                $url = "https://www.theknot.com/marketplace/bridal-accessory-shops-$city-$state";
                break;
            case 'alterations':
                $url = "https://www.theknot.com/marketplace/dress-preservation-cleaning-$city-$state";
                break;
            case 'bar':
                $url = "https://www.theknot.com/marketplace/bar-services-$city-$state";
                break;
            case 'catering':
                $url = "https://www.theknot.com/marketplace/catering-$city-$state";
                break;
            case 'ceremony-venues':
                $url = "https://www.theknot.com/marketplace/wedding-ceremony-venues-$city-$state";
                break;
            case 'dance':
                $url = "https://www.theknot.com/marketplace/wedding-dance-lessons-$city-$state";
                break;
            case 'decor':
                $url = "https://www.theknot.com/marketplace/wedding-decor-shops-$city-$state";
                break;
            case 'dessert':
                $url = "https://www.theknot.com/marketplace/desserts-$city-$state";
                break;
            case 'soloists':
                $url = "https://www.theknot.com/marketplace/wedding-soloists-ensembles-$city-$state";
                break;
            case 'favors':
                $url = "https://www.theknot.com/marketplace/favors-$city-$state";
                break;
            case 'invitations':
                $url = "https://www.theknot.com/marketplace/invitations-$city-$state";
                break;
            case 'lighting':
                $url = "https://www.theknot.com/marketplace/wedding-lighting-$city-$state";
                break;
            case 'menswear':
                $url = "https://www.theknot.com/marketplace/wedding-menswear-$city-$state";
                break;
            case 'officiants':
                $url = "https://www.theknot.com/marketplace/wedding-officiants-$city-$state";
                break;
            case 'photobooth':
                $url = "https://www.theknot.com/marketplace/wedding-photo-booth-rentals-$city-$state";
                break;
            case 'rentals':
                $url = "https://www.theknot.com/marketplace/wedding-rentals-$city-$state";
                break;
            case 'staff':
                $url = "https://www.theknot.com/marketplace/service-staff-$city-$state";
                break;
            case 'transportation':
                $url = "https://www.theknot.com/marketplace/transportation-services-$city-$state";
                break;
            case 'designers':
                $url = "https://www.theknot.com/marketplace/wedding-designers-$city-$state";
                break;
        }
        // get total offset
        $total = $scraper->getTotalTheKnotOffset($url);
        if ($total != 0) {
            $offset = 0;
            $continue = true;
            while ($continue == true) {
                $offsetUrl = $url . "?offset=$offset";
                //echo $offsetUrl . "\n";
                $scraper->getVendors($offsetUrl, $action);
                if($offset === $total){
                    $continue = false;
                }
                $offset = $offset + 30;
                if ($offset > $total) {
                    $offset = $total;
                }
            }
        }
    }
}
?>
