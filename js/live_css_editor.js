(function($, exports){
	$(document).ready(function(){
		var api = wp.customize;
		var css_editor = ace.edit("css_editor");

		//css_editor.setTheme("ace/theme/twilight");
		css_editor.setTheme("ace/theme/github");
		css_editor.getSession().setMode("ace/mode/css");

		var editor = ace.edit("css_editor");
		var textarea = $('#css_editor_textarea').hide();
		editor.getSession().setValue(textarea.val());
		editor.getSession().on('change', function(){
			textarea.val(editor.getSession().getValue());
			textarea.trigger('change');
		});

		$('#accordion-panel-live_css_edit_panel' ).on('click', function(){
			$('#accordion-section-live_css_edit_section' ).addClass('open');
			$('#ccustomize-controls' ).addClass('css_editor_opened');
		});

		$('.control-panel-back' ).on('click', function() {
			$('#customize-controls' ).removeClass('css_editor_opened');
		});

		//css_editor.getSession().on('change', function(e) {
		//
		//	console.log('change');
		//
		//	//var currline = css_editor.getSelectionRange().start.row;
		//	//var wholelinetxt = css_editor.session.getLine(currline);
		//	//
		//	//var css_class = wholelinetxt.replace('{', '');
		//	//var element = $(css_class);
		//
		//
		//	//api.previewer.refresh();
		//	//if ( element.length > 0 ) {
		//	//	setTimeout(function(){
		//	//		api.previewer.trigger('highlight', css_class);
		//	//	}, 800);
		//	//}
		//	// @todo make this work multiline
		//
		//	//var cursor = css_editor.getCursorPosition();
		//	//
		//	//var aaaa = css_editor.find("/\}(.*)\{/gmisU",{
		//	//	backwards: true,
		//	//	wrap: true,
		//	//	caseSensitive: false,
		//	//	wholeWord: false,
		//	//	regExp: true
		//	//	//start: cursor
		//	//});
		//	//
		//	//debugger;
		//	//var bbbb = css_editor.findNext(aaaa);
		//	//var cccc = css_editor.findPrevious(aaaa);
		//
		//
		//	//var range = css_editor.find({
		//	//	needle: /[(}{)\[\]]/g,
		//	//	preventScroll: true,
		//	//	start: {row: cursor.row, column: cursor.column - 1 }
		//	//});
		//	//if(range)
		//	//	var text = css_editor.session.getTextRange(css_editor.session.getBracketRange(range.end));
		//	//console.log(text);
		//});


	});
})(jQuery, window);