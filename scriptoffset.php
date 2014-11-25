<?php

/*
http://marketplace.envato.com/api/edge/item:8745522.json

После импорта сделать апдейт базы, т.к. при импорте отрезается target="_blank"

Имя базы:     gb_tech_art
 сервер:       mysql61.1gb.ru
 пароль:       f570d606qwr
 phpMyAdmin:   http://mysql61.1gb.ru  (адаптирован для русских кодировок)
 phpMyAdmin:   https://pma.1gb.ru  (последняя версия, https, для современных баз)

UPDATE  `ps_product_lang`
SET  `description_short` = REPLACE(`description_short`, '>Demo<', 'target="_blank">Demo<')

*/
session_start();
if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {return;}// Отвечаем только на Ajax
$action = $_POST['action']; if (empty($action)) {return;}			// Можно передавать в скрипт разный action и в соответствии с ним выполнять разные действия.
include_once 'simplehtmldom/simple_html_dom.php';

$url = $_POST['url']; if (empty($url)) return;					    // Получаем от клиента номер итерации
$offset = $_POST['offset'];

if (strlen($_SESSION['PARSE'])==0){									//до первого запуска
	$HTML1 = $url;
	$HTML1 = file_get_html("$HTML1");
	$_SESSION['PARSE'] = $HTML1->find('ul.item-list', 0)->outertext; //Сохраняем контейнер документа
}

function str_clear($str){
	$str = str_replace("&amp;", "&",  $str); 
	$str = str_replace(" / ", " ",  $str); 
	$str = str_replace(";", "",  $str);
	return $str;
}

$output_file="items_file.csv";										   //выходной файл
file_exists($output_file) ? $is_file= true : $is_file= false; 
$f = fopen($output_file, "a"); 

if (!$is_file) 														   //в новый файл пишем заголовок
fwrite($f, 'ITM_ID;Active (0/1);Name *;Categories (x,y,z...);Price tax excluded or Price tax included;Tax rules ID;Wholesale price;On sale (0/1);Discount amount;Discount percent;Discount from (yyyy-mm-dd);Discount to (yyyy-mm-dd);Reference;Supplier reference;Supplier;Manufacturer;EAN13;UPC;Ecotax;Width;Height;Depth;Weight;Quantity;Minimal quantity;Visibility;Additional shipping cost;Unity;Unit price;Short description;Description;Tags (x,y,z...);Meta title;Meta keywords;Meta description;URL rewritten;Text when in stock;Text when backorder allowed;Available for order (0 = No, 1 = Yes);Product available date;Product creation date;Show price (0 = No, 1 = Yes);Image URLs (x,y,z...);Delete existing images (0 = No, 1 = Yes);Feature(Name:Value:Position);Available online only (0 = No, 1 = Yes);Condition;Customizable (0 = No, 1 = Yes);Uploadable files (0 = No, 1 = Yes);Text fields (0 = No, 1 = Yes);Out of stock;ID / Name of shop;Advanced stock management;Depends On Stock;Warehouse'."\r\n");

$S_HTML1 = str_get_html($_SESSION['PARSE']);
$step = 1;
$count = 30;   													   	   //элементов на странице

if ($offset >= $count) {										       // Проверяем, все ли строки обработаны
  $sucsess = 1;
  $_SESSION['PARSE']='';
} else {

				$li=$S_HTML1->find('li[data-item-id]',0);	      	   //$S_HTML1 - контейнер, уменьшается после каждого прохода
				$itm = $li->find('div.item-info',0)->find('a',0);
				$ITEM_NAME = str_clear($itm->plaintext); 
				$ITEM_URL  = str_clear($itm->href);
				$i=0;$ii=0;
				foreach($li->find('span.meta-categories',0)->find('a') as $item_c)   $i++;
				foreach($li->find('span.meta-categories',0)->find('a') as $item_c) { $ii++;
					if ($ii < $i) $ITEM_CATEGORYS=$ITEM_CATEGORYS.$item_c->innertext.','; else  $ITEM_CATEGORYS=$ITEM_CATEGORYS.$item_c->innertext; 
				}
				$ITEM_CATEGORYS=str_clear($ITEM_CATEGORYS);

				$li->innertext='';
				$ITEM_ID = preg_replace("/[^0-9]/", '', $li->outertext); 

				//ищем ID в этом массиве уже обработанных, если нашли, то пропускаем расчет  $xx=$_SESSION['ARR'];
				if (!in_array($ITEM_ID, $_SESSION['ARR']))
				{
					   if (!strpos($ITEM_URL, 'themeforest.net')) 
					    $HTML2 = file_get_html('http://themeforest.net'.$ITEM_URL);
					     else  $HTML2 = file_get_html($ITEM_URL);/**/ 
					//$HTML2 = file_get_html('http://localhost/theme4u/testV2/testItem/item8513578.htm');

						   foreach($HTML2->find('table.meta-attributes__table',0)->find('tr') as $tr) 
						   {	
								$dscr_name=$tr->find('td.meta-attributes__attr-name',0)->plaintext; 
								$dscr_value=trim($tr->find('td.meta-attributes__attr-detail',0)->plaintext);
								$dscr_ar = array( 'Columns' => 'Columns'
												 ,'Compatible Browsers' => 'Browsers'
												 ,'Software Version' => 'Software'
												 ,'Compatible With' => 'Compatible'
												 ,'High Resolution' => 'High Resolution'
												 ,'Layout' => 'Layout'
												 ,'ThemeForest Files Included'=>'Files Included'
												);
								if(strpos($dscr_name, $dscr_ar[$dscr_name]) !== false){
									$xxx= $xxx."<p>$dscr_ar[$dscr_name]: $dscr_value</p>"; 
								}
								if(strpos($dscr_name, 'Tags') !== false){
									$TAGS=str_clear($dscr_value); 
								}
						   }

						$SALES=trim($HTML2->find('strong.sidebar-stats__number',0)->plaintext);
						$FULLSCREEN_URL=$HTML2->find('div#fullscreen',0)->find('a',0)->href;

							$PRICE = str_replace('$','', $HTML2->find('strong.purchase-form__price',0)->plaintext);
							$BIG_IMG_NAME = $HTML2->find('img',0)->src; 
							$IMG_NAME = str_replace("https", "http", $BIG_IMG_NAME);

							/* * /
									$url = "http://marketplace.envato.com/api/v3/item:$ITEM_ID.json";
									$content = file_get_contents($url); 
										{
											$json = json_decode($content, true);
											foreach($json as $i){
												  $ITEM_NAME  = $i['item'];
												  $ITEM_CATEGORYS  = str_replace('/',',',$i['category']);
												  $PRICE  = str_replace('.00','',$i['cost']);
												  $IMG_NAME = str_replace("https", "http",  $i['live_preview_url']); //.','.$i['thumbnail']
												  $TAGS= $i['tags'];
												  $SALES= $i['sales'];
												  $RATING= $i['rating_decimal'];
											}
										}/**/
						/* */
						$HTML3 = file_get_html('http://themeforest.net'.$FULLSCREEN_URL);//$HTML3  = file_get_html('http://dishop/parser2/soouse/html3.htm');
						$ext_u = ' '; 
						$ext_u = ($HTML3->find('iframe',0)->src); 
						/**/

						$ext_url_param = "http://www.theme4u.ru/prev.php?frm=$ext_u&itm=$ITEM_ID";
						$EXT_URL_LIVE_PREV=str_clear('<p><a href="'.$ext_url_param.'" target="_blank">Demo</a></p><p>Sale:'.$SALES.'</p>'.$xxx);
						$ext_u=str_clear($ext_u);

						$SID=session_id();
						if ((!strpos($ext_u, '?')) and (!strpos($ext_u, 'teamkraftt.com')))
						//fwrite($f,"$ITEM_CATEGORYS;$PRICE;;$PRICE;0;;;;;$ITEM_ID;;;;;;;;;;;100;1;;;;;$EXT_URL_LIVE_PREV;;$TAGS;Theme4u шаблон для сайта $ITEM_NAME;$ITEM_CATEGORYS;$ITEM_NAME - $ITEM_CATEGORYS;;;Current supply. Ordering availlable;1;;;1;$IMG_NAME;0;;0;new;0;0;0;0;0;0;0;0"."\r\n");
						fwrite($f,"$ITEM_ID;$SID;$ITEM_NAME;$ITEM_CATEGORYS;$PRICE;;$PRICE;0;;;;;$ITEM_ID;;;;;;;;;;;100;1;;;;;$EXT_URL_LIVE_PREV;;$TAGS;Theme4u шаблон для сайта $ITEM_NAME;$ITEM_CATEGORYS;$ITEM_NAME - $ITEM_CATEGORYS;;;Current supply. Ordering availlable;1;;;1;$IMG_NAME;0;;0;new;0;0;0;0;0;0;0;0"."\r\n");
				}

				$S_HTML1->find('li[data-item-id]',0)->outertext=''; //контейнер уменьшается после каждого прохода
				$_SESSION['PARSE'] = $S_HTML1->outertext;			//контейнер уменьшается после каждого прохода
				$sucsess = round($offset / $count, 2);

} /**/
$offset = $offset + $step;
fclose($f);
$output = Array('offset' => $offset, 'sucsess' => $sucsess); 		// И возвращаем клиенту данные (номер итерации и сообщение об окончании работы скрипта)
echo json_encode($output);