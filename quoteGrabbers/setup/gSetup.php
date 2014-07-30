<?php

include '../includes/simple_html_dom.php';
include '../includes/utils.php';

$urls = array(

  // Ubiquity
  'http://shop.setup.cat/63-airmax-ubiquiti',

  // Routerboards Mikrotik
  'http://shop.setup.cat/64-routerboards',

  // Antenes
  'http://shop.setup.cat/68-antena-24ghz',
  'http://shop.setup.cat/69-antena-5ghz',

  // Caixes
  'http://shop.setup.cat/65-caixes',

  // CPE
  'http://shop.setup.cat/70-cpe-24ghz',
  'http://shop.setup.cat/71-cpe-5ghz',

  // MiniPCI
  'http://shop.setup.cat/75-minipci',

);

function grab_setup($url) {
  $html = file_get_html($url);

  foreach($html->find('ul[id=product_list] li') as $product) {

    // Obtinc el cost
    $costE = $product->find('div[class=content_price] span[class=price]');
    $cost = str_replace(',','.',str_replace(' €','',str_replace('.','',$costE[0]->plaintext)));

    //Obtinc el titol i la url del producte
    foreach($product->find('a[class=product_img_link]') as $productDescr) {
      $title=$productDescr->title;
      $srclink=$productDescr->href;
    }

    // PartNo
    $iname = explode('/',$srclink);
    $part = explode('-',$iname[count($iname)-1]);
    $partNo = $part[0].'-SETUP';


    // Obtinc la imatge
    foreach($product->find('img') as $img) {
      $iname = explode('/',$img->src);
      $fname = $iname[count($iname)-1];
      grab_image($img->src,'../images/'.$fname);
    }

    // Trec l'IVA (21%)
    $cost = ($cost * 100) / 121;

    if (!empty($title) and ($cost))  {
      $cos = '<a href="'.$srclink.'" alt="'.$title.'" title="'.$title.'"><img src="/files/catalog/'.$fname.'" width=124 height=124 align="right"></a>';
      echo "$partNo|$title|$cos|21|$cost\n";
    }

  }
}

foreach ($urls as $u)
  grab_setup($u);

?>
