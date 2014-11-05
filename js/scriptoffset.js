function setCookie (url, offset){
    	var ws=new Date();
        if (!offset && !url) {
                ws.setMinutes(10-ws.getMinutes());
            } else {
                ws.setMinutes(10+ws.getMinutes());
            }
        document.cookie="scriptOffsetUrl="+url+";expires="+ws.toGMTString();
        document.cookie="scriptOffsetOffset="+offset+";expires="+ws.toGMTString();
    }
    
function getCookie(name) {
        var cookie = " " + document.cookie;
        var search = " " + name + "=";
        var setStr = null;
        var offset = 0;
        var end = 0;
        if (cookie.length > 0) {
            offset = cookie.indexOf(search);
            if (offset != -1) {
                offset += search.length;
                end = cookie.indexOf(";", offset)
                if (end == -1) {
                    end = cookie.length;
                }
                setStr = unescape(cookie.substring(offset, end));
            }
        }
        return(setStr);
    }

function showProcess (url, sucsess, offset, action) {
        $('#url, #refreshScript').hide();
        $('.progress').show();
        $('#runScript').text('Stop!');
        $('.bar').text(url);
        $('.bar').css('width', sucsess * 100 + '%');
        setCookie(url, offset);

        $('#runScript').click(function(){
                document.location.href=document.location.href
          });

        scriptOffset(url, offset, action);
    }

function trn () {

	if (myFish.length == 0) return false;
	var action = $('#runScript').data('action');
	var offset = $('#offset').val();
	var url = $('#url').val();
	var pageId= myFish.shift();
	url = url.replace('[DD]', pageId);//выбираем по одному элементу из массива

	if ($('#url').val() != getCookie("scriptOffsetUrl")) {
			setCookie();
			scriptOffset(url, 0, action);
			if (bool) {
				$(".info").append("<p>" + url + "</p>");
				if (myFish.length==0){$(".info").append("<p>All</p>");bool=false; }
			}
		} else {
			scriptOffset(url, offset, action);
		}
	
	return false;
} 


function scriptOffset (url, offset, action) {
        $.ajax({
            url: "scriptoffset.php",
            type: "POST",
            data: {
                "action":action
              , "url":url
              , "offset":offset
            },
            success: function(data){
                data = $.parseJSON(data);
                if(data.sucsess < 1) {
                    showProcess(url, data.sucsess, data.offset, action);
                    } else {
						setCookie();
						$('.bar').css('width','100%');
						$('.bar').text('OK');
						$('#runScript').text('...'); 
						trn();
                    }
            }
        });
    }

$(document).ready(function() {

    var url = getCookie("scriptOffsetUrl");
    var offset = getCookie("scriptOffsetOffset"); 
	bool = true;

    if (url && url != 'undefined') {		
            $('#refreshScript').show();
            $('#runScript').text('Продолжить');
            $('#url').val(url);
            $('#offset').val(offset);
        }

   $('#runScript').click(function() {
		   //myFish = ["1","2","3"];
			var str = $('#pages').val();
			myFish = str.split(',');
            trn();
        });

    $('#refreshScript').click(function() {
            var action = $('#runScript').data('action');
            var url = $('#url').val();
			
            setCookie();
            scriptOffset(url, 0, action);
            return false;
        });

});