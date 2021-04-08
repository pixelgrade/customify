import $ from "jquery";
import { debounce } from '../../utils';
import * as globalService from "../global-service";

import {
  getCallbackFilter,
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

import { getCallback, getSetting, setCallback } from "../global-service";

const wrapperSelector = '.font-options__wrapper';
const fontVariantSelector = '.customify_font_weight';

export const initializeFonts = function() {
  const $fontFields = $( wrapperSelector );

  $fontFields.each( ( i, obj ) => {
    const $fontField = $( obj );

    initializeFontFamilyField( $fontField );
    initializeSubfields( $fontField );
  } );

  handleFontPopupToggle();

  reloadConnectedFields();
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
    selfUpdateValue( $wrapper, getSettingID( $target ) );
  }
}

const bindFontFamilySettingChange = ( $fontFamilyField ) => {
  const $wrapper = $fontFamilyField.closest( wrapperSelector );
  const settingID = getSettingID( $fontFamilyField );

  wp.customize( settingID, setting => {
    setting.bind( function( newValue, oldValue ) {
      // this is a costly operation
      if ( ! fontsService.isUpdating( settingID ) ) {
        loadFontValue( $wrapper, newValue, settingID )
      }
    } );
  } );
}


const reloadConnectedFields = debounce( () => {
  const settingIDs = customify.fontPalettes.masterSettingIds;

  globalService.unbindConnectedFields( settingIDs );

  settingIDs.forEach( settingID => {

    wp.customize( settingID, parentSetting => {

      setCallback( settingID, newValue => {
        const settingConfig = getSetting( settingID );
        const connectedFields = settingConfig.connected_fields || {};

        Object.keys( connectedFields ).forEach( key => {
          const connectedFieldData = connectedFields[key];
          const connectedSettingID = connectedFieldData.setting_id;
          const callbackFilter = getCallbackFilter( connectedFieldData );

          wp.customize( connectedSettingID, connectedSetting => {
            connectedSetting.set( callbackFilter( newValue ) );
          } );
        } );
      } );

      parentSetting.bind( getCallback( settingID ) );
    } );
  } );

}, 30 );
