export { getCallbackFilter } from './callback-filter';
export { convertFontVariantToFVD } from './convert-font-variant';
export { determineFontType } from './determine-font-type';
export { getFontDetails } from './get-font-details';
export { handleFontPopupToggle } from './handle-font-popup-toggle';
export { initSubfield } from './init-subfield';
export { loadFontValue } from './load-font-value';
export { selfUpdateValue } from './self-update-value';
export { standardizeNumericalValue } from './standardize-numerical-value';
export { updateFontHeadTitle } from './update-font-head-title';
export { updateVariantField } from './update-variant-field';

export * as fontsService from './fonts-service';

const wrapperSelector = '.font-options__wrapper';
const valueHolderSelector = '.customify_font_values';

export const getSettingID = ( $element ) => {
  return getWrapper( $element ).find( valueHolderSelector ).data( 'customize-setting-link' );
}

export const getWrapper = ( $element ) => {
  return $element.closest( wrapperSelector );
}

