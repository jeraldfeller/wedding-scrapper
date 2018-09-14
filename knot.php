<?php
include('header.html');
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
require 'simple_html_dom.php';
$url = 'https://www.theknot.com/marketplace/glamorous-events-and-decor-los-angeles-ca-1058992';
$url = 'https://www.theknot.com/marketplace/a-touch-of-soul-productions-placentia-ca-950095';
$url = $_GET['url'];
echo $url;
$html = file_get_html($url, false);

$json = array();
$scripts = $html->find('script');
if(count($scripts) > 0) {
    for ($s = 0; $s < count($scripts); $s++) {
        if($scripts[$s]->getAttribute('data-component-name') == 'Storefront'){
            $scriptInner = $scripts[$s]->innertext;
            $json = json_decode($scriptInner, true);

        }
    }
}
//
//
//$vendorName = $html->find('.styles__vendor-name___2vuwO', 0)->plaintext;
//$website = $html->find('.styles__link___3bUv_', 0)->find('a', 0)->getAttribute('href');
//$details = $html->find('#navDetails', 0)->plaintext;
//$addressContainer = $html->find('.styles__contact-info___1AW60', 0);
//$address = $addressContainer->find('span', 0)->plaintext;
//$span = $addressContainer->find('span');
//$telephone = $span[count($span)-1]->plaintext;
//
//


//$data = array(
//  'vendor' => trim($vendorName),
//    'details' => trim($details),
//    'address' => trim($address),
//    'telephone' => trim($telephone),
//    'website' => trim($website)
//);

?>
<pre>
<?php var_dump($json) ?>;
</pre>
