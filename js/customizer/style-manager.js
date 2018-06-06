(
	function( $, exports, wp ) {
		var api = wp.customize;
		var $window = $( window );

		wp.customize.bind( 'ready', function() {

			// Handle the Style Manager user feedback logic.
            var $styleManagerUserFeedbackModal = $('#style-manager-user-feedback-modal');
            if ( $styleManagerUserFeedbackModal.length ) {
                var $styleManagerUserFeedbackForm = $styleManagerUserFeedbackModal.find('form'),
                    $styleManagerUserFeedbackCloseBtn = $styleManagerUserFeedbackModal.find('.close'),
                    $styleManagerUserFeedbackFirstStep = $styleManagerUserFeedbackModal.find('.first-step'),
                    $styleManagerUserFeedbackSecondStep = $styleManagerUserFeedbackModal.find('.second-step'),
                    $styleManagerUserFeedbackThanksStep = $styleManagerUserFeedbackModal.find('.thanks-step'),
                    $styleManagerUserFeedbackErrorStep = $styleManagerUserFeedbackModal.find('.error-step'),
                    styleManagerUserFeedbackModalShown = false,
                    styleManagerColorPaletteChanged = false;

                // Handle when to open the modal.
                api.bind('saved', function () {
                    // We will only show the modal once per Customizer session.
                    if (!styleManagerUserFeedbackModalShown && styleManagerColorPaletteChanged) {
                        $('body').addClass('modal-open');
                        styleManagerUserFeedbackModalShown = true;
                    }
                });

                // Handle the color palette changed info update.
                const colorPaletteSetting = api( 'sm_color_palette' );
                if ( !_.isUndefined(colorPaletteSetting) ) {
                    colorPaletteSetting.bind( function( new_value, old_value ) {
                        if ( new_value != old_value ) {
                            styleManagerColorPaletteChanged = true;
                        }
                    } )
                }
                const colorPaletteVariationSetting = api( 'sm_color_palette_variation' );
                if ( !_.isUndefined(colorPaletteVariationSetting) ) {
                    colorPaletteVariationSetting.bind( function( new_value, old_value ) {
                        if ( new_value != old_value ) {
                            styleManagerColorPaletteChanged = true;
                        }
                    } )
                }

                // Handle the modal submit.
                $styleManagerUserFeedbackForm.on('submit', function (event) {
                    event.preventDefault();

                    let $form = $(event.target);

                    let data = {
                        action: 'customify_style_manager_user_feedback',
                        nonce: customify_settings.style_manager_user_feedback_nonce,
                        type: $form.find('input[name=type]').val(),
                        rating: $form.find('input[name=rating]:checked').val(),
                        message: $form.find('textarea[name=message]').val()
                    };

                    $.post(
                        customify_settings.ajax_url,
                        data,
                        function (response) {
                            if (true === response.success) {
                                $styleManagerUserFeedbackFirstStep.hide();
                                $styleManagerUserFeedbackSecondStep.hide();
                                $styleManagerUserFeedbackThanksStep.show();
                                $styleManagerUserFeedbackErrorStep.hide();
                            } else {
                                $styleManagerUserFeedbackFirstStep.hide();
                                $styleManagerUserFeedbackSecondStep.hide();
                                $styleManagerUserFeedbackThanksStep.hide();
                                $styleManagerUserFeedbackErrorStep.show();
                            }
                        }
                    );
                });

                $styleManagerUserFeedbackForm.find('input[name=rating]').on('change', function (event) {
                    // Leave everything in working order
                    setTimeout(function () {
                        $styleManagerUserFeedbackSecondStep.show();
                    }, 300);

                    let rating = $styleManagerUserFeedbackForm.find('input[name=rating]:checked').val();

                    $styleManagerUserFeedbackForm.find('.rating-placeholder').text(rating);
                });

                $styleManagerUserFeedbackCloseBtn.on('click', function (event) {
                    event.preventDefault();

                    $('body').removeClass('modal-open');

                    // Leave everything in working order
                    setTimeout(function () {
                        $styleManagerUserFeedbackFirstStep.show();
                        $styleManagerUserFeedbackSecondStep.hide();
                        $styleManagerUserFeedbackThanksStep.hide();
                        $styleManagerUserFeedbackErrorStep.hide();
                    }, 300);
                });
            }
		} );
	}
)( jQuery, window, wp );


// Reverses a hex color to either black or white
function customifyInverseHexColorToBlackOrWhite( hex ) {
	return customifyInverseHexColor( hex, true );
}

// Taken from here: https://stackoverflow.com/a/35970186/6260836
function customifyInverseHexColor( hex, bw ) {
	if ( hex.indexOf( '#' ) === 0 ) {
		hex = hex.slice( 1 );
	}
	// convert 3-digit hex to 6-digits.
	if ( hex.length === 3 ) {
		hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
	}
	if ( hex.length !== 6 ) {
		throw new Error( 'Invalid HEX color.' );
	}
	var r = parseInt( hex.slice( 0, 2 ), 16 ),
		g = parseInt( hex.slice( 2, 4 ), 16 ),
		b = parseInt( hex.slice( 4, 6 ), 16 );
	if ( bw ) {
		// http://stackoverflow.com/a/3943023/112731
		return (
			       r * 0.299 + g * 0.587 + b * 0.114
		       ) > 186
			? '#000000'
			: '#FFFFFF';
	}
	// invert color components
	r = (
		255 - r
	).toString( 16 );
	g = (
		255 - g
	).toString( 16 );
	b = (
		255 - b
	).toString( 16 );
	// pad each with zeros and return
	return "#" + customifyPadZero( r ) + customifyPadZero( g ) + customifyPadZero( b );
}

function customifyPadZero( str, len ) {
	len = len || 2;
	var zeros = new Array( len ).join( '0' );
	return (
		zeros + str
	).slice( - len );
}
