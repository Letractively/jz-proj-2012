<?php

		include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/en.php";

		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php")) {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php";
		}

		foreach ($smilies_language as $i => $l) {
			$smilies_language[$i] = str_replace("'", "\'", $l);
		}
?>

/*
 * CometChat
 * Copyright (c) 2012 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

(function($){   
  
	$.ccsmilies = (function () {

		var title = '<?php echo $smilies_language[0];?>';

        return {

			getTitle: function() {
				return title;	
			},

			init: function (id) {
				baseUrl = getBaseUrl();
				loadCCPopup(baseUrl+'plugins/smilies/index.php?chatroommode=1&id='+id, 'smilies',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=220,height=200",220,150,'<?php echo $smilies_language[1];?>');  
			},

			addtext: function (id,text) {

				var string = $('#currentroom .cometchat_textarea').val();
				
				if (string.charAt(string.length-1) == ' ') {
					$('#currentroom .cometchat_textarea').val($('#currentroom .cometchat_textarea').val()+text);
				} else {
					if (string.length == 0) {
						$('#currentroom .cometchat_textarea').val(text);
					} else {
						$('#currentroom .cometchat_textarea').val($('#currentroom .cometchat_textarea').val()+' '+text);
					}
				}
				
				$('#currentroom .cometchat_textarea').focus();
				
			}

        };
    })();
 
})(jqcc);