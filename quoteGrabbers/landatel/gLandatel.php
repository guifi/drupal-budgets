<?php

include '../includes/simple_html_dom.php';
include '../includes/utils.php';

$urls = array(

// Ubiquity

'http://landashop.com/catalog/sistemas-wireless-c-182_185.html',
'http://landashop.com/catalog/airfiber-c-175_422.html', // UBNT AircwFiberMAX
'http://landashop.com/catalog/airmax-titanium-c-175_417.html', // UBNT Titanium
'http://landashop.com/catalog/airmax-10-ghz-c-175_416.html', // UBNT AirMAX10
'http://landashop.com/catalog/airmax-5-ghz-c-175_261.html', // AirMAX M5
'http://landashop.com/catalog/airmax-5-ghz-c-175_261.html?page=2', // AirMAX M5
'http://landashop.com/catalog/airmax-5-ghz-c-175_261.html?page=3', // AirMAX M5
'http://landashop.com/catalog/airmax-24-ghz-c-175_314.html', // AirMAX M2
'http://landashop.com/catalog/airmax-24-ghz-c-175_314.html?page=2', // AirMAX M2
'http://landashop.com/catalog/airmax-35-ghz-c-175_336.html', // AirMAX M3
'http://landashop.com/catalog/nanobeam-c-175_337.html', // Nanobeam
'http://landashop.com/catalog/nano-series-c-175_178.html?page=1', // Nano
'http://landashop.com/catalog/nano-series-c-175_178.html?page=2', // Nano
'http://landashop.com/catalog/power-series-c-175_179.html', // Power
'http://landashop.com/catalog/picostation-c-175_196.html', // PicoStation
'http://landashop.com/catalog/bullet-c-175_197.html', // Bullet
'http://landashop.com/catalog/airvision-c-175_300.html?page=1', // Airvision
'http://landashop.com/catalog/airvision-c-175_300.html?page=2', // Airvision
'http://landashop.com/catalog/antenas-c-175_198.html?page=1', // Antenas
'http://landashop.com/catalog/antenas-c-175_198.html?page=2', // Antenas
'http://landashop.com/catalog/antenas-c-175_198.html?page=3', // Antenas
'http://landashop.com/catalog/soluciones-usuario-final-c-175_315.html', // Home
'http://landashop.com/catalog/minipci-wlanwifi-radios-c-175_176.html', // MiniPCI
'http://landashop.com/catalog/power-over-ethernet-c-175_254.html?page=1', // UBNT PoE
'http://landashop.com/catalog/power-over-ethernet-c-175_254.html?page=2', // UBNT PoE
'http://landashop.com/catalog/power-over-ethernet-c-175_254.html?page=3', // UBNT PoE

// Mikrotik
'http://landashop.com/catalog/sistemas-wireless-c-182_185.html',
'http://landashop.com/catalog/sistemas-wireless-c-182_185.html?page=2',
'http://landashop.com/catalog/sistemas-wireless-c-182_185.html?page=3',
'http://landashop.com/catalog/routers-c-182_186.html',
'http://landashop.com/catalog/routers-c-182_186.html?page=2',
'http://landashop.com/catalog/routers-c-182_186.html?page=3',
'http://landashop.com/catalog/routers-c-182_186.html?page=4',
'http://landashop.com/catalog/switches-c-182_330.html',
'http://landashop.com/catalog/todas-routerboards-c-182_187.html',
'http://landashop.com/catalog/todas-routerboards-c-182_187.html?page=2',
'http://landashop.com/catalog/todas-routerboards-c-182_187.html?page=3',
'http://landashop.com/catalog/todas-routerboards-c-182_187.html?page=4',
'http://landashop.com/catalog/todas-routerboards-c-182_187.html?page=5',
'http://landashop.com/catalog/fibra-optica-c-182_450.html',
'http://landashop.com/catalog/minipci-minipcie-c-182_188.html',
'http://landashop.com/catalog/alimentacion-electrica-c-182_222.html',
'http://landashop.com/catalog/alimentacion-electrica-c-182_222.html?page=2',
'http://landashop.com/catalog/cajas-c-182_189.html',

// TP-Link
'http://landashop.com/catalog/fibra-optica-c-236_271.html',
'http://landashop.com/catalog/fibra-optica-c-236_271.html?page=2',

// Switches gestionables
'http://landashop.com/catalog/gestionables-c-339_343.html'

);

function grab_landatel($url) {
  $html = file_get_html($url);

  foreach($html->find('table[class=productListing] tr') as $product) {

    // Obtinc el titol, el partno i el cost
    $i=0;
    $productsTDs = $product->find('td');
    foreach($productsTDs as $productTD) {
      if ($productTD->class == 'productListing-heading')
        continue;
      $str = str_replace('&nbsp;','', $productTD->plaintext);
      switch ($i) {
      case 2: $title = $str; break;
      case 3: $partNo = $str; break;
      case 4: $cost = explode(' - ',str_replace('EUR','',str_replace(',','',$str))); if (empty($cost[1])) $cost[1]=$cost[0]; break;
      }
      $i++;
    }

    // Obtinc la referència a la pagina del producte
    foreach($product->find('a') as $link) {
      $srclink = $link->href;
    }

    // Obtinc la imatge
    foreach($product->find('img[width=120]') as $img) {
      $iname = explode('/',$img->src);
      grab_image('http://landashop.com/catalog/images/'.$iname[1],'../images/'.$iname[1]);
    }

    if (!empty($title))  {
      $cos = '<a href="'.$srclink.'" alt="'.$title.'"><img src="/files/catalog/'.$iname[1].'" width=120 height=120 align="right"></a>';
      echo "$partNo|$title|$cos|21|$cost[1]\n";
    }

  }
}

foreach ($urls as $u)
  grab_landatel($u);

?>
