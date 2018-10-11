<?php
require 'simple_html_dom.php';
curlTo();


function curlTo(){
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://www.edmunds.com/acura/tl/2010/vin/19UUA8F23AA015904/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_SSL_VERIFYHOST => FALSE,
    CURLOPT_SSL_VERIFYPEER => FALSE,
    CURLOPT_HTTPHEADER => array(
        "Cache-Control: no-cache",
  //      "Postman-Token: badc44ad-e3b9-437b-ae23-89ea682d9dd2"
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    echo $response;
}
}