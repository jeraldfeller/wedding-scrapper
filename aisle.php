<?php
include('header.html');
require 'simple_html_dom.php';
$url = 'https://www.aisleplanner.com/wedding-services/vendor-listings/theinnatrsf/';
$html = file_get_html($url, false);

$title = $html->find('.business-name', 0)->plaintext;
$locationContainer = $html->find('.category-location', 0);
$location = $locationContainer->find('text', 3)->plaintext;
$phoneNumber = $locationContainer->find('text', 4)->plaintext;
$cost = $html->find('.cost-rating', 0)->plaintext;
$serving = $html->find('.serving', 0);
$markets = $serving->find('.market');
$servings = array();
for($x = 0; $x < count($markets); $x++){
    $servings[] = trim($markets[$x]->plaintext);
}
$data = array(
    'title' => trim($title),
    'location' => trim($location),
    'phone_number' => trim($phoneNumber),
    'cost' => trim($cost),
    'serving' => $servings

);

var_dump($data);