import $ from "jquery";
import _ from "lodash";
import * as globalService from "../global-service";

import {
  callbackFilter,
  getFontDetails,
  getSettingID,
  handleFontPopupToggle,
  initSubfield,
  loadFontValue,
  selfUpdateValue,
  updateFontHeadTitle,
  updateVariantField,
  fontsService,
} from './utils'

const wrapperSelector = '.font-options__wrapper';
const fontVariantSelector = '.customify_font_weight';

wp.customize.bind( 'ready', () => {
  initializeFonts();
  reloadConnectedFields();
} );

export const initializeFonts = function() {


  const $fontFields = $( wrapperSelector );

  $fontFields.each( ( i, obj ) => {
    const $fontField = $( obj );

    initializeFontFamilyField( $fontField );
    initializeSubfields( $fontField );
  } );

  handleFontPopupToggle();
}

const initializeFontFamilyField = ( $fontField ) => {
  const $fontFamilyField = $fontField.find( '.customify_font_family' );
  const familyPlaceholderText = customify.l10n.fonts.familyPlaceholderText;

  // Add the Google Fonts opts to each control
  addGoogleFontsToFontFamilyField( $fontFamilyField );

  // Initialize the select2 field for the font family
  $fontFamilyField.select2( {
    placeholder: familyPlaceholderText
  } );

  $fontFamilyField.on( 'change', onFontFamilyChange );
  bindFontFamilySettingChange( $fontFamilyField );
}

const initializeSubfields = ( $fontField ) => {
  const $variant = $fontField.find( fontVariantSelector );
  const $select = $fontField.find( 'select' ).not( 'select[class*=\' select2\'],select[class^=\'select2\']' );
  const $range = $fontField.find( 'input[type="range"]' );

  // Initialize the select2 field for the font variant
  initSubfield( $variant, true )

  // Initialize all the regular selects in the font subfields
  initSubfield( $select, false );

  // Initialize the all the range fields in the font subfields
  initSubfield( $range, false );
}

const addGoogleFontsToFontFamilyField = ( $fontFamilyField ) => {
  const googleFontsOptions = wp.customize.settings[ 'google_fonts_opts' ];
  const $googleOptionsPlaceholder = $fontFamilyField.find( '.google-fonts-opts-placeholder' ).first();

  if ( typeof googleFontsOptions !== 'undefined' && $googleOptionsPlaceholder.length ) {

    // Replace the placeholder with the HTML for the Google fonts select options.
    $googleOptionsPlaceholder.replaceWith( googleFontsOptions );

    // The active font family might be a Google font so we need to set the current value after we've added the options.
    const activeFontFamily = $fontFamilyField.data( 'active_font_family' );

    if ( typeof activeFontFamily !== 'undefined' ) {
      $fontFamilyField.val( activeFontFamily );
    }
  }
}

const onFontFamilyChange = ( event ) => {
  const newFontFamily = event.target.value;
  const $target = $( event.target );
  const $wrapper = $target.closest( wrapperSelector );

  // Get the new font details
  const newFontDetails = getFontDetails( newFontFamily )

  // Update the font field head title (with the new font family name).
  updateFontHeadTitle( newFontDetails, $wrapper )

  // Update the variant subfield with the new options given by the selected font family.
  updateVariantField( newFontDetails, $wrapper )

  if ( typeof who !== 'undefined' && who === 'customify' ) {
    // The change was triggered programmatically by Customify.
    // No need to self-update the value.
  } else {
    // Mark this input as touched by the user.
    $( event.target ).data( 'touched', true )

    // Serialize subfield values and refresh the fonts in the preview window.
    selfUpdateValue( $wrapper );
  }
}

const bindFontFamilySettingChange = ( $fontFamilyField ) => {
  const $wrapper = $fontFamilyField.closest( wrapperSelector );
  const settingID = getSettingID( $fontFamilyField );

  wp.customize( settingID, setting => {
    setting.bind( function( newValue, oldValue ) {
      if ( ! fontsService.isUpdating( settingID ) ) {
        loadFontValue( $wrapper, newValue, settingID )
      }
    } );
  } );
}


const reloadConnectedFields = _.debounce( () => {
  const settingIDs = customify.fontPalettes.masterSettingIds;

  globalService.unbindConnectedFields( settingIDs );
  globalService.bindConnectedFields( settingIDs, callbackFilter );

}, 30 );
