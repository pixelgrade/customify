(function($, exports){

	var timeout = null;

	$(document).ready(function(){
		var api = wp.customize;
		var css_editor = ace.edit("css_editor");

		// init the ace editor
		css_editor.setTheme("ace/theme/github");
		css_editor.getSession().setMode("ace/mode/css");

		// hide the textarea and enable the ace editor
		var textarea = $('#css_editor_textarea').hide();
		css_editor.getSession().setValue(textarea.val());

		var customizer_overlay = $('.wp-full-overlay');

		// open the ace editor section when we click on the panel
		$('#accordion-section-live_css_edit_section').on('click', function(){
			customizer_overlay.addClass('editor_opened');
		});

		$(document).on('click', '.customize-section-back', function(){
			customizer_overlay.removeClass('editor_opened');
		});

		// each time a change is triggered start a timeout of 1,5s and when is finished refresh the previewer
		// if the user types faster than this delay then reset it
		css_editor.getSession().on('change', function(e) {
			if ( timeout !== null ){
				clearTimeout(timeout);
				timeout = null;
			} else {
				timeout = setTimeout( function(){
					//var state = css_editor.session.getState();
					textarea.val(css_editor.getSession().getValue());
					textarea.trigger('change');
				},1500);
			}
		});
	});
})(jQuery, window);