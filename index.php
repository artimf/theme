<? 
header("Content-Type: text/html; charset=utf-8");
//http://habrahabr.ru/post/153731/
session_start();
$_SESSION['PARSE'] = '';

//Читаем все ID в массив , сохраняем его в сессионую переменную, чтобы потом исключать уже сохраненные позиции
$cash_file="items_file.csv";
$in = array();
$_SESSION['ARR'] = $in;
if (file_exists($cash_file)){
	$fp = fopen ($cash_file,"r"); 
	while ($data = fgetcsv ($fp, 10000, ";")) $in[] = $data[0];
	$_SESSION['ARR'] = $in;
	fclose($fp);
}
echo '<!DOCTYPE html>';
?>

<html>
  <head>
    <title>ScriptOffset</title>
    <script type="text/javascript" src="./js/jquery.min.js"></script>
    <script type="text/javascript" src="./js/scriptoffset.js"></script>
    <link rel="stylesheet" type="text/css" href="./js/scriptoffset.css">
  </head>
  <body>

	<div class="form">
	  <input id="url" name="url" value ="http://themeforest.net/category/cms-themes?page=[DD]" style="width: 400px;">
	  <!-- http://themeforest.net/category/wordpress?page=[DD] -->
      <input id="offset" name="offset" type="hidden">
      <div class="progress" style="display: none;">
		<div class="bar" style="width: 0%;"></div>
      </div>
	  <p>
		<input id="pages" name="pages" value ="1,2,3" style="width: 400px;">
	  </p>
      <a href="#" id="runScript"  class="btn" data-action="run">Start</a>
      <a href="#" id="refreshScript" class="btn" style="display: none;">Restart</a> 
    </div>

	<div class="info"></div> 
	<a href="1.php" target="_blanck" data-action="run">1php</a>
  </body>
</html>