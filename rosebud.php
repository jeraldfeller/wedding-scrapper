<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
include('header.html');
require 'simple_html_dom.php';
$url = 'https://www.weddingwire.com/biz/redondo-beach-historic-library-redondo-beach/b2e073faa494af3f.html';
$htmlData = curlTo($url);
$html = str_get_html($htmlData['html']);

$header = $html->find('#storefront-header-info', 0);
$title = $header->find('h1', 0)->plaintext;
$address = $header->find('.testing-location-header', 0)->plaintext;
$phoneNumber = $header->find('.testing-phone-number', 0)->plaintext;
$aboutContent = $html->find('.read-more-content', 0);
$ul = $aboutContent->find('ul', 0);
$li = $ul->find('li');
$services = array();
for($x = 0; $x < count($li); $x++){
    $services[] = trim($li[$x]->plaintext);
}

$pricingSection = $html->find('#storefront-section-pricing', 0);
$startingPriceSection = $pricingSection->find('#collapse-filters-0-dj_services_starting_price', 0);
$startingPanelHeader = $startingPriceSection->find('.panel-title', 0);
$startingPrice = $startingPanelHeader->find('.type', 0)->plaintext;
$startingInfo = $startingPriceSection->find('.info-row', 0);
$startingIncludes = $startingInfo->find('div');
$sIncludes = array();
for($x = 0; $x < count($startingIncludes); $x++){
    if(trim($startingIncludes[$x]->plaintext) != ''){
        $sIncludes[] = trim($startingIncludes[$x]->plaintext);
    }
}
$sIncludes = array_unique($sIncludes);

$packagePriceSection = $pricingSection->find('#collapse-filters-1-dj_package_pricing', 0);
$packagePanelHeader = $packagePriceSection->find('.panel-title', 0);
$packagePrice = $packagePriceSection->find('.type', 0)->plaintext;
$packageInfo = $packagePriceSection->find('.info-row', 0);
$packageIncludes = $packageInfo->find('div');
$pIncludes = array();
for($x = 0; $x < count($packageIncludes); $x++){
    if(trim($packageIncludes[$x]->plaintext) != ''){
        $pIncludes[] = trim($packageIncludes[$x]->plaintext);
    }
}
$pIncludes = array_unique($pIncludes);

$data = array(
    'title' => trim($title),
    'address' => $address,
    'phone_number' => $phoneNumber,
    'services' => $services,
    'prices' => array(
        'starting_price' => array(
            'price' => $startingPrice,
            'includes' => $sIncludes
        ),
        'package_price' => array(
            'price' => $packagePrice,
            'includes' => $pIncludes
        )
    )

);

function curlTo($url){
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
?>
<pre>
<?php var_dump($data) ?>;
</pre>