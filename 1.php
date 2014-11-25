<?header("Content-Type: text/html; charset=utf-8"); ?>
<head>
    <title>1</title>
	<link href="js/bootstrap.min.css" rel="stylesheet"> 
    <script type="text/javascript" src="./js/jquery.min.js"></script>
	<script>
//console.log(arr.bad_id);

function ellAdd (ellement,arr){
		$(ellement).empty();
    	$.each(arr, function(key, value) {
		  $(ellement).append("<tr><td>"+value+"</td></tr>"); 
		});
}

		$(document).ready(function() {

			var arr = []; 
			arr["bad_id"]=[];
			arr["bad_url"]=[];

		    $('.delete').click(function() {
					var id =$(this).attr("id");
					var bu =$(this).attr("badurl");
					$('.'+id).css('color','red');
 
					if($.inArray(id, arr.bad_id)<0) {
						arr.bad_id.push(id); arr.bad_url.push(bu);
						ellAdd ('#bad',arr.bad_id);
						ellAdd ('#bad_url',arr.bad_url);
					}
					$("#count").text(arr.bad_id.length);
				});

			$('.back').click(function() {
					var id =$(this).attr("id");
					var bu =$(this).attr("badurl");
					if ((idx =arr.bad_id.indexOf(""+id+""))>=0) {
						arr.bad_id.splice(idx, 1);$('.'+id).css('color','green');
					}
					if ((idx =arr.bad_url.indexOf(""+bu+""))>=0) { 
						arr.bad_url.splice(idx, 1);
					}
					ellAdd('#bad',arr.bad_id);
					ellAdd('#bad_url',arr.bad_url);					
					$("#count").text(arr.bad_id.length);
				});

				$('.btn-link').click(function() { 
					$(this).css('color','green');
				});

		});

console.time('create list');  
  
for (i = 0; i < 1000; i++) {  
    var myList = $('.myList');  
    myList.append('This is list item ' + i);  
}  
console.timeEnd('create list');
	</script>
</head>
<html>

<div class="row table table-striped">
  <div class="col-md-6"> 
<?php
		include_once 'simplehtmldom/simple_html_dom.php';
		$HTML = file_get_html('1.html');
		 $i=0;
		 foreach($HTML->find('a')  as $a)
			   {
				  $id=substr($a->href,strpos($a->href,'&itm=')+5);
				  $a->plaintext=$id;
				  $at=$a->outertext;

				  $href_s=str_replace("http://www.theme4u.ru/prev.php?frm=http://", "",  $a->href);
				  $href_s=str_replace("&itm=","/&itm=",$href_s);
				  $href_s=substr($href_s,0,min(strpos($href_s,'&itm='),strpos($href_s,'/')));
				  $i++;
				  echo "<p class='pp bg-info'>$i<a href = '$a->href' target='_blanck' class='btn btn-link $id' id='$id'>$id</a>
											  <button type='button' class='btn btn-danger btn-xs  delete' id='$id' badurl='$href_s'>Удалить</button>
											  <button type='button' class='btn btn-success btn-xs back'   id='$id' badurl='$href_s'>Вернуть</button>
											  $href_s</p>";
			   }

?>
  </div>
  <div class="col-md-2" id="block"> 
	    <table class="table table-bordered">
		 <tbody id = "bad"></tbody>
		</table>
	<div id = "count"></div>
  </div>
  <div class="col-md-4" id="block2"> 
	    <table class="table table-striped table-bordered">
		 <tbody id = "bad_url"></tbody>
		</table>
  </div>
</div>

</html>