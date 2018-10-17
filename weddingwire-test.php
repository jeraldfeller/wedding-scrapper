<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
//include('header.html');
require 'simple_html_dom.php';
$url = 'https://www.weddingwire.com/biz/redondo-beach-historic-library-redondo-beach/b2e073faa494af3f.html';
$htmlData = curlTo($url);
$html = str_get_html($htmlData['html']);

$header = $html->find('#storefront-header-info', 0);
$title = $header->find('h1', 0)->plaintext;
$address = $header->find('.testing-location-header', 0)->plaintext;
$phoneNumber = $header->find('.testing-phone-number', 0)->plaintext;
$aLinks = $header->find('a');
$website = '';
for ($a = 0; $a < count($aLinks); $a++) {
    $textLink = trim($aLinks[$a]->plaintext);
    if ($textLink == 'Visit Website') {
        $website = $aLinks[$a]->getAttribute('href');
    }
}

$about = $html->find('#about-us-read-more', 0);
if ($about) {
    $about = $about->plaintext;
} else {
    $about = '';
}

$faq = $html->find('#storefront-section-faq', 0);
$panels = $faq->find('.faq-panel');
$services = array();
$maxCapacity = '';
$eventSpaces = '';
$type = '';
$style = '';
$setting = '';
for ($p = 0; $p < count($panels); $p++) {
    $title = trim($panels[$p]->find('.panel-heading', 0)->plaintext);
    echo $title . "\n";
    if ($title == 'Event Services') {
        $items = $panels[$p]->find('.faq-item');
        for ($i = 0; $i < count($items); $i++) {
            $isOffered = $items[$i]->find('.fa-check', 0);
            if ($isOffered) {
                $services[] = trim($items[$i]->find('.core-ellipsis', 0)->plaintext);
            }
        }

    }

    if ($title == 'Venue Highlights') {
        $items = $panels[$p]->find('.faq-item');
        for ($i = 0; $i < count($items); $i++) {
            $faqItem = trim($items[$i]->plaintext);
            if(strpos($faqItem, 'Maximum Capacity') !== false){
                $maxCapacity = trim($items[$i]->find('.type', 0)->plaintext);
            }
            if(strpos($faqItem, 'Event Spaces') !== false){
                $eventSpaces = trim($items[$i]->find('.type', 0)->plaintext);
            }
            if(strpos($faqItem, 'Type') !== false){
                $type = trim($items[$i]->find('.type', 0)->plaintext);
            }
            if(strpos($faqItem, 'Style') !== false){
                $style = trim($items[$i]->find('.type', 0)->plaintext);
            }
            if(strpos($faqItem, 'Setting') !== false){
                $setting = trim($items[$i]->find('.type', 0)->plaintext);
            }
            echo $items[$i]->plaintext."\n";
        }
    }

}


$data = array(
    'title' => trim($title),
    'address' => trim($address),
    'phone_number' => trim($phoneNumber),
    'website' => trim($website),
    'about' => trim($about),
    'services' => $services,
    'maximumCapacity' => $maxCapacity,
    'eventSpaces' => $eventSpaces,
    'type' => $type,
    'style' => $style,
    'setting' => $setting
);

function curlTo($url)
{
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
var_dump($data);
?>
