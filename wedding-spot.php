<?php
include('header.html');
require 'simple_html_dom.php';
$url = 'https://www.wedding-spot.com/venue/722/Brandview-Ballroom-by-LA-Banquets/';
$html = file_get_html($url, false);

$titleContainer = $html->find('#venue-details-images', 0);
$detailsContainer = $html->find('#venue-sidebar-affix', 0);
if($titleContainer){
    $title = $titleContainer->find('h1', 0)->plaintext;
}else{
    $title = '';
}

if($detailsContainer){
    $vendorAddressContainer = $detailsContainer->find('#vendor-address', 0);
    if($vendorAddressContainer){
        $spans = $vendorAddressContainer->find('span');
        $contactNumber = $vendorAddressContainer->find('#show-phone-number', 0)->plaintext;
        for($x = 0; $x < count($spans); $x++){
            $s = $spans[$x];
            if($s->getAttribute('itemprop') == 'streetAddress'){
                $address = $s->plaintext;
            }else if($s->getAttribute('itemprop') == 'addressLocality'){
                $locale = $s->plaintext;
            }else if($s->getAttribute('itemprop') == 'addressRegion'){
                $region = $s->plaintext;
            }else if($s->getAttribute('itemprop') == 'postalCode'){
                $zip = $s->plaintext;
            }
        }
    }

    $amenities = array();
    $amenitiesItem = $html->find('.amenity-item');
    for($a = 0; $a < count($amenitiesItem); $a++){
        $amenities[] = trim($amenitiesItem[$a]->plaintext);
    }

    $overview = $html->find('#overview', 0);
    $h4 = $overview->find('h4');
    $p = $overview->find('p');

    for($h = 0; $h < count($h4); $h++){
        $h4Title = trim($h4[$h]->plaintext);
        if($h4Title == 'Rental Fees'){
           $rentalFees = $p[$h]->plaintext;
        }else if($h4Title == 'Wedding Cost'){
            $wedingCost = $p[$h]->plaintext;
        }
    }

    $fees = array('rental_fees' => trim($rentalFees), 'wedding_cost' => trim($wedingCost));
}

$data = array(
  'title' => trim($title),
    'address' => trim($address),
    'locale' => trim($locale),
    'region' => trim($region),
    'zip' => trim($zip),
    'phone_number' => trim($contactNumber),
    'amenities' => $amenities,
    'fees' => $fees
);
?>
<pre>
<?php var_dump($data) ?>;
</pre>