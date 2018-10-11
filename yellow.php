<?php
require 'simple_html_dom.php';
$post = $_POST['html'];
$html = str_get_html($post, false);
$container = $html->find('.search-results', 0);
$list = $container->find('.find-show-more-trial');
echo 'Page: ' . $_GET['page'] . "\n";
$lists = array();
for($x = 0; $x < count($list); $x++){
    $ldc = $list[$x]->find('.listing', 0);
    if($ldc){
        $listingId = $ldc->getAttribute('data-listing-id');
        $advertiserId = $ldc->getAttribute('data-advertiser-id');
        $productId = $ldc->getAttribute('data-product-id');
        $productCode = $ldc->getAttribute('data-product-code');
        $businessName = $ldc->getAttribute('data-full-name');
        $heading = $ldc->getAttribute('data-heading-name');
        $suburb = $ldc->getAttribute('data-suburb');
        $state = $ldc->getAttribute('data-state');
        $totalReview = $ldc->getAttribute('data-total-reviews');
        $averageRating = $ldc->getAttribute('data-omniture-average-rating');
        $url = $ldc->getAttribute('data-url');
        $postCode = $ldc->getAttribute('data-postcode');
        $contentScore = $ldc->getAttribute('data-content-score');
        $score = $ldc->getAttribute('data-score');
        $address = $ldc->getAttribute('data-full-address');

        $contactPhone = $list[$x]->find('.contact-phone', 0);
        if($contactPhone){
          $phone = $contactPhone->plaintext;
        }else{
          $phone = '';
        }
        $contactEmail = $list[$x]->find('.contact-email', 0);
        if($contactEmail){
            $email = $contactEmail->getAttribute('data-email');
        }else{
            $email = '';
        }
        $contactUrl = $list[$x]->find('.contact-url', 0);
        if($contactUrl){
            $website = $contactUrl->getAttribute('href');
        }else{
            $website = '';
        }

        $description = $list[$x]->find('.listing-short-description', 0);
        if($description){
            $description = $description->plaintext;
        }else{
            $description = '';
        }

        $uspUl = $list[$x]->find('.item-list-usp', 0);
        $uspList = array();
        if($uspUl){
            $uspLi = $uspUl->find('li');
            for($l = 0; $l < count($uspLi); $l++){
                $uspList[] = $uspLi[$l]->plaintext;
            }
        }

        $uspUl = $list[$x]->find('.item-list-usp', 0);
        $uspList = array();
        if($uspUl){
            $uspLi = $uspUl->find('li');
            for($l = 0; $l < count($uspLi); $l++){
                $uspList[] = $uspLi[$l]->plaintext;
            }
        }

        $accUl = $list[$x]->find('.item-list-accreditation', 0);
        $accList = array();
        if($uspUl){
            $accLi = $uspUl->find('li');
            for($l = 0; $l < count($accLi); $l++){
                $accList[] = $accLi[$l]->plaintext;
            }
        }


        $lists[] = implode('","', array(
            'listingId' => $listingId,
            'advertiserId' => $advertiserId,
            'productId' => $productId,
            'productCode' => $productCode,
            'name' => $businessName,
            'heading' => $heading,
            'totalReview' => $totalReview,
            'averageRating' => $averageRating,
            'contentScore' => $contentScore,
            'score' => $score,
            'description' => $description,
            'usp' => implode(',', $uspList),
            'accreditation' => implode(',', $accList),
            'url' => 'https://www.yellowpages.com.au/'.$url,
            'address' => $address,
            'suburb' => $suburb,
            'state' => $state,
            'postCode' => $postCode,
            'phone' => trim($phone),
            'email' => trim($email),
            'website' => trim($website)
        ));
        echo $listingId . "\n";
    }
}
$csv = 'yellow-pages.csv';
$file = fopen($csv, "a");
foreach ($lists as $line) {
    fputcsv($file, explode('","', $line));
}
fclose($file);

echo '-----------------------------------------------'."\n";




