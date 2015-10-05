window.onload = function(){
	if ((!!window.jQuery) == false) {
		var s = document.createElement("script");
		s.type = "text/javascript";
		s.src = "http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js";
		document.head.appendChild(s);
		setTimeout(function(){
			load_onglets();
		}, 2000);
	} else {
		load_onglets();
	}
};

function load_onglets() {
	$(function(){
		var blocs = $('#debug_tool #debug_barre .bloc');
		blocs.hover(function(){
			var id = $(this).attr('id').substr(12);
			$(this).css('z-index', '1002');
			var left = $(this).position().left -1;
			if (id != 'console' && id != 'base') { 
				$('#debug_tool #debug_tool_'+id).css('margin-left', left+'px');
			} else {
				var max_height = window.innerHeight - $('#debug_tool #debug_barre').height();
				$('#debug_tool #debug_tool_'+id).css('max-height', max_height +'px');
			}
			$('#debug_tool .bloc_info').not('#debug_tool_'+id).hide();
			$('#debug_tool #debug_barre .bloc').not('#debug_barre_'+id).css('z-index', '1000');
			$('#debug_tool #debug_tool_'+id).show();
		}, function(){
			var bloc = $(this);
			var id =  bloc.attr('id').substr(12);
			$('#debug_tool #debug_tool_'+id).hover(function(){}, function(){
				bloc.css('z-index', '1000');
				$('#debug_tool #debug_tool_'+id).hide();
			});
		});
	});	
}