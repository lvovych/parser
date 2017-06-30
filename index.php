<?php

require __DIR__.'/vendor/autoload.php';

/**
 * @param $url
 * @return array
 */
function getPages ($url) {
    $result=[];
    array_push($result,$url);

    $html = file_get_html($url);
    $links = $html->find('div.links',0);
//    $links = $links[0]->children;
    foreach ($links->find('a') as $link) {
        if ( $link->href && $link->nodes[0]->_[4] !== '&gt;' && $link->nodes[0]->_[4] !== '&gt;|' ) {

            array_push($result,html_entity_decode($link->href));

        }
    }

    return $result;
}

/**
 * @param $name
 * @return string
 */
function parseFutures ($name) {
    $result = '';
    $type = '';
    $killZhyl = '';
    $pereriz = '';


    $arr = explode(" ", $name);
    switch ($arr[0]) {
        case 'АПВ-10':
            $result = 'Тип:АПВ:1,Кількість жил:1:2,Переріз:10:3';
            $arr = [];
            break;
        case 'АПВ-2,5':
            $result = 'Тип:АПВ:1,Кількість жил:1:2,Переріз:2.5:3';
            $arr = [];
            break;
        case 'АПВ-4':
            $result = 'Тип:АПВ:1,Кількість жил:1:2,Переріз:4:3';
            $arr = [];
            break;
        case 'АПВ-6':
            $result = 'Тип:АПВ:1,Кількість жил:1:2,Переріз:6:3';
            $arr = [];
            break;
        default:
            $type = $arr[0];
            unset($arr[0]);

            foreach ($arr as $item){
                if ($item === '') {
                    continue;
                }
                if (stristr($item,'*',true)) {
                    $pereriz = str_replace(['*',','], ['','.'],stristr($item,'*',false));
                    $killZhyl = stristr($item,'*',true);
                    continue;
                }
                if (stristr($item,'х',true)) {
                    $pereriz = str_replace(['х',','], ['','.'],stristr($item,'х',false));
                    $killZhyl = stristr($item,'х',true);
                    continue;
                }
                if (stristr($item,'Х',true)) {
                    $pereriz = str_replace(['Х',','], ['','.'],stristr($item,'Х',false));
                    $killZhyl = stristr($item,'Х',true);
                    continue;
                }
                if ( ( preg_match("~[1-9]{1,2}~ui", $item) || preg_match("~[1-9],[1-9]{1,2}~ui", $item) ) && stristr($type,'ПВ-') )
                    {
                    $killZhyl = 1;
                    $pereriz = str_replace(',', '.', $item);
                    }

                switch ($item){
                    case 'П':
                        $type .= ' п';
                        continue;
                    case 'п':
                        $type .= ' п';
                        continue;
                    case 'нгд':
                        $type .= ' нгд';
                        continue;
                    case 'нг':
                        $type .= ' нг';
                        continue;
                    case 'НГ':
                        $type .= ' нг';
                        continue;
                    case 'нгд':
                        $type .= ' нг';
                        continue;
                    case 'НГД':
                        $type .= ' нг';
                        continue;
                    default:
                        break;
                }

                if (strripos($type,'ПВ') && $pereriz ==='') {
                    $pereriz = str_replace(',', '.',$item);
                    continue;
                }
            }
            $result = 'Тип:' . $type . ':1';
            if ($killZhyl !== ''){
                $result .=',Кількість жил:' . intval($killZhyl) . ':2';
            }
            if ($pereriz !== '') {
                $result .= ',Переріз:' . floatval($pereriz) . ':3';
            }
            break;
    }
     return $result;
}

/**
 * @param $products
 */
function setExcel ($products) {
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Name *');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Active(0/1)');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Categories (x,y,z...)');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Price tax included');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Cost');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Manufacturer');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Quantity');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Minimal quantity');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Reference #');
    $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Visibility');
    $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Show price (0 = No, 1 = Yes)');
    $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Meta title');
    $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Meta keywords');
    $objPHPExcel->getActiveSheet()->setCellValue('N1', 'Image URLs (x,y,z...)');
    $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Image alt texts (x,y,z...)');
    $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Delete existing images (0 = No, 1 = Yes)');
    $objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Feature(Name:Value:Position)');

    $i=2;
    foreach ($products as $product) {
        $objPHPExcel->getActiveSheet()->setCellValue("A$i", $product['name']);
        $objPHPExcel->getActiveSheet()->setCellValue("B$i", '1');
        $objPHPExcel->getActiveSheet()->setCellValue("C$i", '33,5');
        $objPHPExcel->getActiveSheet()->setCellValue("D$i", $product['price']);
        $objPHPExcel->getActiveSheet()->setCellValue("E$i", bcmul($product['price'],'0.87',2));
        $objPHPExcel->getActiveSheet()->setCellValue("F$i", 'Каблекс Україна');
        $objPHPExcel->getActiveSheet()->setCellValue("G$i", '1000');
        $objPHPExcel->getActiveSheet()->setCellValue("H$i", '5');
        $objPHPExcel->getActiveSheet()->setCellValue("I$i", $product['sku']);
        $objPHPExcel->getActiveSheet()->setCellValue("J$i", 'both');
        $objPHPExcel->getActiveSheet()->setCellValue("K$i", '1');
        $objPHPExcel->getActiveSheet()->setCellValue("L$i", $product['name']);
        $objPHPExcel->getActiveSheet()->setCellValue("M$i", $product['name']);
        $objPHPExcel->getActiveSheet()->setCellValue("N$i", $product['image']);
        $objPHPExcel->getActiveSheet()->setCellValue("O$i", $product['name']);
        $objPHPExcel->getActiveSheet()->setCellValue("P$i", '1');
        $objPHPExcel->getActiveSheet()->setCellValue("Q$i", $product['futures']);
        $i++;
    }

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $objWriter->save(__DIR__ . '/output/Kablex_'.date("Ymd").'.xlsx');
}

function parseKablex ($url){
    $pages = getPages($url);
    $products = [];
    foreach ($pages as $page) {
        $html = file_get_html($page);
        $articles = $html->find('div.product-list');
        $articles = $articles[0]->children;
        foreach ($articles as $article) {
            foreach ($article->children as $div) {
                switch ($div->attr['class']) {
                    case 'image':
                        $product['image'] = $div->nodes[1]->nodes[0]->attr['src'];
                        break;
                    case 'name':
                        $product['name'] = $div->nodes[0]->nodes[0]->_[4];
                        $product['name'] = str_replace(['Черный Каблекс','Каблекс черн.','Каблекс черній','Оранжевый  Каблекс','Оранжевый   Каблекс','КАБЛЕКС'], ['чорний Каблекс','чорний Каблекс','чорний Каблекс','оранжевий Каблекс','оранжевий Каблекс','Каблекс'], $product['name']);
                        var_dump($product['name']);
                        break;
                    case 'price':
                        $product['price'] = trim(substr(preg_replace("/(\s|\t|\r|\n|грн)+/",'', $div->nodes[0]->_[4]),0,-2));
                        $product['price_type'] = trim($div->nodes[1]->nodes[0]->_[4]);
                        break;
                    case 'more':
                        mb_internal_encoding("UTF-8");
                        $pUrl = $div->find('a',0)->href;
                        $pBlock = file_get_html($pUrl);
                        $product['sku'] = $pBlock->find('div.infoleft',0)->plaintext;
                        $product['sku'] = stristr($product['sku'],"\n\t\t\t\t\t\t\t\tПроизводитель:",true);
                        $product['sku'] = mb_substr(stristr($product['sku'],"Код:",false),5);
                        break;
                }

            }
            $product['futures'] = parseFutures($product['name']);
            array_push($products,$product);
        }
    }
    setExcel($products);
}
parseKablex('http://kablex.net.ua/cm-kabelno-provodnikovaja-produkcija/?filter_ocfilter=m:65');





