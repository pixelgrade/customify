import $ from "jquery";
import { moveConnectedFields } from "./connected-fields/move-connected-fields";
import { swapConnectedFields } from "./connected-fields/swap-connected-fields";

const getSwapMap = ( variation ) => {
  if ( ! customify.colorPalettes.variations.hasOwnProperty( variation ) ) {
    return customify.colorPalettes.variations['light'];
  }

  return customify.colorPalettes.variations[variation]
}

const getSelectedDiversity = () => {
  const diversityOptions = $( '[name*="sm_color_diversity"]' );
  const hasDiversityOption = !! diversityOptions.length;

  return hasDiversityOption ? $('[name*="sm_color_diversity"]:checked').val() : wp.customize('sm_color_diversity')()
}

const isDefaultDiversitySet = () => {
  const diversityOptions = $( '[name*="sm_color_diversity"]' );
  const hasDiversityOption = !! diversityOptions.length;
  const isDefaultDiversitySelected = typeof diversityOptions.filter( ':checked' ).data( 'default' ) !== 'undefined';

  return hasDiversityOption ? isDefaultDiversitySelected : true;
}

const isDefaultColorationSet = () => {
  const colorationOptions = $( '[name*="sm_coloration_level"]' );
  const hasColorationOptions = !! colorationOptions.length;
  const isDefaultColorationSelected = typeof $( '[name*="sm_coloration_level"]:checked' ).data( 'default' ) !== 'undefined';

  return hasColorationOptions ? isDefaultColorationSelected : true;
}

const applyNewColorationLevel = ( tempSettings ) => {
  const switchColorSelector = '#_customize-input-sm_dark_color_switch_slider_control';
  const selectColorSelector = '#_customize-input-sm_dark_color_select_slider_control';

  if ( ! isDefaultDiversitySet() ) {
    const switchRatio = $( switchColorSelector ).val() / 100;
    const selectRatio = $( selectColorSelector ).val() / 100;

    tempSettings = moveConnectedFields( tempSettings, 'sm_text_color_switch_master', 'sm_accent_color_switch_master', switchRatio );
    tempSettings = moveConnectedFields( tempSettings, 'sm_text_color_select_master', 'sm_accent_color_select_master', selectRatio );
  }

  return tempSettings;
}

const applyOldColorationLevel = ( tempSettings ) => {
  const primaryColorSelector = '#_customize-input-sm_dark_color_primary_slider_control';
  const secondaryColorSelector = '#_customize-input-sm_dark_color_secondary_slider_control';
  const tertiaryColorSelector = '#_customize-input-sm_dark_color_tertiary_slider_control';

  if ( ! isDefaultDiversitySet() || ! isDefaultColorationSet() ) {
    const primaryRatio = $( primaryColorSelector ).val() / 100;
    const secondaryRatio = $( secondaryColorSelector ).val() / 100;
    const tertiaryRatio = $( tertiaryColorSelector ).val() / 100;

    tempSettings = moveConnectedFields( tempSettings, 'sm_dark_primary', 'sm_color_primary', primaryRatio );
    tempSettings = moveConnectedFields( tempSettings, 'sm_dark_secondary', 'sm_color_secondary', secondaryRatio );
    tempSettings = moveConnectedFields( tempSettings, 'sm_dark_tertiary', 'sm_color_tertiary', tertiaryRatio );
  }

  return tempSettings;
}

const applyColorDiversity = ( tempSettings ) => {
  if ( ! isDefaultDiversitySet() || ! isDefaultColorationSet() ) {
    let selectedDiversity = getSelectedDiversity();
    let diversity_variation = getSwapMap( 'color_diversity_low' );
    tempSettings = swapConnectedFields( tempSettings, diversity_variation );

    if ( selectedDiversity === 'medium' ) {
      tempSettings = moveConnectedFields( tempSettings, 'sm_color_primary', 'sm_color_secondary', 0.5 );
    }

    if ( selectedDiversity === 'high' ) {
      tempSettings = moveConnectedFields( tempSettings, 'sm_color_primary', 'sm_color_secondary', 0.67 );
      tempSettings = moveConnectedFields( tempSettings, 'sm_color_secondary', 'sm_color_tertiary', 0.50 );
    }
  }

  return tempSettings;
}

const applyShuffle = ( tempSettings ) => {
  const shuffle = $( '[name*="sm_shuffle_colors"]:checked' ).val();

  if ( shuffle !== 'default' ) {
    const shuffleVariation = getSwapMap( 'shuffle_' + shuffle )
    tempSettings = swapConnectedFields( tempSettings, shuffleVariation )
  }

  return tempSettings;
}

const applyDarkMode = ( tempSettings ) => {
  const dark_mode = $( '[name*="sm_dark_mode_control"]:checked' ).val()

  if ( dark_mode === 'on' ) {
    const dark_mmode_variation = getSwapMap( 'dark' )
    tempSettings = swapConnectedFields( tempSettings, dark_mmode_variation )
  }

  return tempSettings;
}

export const applyConnectedFieldsAlterations = ( tempSettings ) => {
  tempSettings = applyOldColorationLevel( tempSettings );
  tempSettings = applyOldColorationLevel( tempSettings );
  tempSettings = applyNewColorationLevel( tempSettings );
  tempSettings = applyColorDiversity( tempSettings );
  tempSettings = applyShuffle( tempSettings );
  tempSettings = applyDarkMode( tempSettings );

  return tempSettings
}
