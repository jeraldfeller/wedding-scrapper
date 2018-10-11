<?php
require 'simple_html_dom.php';
$url = 'https://www.autotrader.com/cars-for-sale/searchresults.xhtml?zip=78541&makeCode1=ACURA&Log=0';

$html = curlTo();
var_dump($html);
//
//$listings = $html->find('.inventory-listing-body');
//if($listings){
//    for($x = 0; $x < count($listings); $x++){
//        $a = $listings[$x]->find('a',0)->getAttribute();
//        echo $a . "\n";
//    }
//}


function curlTo(){
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www.autotrader.com/cars-for-sale/searchresults.xhtml?zip=78541&makeCode1=ACURA&Log=0",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_SSL_VERIFYHOST => FALSE,
        CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
            "Postman-Token: 4020aae6-52dc-41c3-aaaa-8d153bba2482",
            "cookie: DCNAME=www-aws.autotrader.com; exp=csFF~cntl!fonts2~hosted!gbb2metb~cntrl!hpAd~ctr!perf~cntrl!rLive~grpA!sgtrckcb~on!sycfree~ctrl!vdpemailcc~emailccon; ATC_ID=110.54.237.171.1538008909031398; AWSELB=3F0DAFC91E416AE3FD2D26224F019EEBBA5F98A0B64AF8638929E1848094E1A684917E41CF3B85BCA40CC5892DFFB08D8017C92B2271C9E1745B54CA4FC0DC97AB4619017D; bm_sz=E2A1E15425D0F1C7A9A8503C19FDE189~QAAQ9CMcePJy5/xlAQAAg4V5GFpU6IoinAYb78NJrd19zTttIA3z2k5047Rx+B1qBdN0IRIYNEp9ZSpq6MWqS8bPXD0o41DKiPIo46IZ/OgCYvGjuaQH1nEXs7T7lkNL54n7o8RC393LdH1X/B5ILpudE/qKsWI/D7kduDsNAb3bBXtivjW4czH1zlKFwyd5r+wZ; AMCVS_A9D33BC75245B2650A490D4D%40AdobeOrg=1; ak_bmsc=9804453C8E682FEA5AFBBB0AED762D25781C23F4977300004D27AC5BE463056B~plEw6YxsWCPclC8aC6JZ0/afqiGPn6lebKrRuWDKHNijZ5H4sByjQsXom9eGlQY/75qRP6a3psZXncR38cAgqujlbAEcL9FRi1i2lLJTeR8RcFmL7HZb6gQ62jhpyTTe7qVT/6bKKdc4JO89t5HmeUG51Ox/PJn9W9GWPMuX4tPzutQztwsvBKBJZquCa7ok6cULJAOZDYvQQO6wyzZaQgR9FnlsD7uf4Cm9+bXkVv6/l8v0HmCCwg70rMPaOmKSUf; _abck=584BA70F1E5ED380D5CAE3E98651C7AE781C23F4977300004D27AC5B1FDFB062~0~ANPc/+BJKVcYSijrxjr53/a1aXvXYG+WerK9ZOEG55Y=~-1~-1; AMCV_A9D33BC75245B2650A490D4D%40AdobeOrg=1099438348%7CMCIDTS%7C17802%7CMCMID%7C50437537896836583480965726358175914837%7CMCAAMLH-1538613710%7C3%7CMCAAMB-1538613712%7CRKhpRz8krg2tLO6pguXWp5olkAcUniQYPHaMWWgdJ3xzPWQmdj0y%7CMCOPTOUT-1538016110s%7CNONE%7CMCAID%7C2CFD717B85032FB8-40001180C0012FEB%7CvVersion%7C2.1.0; BIRF_Audit=true; pxa_id=dlF2fwfssBs9enP94OaI4Ffc; pxa_at=true; _sdsat_DesktopOrMobile=desktop; gdptest=test%3Dand; aam_id=50244294308023014970946789095528216320; __gads=ID=a5692df2b0554066:T=1538008936:S=ALNI_MaNWcpXPMpgiIWQxf-KozywpM_yeQ; nrob=y; intVis=true; JSESSIONID=JbYdK-hoLOOlYdGIodXKjpKMRUvDplmVgjizuqXN.e033542a4dff; gig_hasGmid=ver2; ATC_USER_RADIUS=0; aam_tnt_vin=atc_seg18%3D92498%2Catc_seg27%3D96938%2Catc_seg31%3D92302%2Catc_seg32%3D92303%2Catc_seg55%3D751460%2Catc_seg63%3D751471%2Catc_seg67%3D751477%2Catc_seg68%3D751478%2Catc_seg86%3D751536%2Catc_seg87%3D751538%2Catc_seg88%3D751539%2Cvin_seg%3Dkbb%2Catc; heroTest=lux%3Dtrue%2Csuv%3Dtrue; AKA_A2=A; dxatc=%3D21%2C1%2C6%2C7%2C27%2C64%2C65%2C98%2C319%2C1397%2C76%2C1399%2C1820%2C1821%2C1910%2C1995%2C2049%2C2050%2C2051%2C2053%2C2060%2C2061%2C2092%2C2114%2C2156%2C2204%2C2291%2C2878%2C2879%2C2900%2C2905%2C3052%2C3058%2C3065%2C3066%2C3085%2C3102%2C3104%2C3142; BIGipServerwww-rh7=294182922.61475.0000; s_pencilEvent=true; ATC_USER_ZIP=78541; oam.Flash.RENDERMAP.TOKEN=-i9p7nzo0y; ATC_BTC=21%2C1%2C6%2C7%2C27%2C64%2C65%2C98%2C319%2C1397%2C76%2C1399%2C1820%2C1821%2C1910%2C1995%2C2049%2C2050%2C2051%2C2053%2C2060%2C2061%2C2092%2C2114%2C2156%2C2204%2C2291%2C2878%2C2879%2C2900%2C2905%2C3052%2C3058%2C3065%2C3066%2C3085%2C3102%2C3104%2C3142; ATC_PID=570846228|380089149968032562&-2035463884|380108256810383623&95598174|380108835113466314&696415375|380109333505493873&549761013|380109485039013745&2096093824|380109670656427107&-360840848|380110326593672849&884501241|380116267170432080&2062513999|380116434072443173&-724398254|380124332219401281&1199891896|380128844209611373&-775720878|380128997051982965&-1619429710|380129159035115178&1284010817|380129398190769679&1202089315|380129525930783909&1825686047|380133379355710117&614536969|380133824218617440&-1036098363|380134081813681996&-638656542|380137266631513267; ATC_SID=570846228|-1&-2035463884|-1&95598174|-1&696415375|-1&549761013|-1&2096093824|380109673880550037&-360840848|380110329140386898&884501241|380110967458959291&2062513999|380116442664863216&-724398254|380116442664863216&1199891896|-1&-775720878|-1&-1619429710|380129160167981768&1284010817|380129400015146115&1202089315|380129400015146115&1825686047|380133382892757822&614536969|380133382892757822&-1036098363|380134083126416593&-638656542|380137268341470198; s_pers=%20ev96gapv%3DUnknown%7C1538323199118%3B%20s_dl%3D1%7C1538014684953%3B%20ev95gapv%3DTyped%252FBookmarked%7C1538014684953%3B%20s_ppn%3Dfyc_srl%7C1538015527037%3B%20s_dl3%3D1%7C1538015527091%3B%20s_dl4%3D1%7C1538015527093%3B; s_sess=%20pageNum%3D%3B%20s_skw3%3Dfyc_srl%3B%20s_skw%3D140%252C603ACURAany0any19812019b78541no%2520product%2520type%3B%20s_skw2%3D40%252C603ACURAany0any19812019b78541no%2520product%2520type%3B%20s_sq%3D%3B%20s_cc%3Dtrue%3B%20tp%3D8223%3B%20s_ppv%3Dfyc_srl%252C22%252C22%252C1798%3B; _4c_=jVRrb%2BI4FP0ro0j0EwS%2FEttIqApQpF1N221nVqv5VDm2gah5IMeUdqr%2B97kOzy6r1SBEuMfH1%2BfeXJ%2F3aLuydTTCCRUIMyQFl0k%2FerZvbTR6j1xhwuMlGkULlGCWa2VMSrHVCyIU0VYzlkuJc5ZE%2Feg15GEoRURSjjDvR96X0YgkkqDu89GP9Hqf8D3SjbGQGMs4jRls9z8hYgLBX1uHI9fOwP8F7Ig0NUanXCiZC0wlJ5aw3FBpsMylpjnwNg7Oilber9vRcLjdbmO18Y13ylgX66YaauXawaJxg1aVdtha5fTK2XZT%2BjZ%2BXfmqvP5ZrMdcJAxfVerZTkEfHmfTvx%2Bzq6%2FNchyErV1jNto%2F%2Bbd10L61%2BZfWPMOCqRTEd%2FdPs9sMwupNef1UNsulNQX0N6zc%2Fsi%2BT2Etd822tQ7A6co1lf2SSkAbaHj0T1EbWITQ2YV1rmOdl7RZbxv33JVTWvVS1MtrYI47So9mPTKH72XtAH6qHuIXuyp0aY31qij3DejReVm0HrL%2BYXp0xiSmmJCU9kgKrQGkaw5EB3UA9UhykRyQ%2F2gv4HQe8sDzkCkhaVXUf7lCh210hhMYkw5uvXL%2BB2TpcBgg3sH1pnq0unGm3fEP7Mb5yVsHQcnFizXrkHN28226O0S9ng6RYRY7uKi1s5WtverUzVQZniRdFK71u3N2x1C0E2trc6YJy93hXa2PyhSbnSpIfirrWNRZSYeCzsvZF3OSehT6Sc5BzFHKQchnGXsRhzGGeJrBu0oriMoDBN3JvoZxbQsfxvnz0Oxx8IJPS%2BEWhNsZrmzZaHjhEICH9KP72%2B%2BPT5ObbHp%2Fdza2Z0kNiVsdN5V3Rse19cN82LaBAJdlWTa5Kod4%2BOe3AYlRjAYz%2FnA3bIVMJENMpJwwLK6zh8kYrmdhxglilCeUC5kKmiaCgnXINOEwr4nAPJGYCcqvFFDJdD7jmE9EgiiZT8SAQV8xFmgKDzK%2FmVxlDzdjHK5xuKvGLhSMbQR21VkaFfBmMOEUob2liTRk6AxtDWFnaPjIRgS0UETIno3Zid05H%2FywE1tyShmY6GXunVP8zx5A%2Fr0HxF8K2tPB2S%2FoUPDO4A9V%2F56PF8dtv7Xh4%2BMX; bm_sv=22271F8F83C2EF44E10B1565BE303151~kU2PRal24iEihXlnddXqxpN2SM+KOm9iJAs8HVeAQOadGVo634/ZM4q0XuSllyMxm/a801hyEeCEWaXTAmR1LDTw1htkZfJ2tU02ymfNfs6F88TvQo8jUJ2mtTAgeggHfXfNBX77hDqSReZiEBvpdgxGUXdRPFQgcWcs3Zgqzs8=; RT=\"\""
        )
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        $return = array('error' => $err);
    } else {
        $return = array('html' => $response);
    }

    return $return;
}