<?php

// Latest grabber as from Nov 28 2015
/* to use:
 * within a shell:
 * php gLandatel.php > landatel.txt
 * within a browser, goto address for supplier quotes batch upload and
 * select landatel.txt as the csv file, with | as a delimiter
 *
 */



include '../includes/simple_html_dom.php';
include '../includes/utils.php';

$urls = array(

// Ubiquity

'http://www.landashop.com/catalog/airfiber-c-175_422.html', // UBNT AirFiber
'http://www.landashop.com/catalog/airfiber-c-175_422.html?page=2', // UBNT AirFiber 2
'http://www.landashop.com/catalog/airmax-ac-c-175_417.html', // UBNT AirMAX AC
'http://www.landashop.com/catalog/airmax-ac-c-175_417.html?page=2', // UBNT AirMAX AC 2
'http://landashop.com/catalog/sistemas-wireless-c-182_185.html',
'http://landashop.com/catalog/airmax-titanium-c-175_417.html', // UBNT Titanium
'http://landashop.com/catalog/airmax-10-ghz-c-175_416.html', // UBNT AirMAX10
'http://landashop.com/catalog/airmax-5-ghz-c-175_261.html', // AirMAX M5
'http://landashop.com/catalog/airmax-5-ghz-c-175_261.html?page=2', // AirMAX M5
'http://landashop.com/catalog/airmax-5-ghz-c-175_261.html?page=3', // AirMAX M5
'http://landashop.com/catalog/airmax-5-ghz-c-175_261.html?page=4', // AirMAX M5
'http://landashop.com/catalog/airmax-5-ghz-c-175_261.html?page=5', // AirMAX M5
'http://landashop.com/catalog/airmax-24-ghz-c-175_314.html', // AirMAX M2
'http://landashop.com/catalog/airmax-24-ghz-c-175_314.html?page=2', // AirMAX M2
'http://landashop.com/catalog/airmax-24-ghz-c-175_314.html?page=3', // AirMAX M2
'http://landashop.com/catalog/airmax-35-ghz-c-175_336.html', // AirMAX M3
'http://landashop.com/catalog/airmax-35-ghz-c-175_336.html?page=2', // AirMAX M3 2
'http://www.landashop.com/catalog/nanobeam-powerbeam-c-175_337.html', // NanoBeam & PowerBeam
'http://www.landashop.com/catalog/nanobeam-powerbeam-c-175_337.html?page=2', // NanoBeam & PowerBeam
'http://www.landashop.com/catalog/nanobeam-powerbeam-c-175_337.html?page=3', // NanoBeam & PowerBeam
'http://www.landashop.com/catalog/unifi-wifi-c-175_338.html', // UniFI
'http://www.landashop.com/catalog/unifi-wifi-c-175_338.html?page=2', // UniFI
'http://www.landashop.com/catalog/unifi-switches-c-175_526.html', // UniFI Switches
'http://landashop.com/catalog/nano-series-c-175_178.html?page=1', // Nano
'http://landashop.com/catalog/nano-series-c-175_178.html?page=2', // Nano
'http://landashop.com/catalog/powerbridges-c-175_179.html', // Powerbridges
'http://landashop.com/catalog/picostation-c-175_196.html', // PicoStation
'http://landashop.com/catalog/bullet-c-175_197.html', // Bullet
'http://landashop.com/catalog/unifi-camaras-c-175_300.html?page=1', // Airvision
'http://landashop.com/catalog/unifi-camaras-c-175_300.html?page=2', // Airvision
'http://www.landashop.com/catalog/tough-switches-c-175_423.html', // Toughswitches
'http://www.landashop.com/catalog/edgemax-c-175_442.html?page=1', // EdgeSwitches
'http://www.landashop.com/catalog/edgemax-c-175_442.html?page=2', // EdgeSwitches
'http://www.landashop.com/catalog/mfi-c-175_421.html', // mFI
'http://landashop.com/catalog/antenas-c-175_198.html?page=1', // Antenas
'http://landashop.com/catalog/antenas-c-175_198.html?page=2', // Antenas
'http://landashop.com/catalog/antenas-c-175_198.html?page=3', // Antenas
'http://landashop.com/catalog/soluciones-usuario-final-c-175_315.html', // Home
'http://landashop.com/catalog/minipci-wlanwifi-radios-c-175_176.html', // MiniPCI
'http://landashop.com/catalog/power-over-ethernet-c-175_254.html?page=1', // UBNT PoE
'http://landashop.com/catalog/power-over-ethernet-c-175_254.html?page=2', // UBNT PoE
'http://landashop.com/catalog/power-over-ethernet-c-175_254.html?page=3', // UBNT PoE
'http://landashop.com/catalog/power-over-ethernet-c-175_254.html?page=4', // UBNT PoE
'http://www.landashop.com/catalog/accesorios-c-175_352.html?page=1', // UBNT Accessories
'http://www.landashop.com/catalog/accesorios-c-175_352.html?page=2', // UBNT Accessories

// Mikrotik
'http://www.landashop.com/catalog/wireless-ac-c-182_508.html', // Wireless AC
'http://www.landashop.com/catalog/wireless-ac-c-182_508.html?page=2', // Wireless AC
'http://landashop.com/catalog/wireless-exterior-c-182_185.html', // Wireless exterior
'http://landashop.com/catalog/wireless-exterior-c-182_185.html?page=2',
'http://landashop.com/catalog/sistemas-wireless-c-182_185.html?page=3',
'http://www.landashop.com/catalog/wireless-interior-c-182_507.html', // Wireless Interior
'http://landashop.com/catalog/routers-c-182_186.html',
'http://landashop.com/catalog/routers-c-182_186.html?page=2',
'http://landashop.com/catalog/routers-c-182_186.html?page=3',
'http://landashop.com/catalog/switches-c-182_330.html', // Switches
'http://landashop.com/catalog/switches-c-182_330.html?page=2', // Todas Routerboards
'http://landashop.com/catalog/todas-routerboards-c-182_187.html', // Routerboards
'http://landashop.com/catalog/todas-routerboards-c-182_187.html?page=2',
'http://landashop.com/catalog/todas-routerboards-c-182_187.html?page=3',
'http://landashop.com/catalog/todas-routerboards-c-182_187.html?page=4',
'http://landashop.com/catalog/todas-routerboards-c-182_187.html?page=5',
'http://landashop.com/catalog/todas-routerboards-c-182_187.html?page=6',
'http://landashop.com/catalog/todas-routerboards-c-182_187.html?page=7',
'http://landashop.com/catalog/fibra-optica-c-182_450.html', // Fibra Optica
'http://landashop.com/catalog/minipci-minipcie-c-182_188.html', // Mini-PCI
'http://landashop.com/catalog/minipci-minipcie-c-182_188.html?page=2',
'http://landashop.com/catalog/alimentacion-electrica-c-182_222.html', // Alimentacion Electrica
'http://landashop.com/catalog/alimentacion-electrica-c-182_222.html?page=2',
'http://landashop.com/catalog/cajas-c-182_189.html',
'http://www.landashop.com/catalog/antenas-cables-c-182_192.html', // Antenas-cables
'http://www.landashop.com/catalog/antenas-cables-c-182_192.html?page=2',
'http://www.landashop.com/catalog/routeros-c-182_190.html', // RouterOS


// TP-Link
'http://landashop.com/catalog/fibra-optica-c-236_271.html', // Fibra Optica
'http://landashop.com/catalog/fibra-optica-c-236_271.html?page=2',

// Powerline
'http://www.landashop.com/catalog/tplink-c-738_739.html?page=1', // TP-Link
'http://www.landashop.com/catalog/tplink-c-738_739.html?page=2', // TP-Link
'http://www.landashop.com/catalog/edimax-c-738_740.html', // Edimax

// Antenas
'http://www.landashop.com/catalog/24-ghz-c-538_545.html', // Sectoriales 2.4
'http://www.landashop.com/catalog/35-ghz-c-538_546.html', // Sectoriales 3.5 GHz
'http://www.landashop.com/catalog/5-ghz-c-538_547.html', // Sectoriales 5GHz
'http://www.landashop.com/catalog/5-ghz-c-538_547.html?page=2', // Sectoriales 5GHz
'http://www.landashop.com/catalog/5-ghz-c-538_547.html?page=3', // Sectoriales 5GHz
'http://www.landashop.com/catalog/24-ghz-c-548_552.html', // Parabolica 2.4
'http://www.landashop.com/catalog/5-ghz-c-548_553.html?page=1', // Parablica 5
'http://www.landashop.com/catalog/5-ghz-c-548_553.html?page=2', // Parablica 5
'http://www.landashop.com/catalog/5-ghz-c-548_553.html?page=3', // Parablica 5
'http://www.landashop.com/catalog/5-ghz-c-548_553.html?page=4', // Parablica 5
'http://www.landashop.com/catalog/10-ghz-c-548_554.html', // Parabolica 10
'http://www.landashop.com/catalog/bandas-licenciadas-c-548_555.html', // Bandas Licenciadas
'http://www.landashop.com/catalog/24-ghz-c-556_557.html', // Panel 2.4
'http://www.landashop.com/catalog/35-ghz-c-556_558.html', // Panel 3.5
'http://www.landashop.com/catalog/5-ghz-c-556_559.html', // Panel 5
'http://www.landashop.com/catalog/24-ghz-c-560_565.html?page=1', // Omni 2.4
'http://www.landashop.com/catalog/24-ghz-c-560_565.html?page=2', // Omni 2.4
'http://www.landashop.com/catalog/24-ghz-c-560_565.html?page=3', // Omni 2.4
'http://www.landashop.com/catalog/banda-dual-c-560_564.html', // Omni Dual
'http://www.landashop.com/catalog/5-ghz-c-560_567.html', // Omni 5
'http://www.landashop.com/catalog/24-ghz-c-568_575.html?page=1', // Caja 2.4
'http://www.landashop.com/catalog/24-ghz-c-568_575.html?page=2', // Caja 2.4
'http://www.landashop.com/catalog/35-ghz-c-568_577.html', // Caja 3.5
'http://www.landashop.com/catalog/5-ghz-c-568_576.html?page=1', // Caja 5
'http://www.landashop.com/catalog/5-ghz-c-568_576.html?page=2', // Caja 5
'http://www.landashop.com/catalog/5-ghz-c-568_576.html?page=3', // Caja 5
'http://www.landashop.com/catalog/5-ghz-c-568_576.html?page=4', // Caja 5
'http://www.landashop.com/catalog/bana-dual-c-568_574.html', // Caja Dual

// Cables
'http://www.landashop.com/catalog/bobinas-utp-c-476_485.html?page=1', // Bobinas UTP
'http://www.landashop.com/catalog/bobinas-utp-c-476_485.html?page=2', // Bobinas UTP
'http://www.landashop.com/catalog/latiguillos-cat5e-c-476_477.html?page=1', // Latiguillos UTP CAT5E
'http://www.landashop.com/catalog/latiguillos-cat5e-c-476_477.html?page=2', // Latiguillos UTP CAT5E
'http://www.landashop.com/catalog/latiguillos-cat5e-c-476_477.html?page=3', // Latiguillos UTP CAT5E
'http://www.landashop.com/catalog/latiguillos-cat5e-c-476_477.html?page=4', // Latiguillos UTP CAT5E
'http://www.landashop.com/catalog/latiguillo-cat6-c-476_478.html?page=1', // Latigillos UTP CAT6
'http://www.landashop.com/catalog/latiguillo-cat6-c-476_478.html?page=2', // Latigillos UTP CAT6
'http://www.landashop.com/catalog/latiguillo-cat6-c-476_478.html?page=3', // Latigillos UTP CAT6
'http://www.landashop.com/catalog/latiguillo-fibra-optica-c-476_479.html?page=1', // Latiguillos FO
'http://www.landashop.com/catalog/latiguillo-fibra-optica-c-476_479.html?page=2', // Latiguillos FO
'http://www.landashop.com/catalog/latiguillo-fibra-optica-c-476_479.html?page=3', // Latiguillos FO
'http://www.landashop.com/catalog/antena-rf-c-476_480.html?page=1', // Cables RF Antena
'http://www.landashop.com/catalog/antena-rf-c-476_480.html?page=2', // Cables RF Antena
'http://www.landashop.com/catalog/antena-rf-c-476_480.html?page=3', // Cables RF Antena
'http://www.landashop.com/catalog/pigtails-rf-c-476_481.html?page=1', // Pigtail RF
'http://www.landashop.com/catalog/pigtails-rf-c-476_481.html?page=2', // Pigtail RF

// Fibra Optica
'http://www.landashop.com/catalog/gpon-olts-c-609_679.html', // GPON OLT
'http://www.landashop.com/catalog/gpon-onts-c-609_680.html', // GPON ONT
'http://www.landashop.com/catalog/epon-olts-c-609_747.html', // EPON OLT
'http://www.landashop.com/catalog/epon-onus-c-609_748.html', // EPON ONU
'http://www.landashop.com/catalog/modulos-c-609_677.html', // Modulos SFP
'http://www.landashop.com/catalog/modulos-c-609_677.html?page=2', // Modulos SFP
'http://www.landashop.com/catalog/cables-c-609_681.html?page=1', // Cables FO
'http://www.landashop.com/catalog/cables-c-609_681.html?page=2', // Cables FO
'http://www.landashop.com/catalog/cables-c-609_681.html?page=3', // Cables FO

// Switches Gigabit Ethernet
'http://www.landashop.com/catalog/gigabit-ethernet-c-339_340.html?page=1',
'http://www.landashop.com/catalog/gigabit-ethernet-c-339_340.html?page=2',
'http://www.landashop.com/catalog/gigabit-ethernet-c-339_340.html?page=3',
'http://www.landashop.com/catalog/gigabit-ethernet-c-339_340.html?page=4',
'http://www.landashop.com/catalog/gigabit-ethernet-c-339_340.html?page=5',

// Switches gestionables
'http://landashop.com/catalog/gestionables-c-339_343.html',
'http://landashop.com/catalog/gestionables-c-339_343.html?page=2',
'http://landashop.com/catalog/gestionables-c-339_343.html?page=2'

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
      $productTD->find('s',0)->innertext='';;
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
