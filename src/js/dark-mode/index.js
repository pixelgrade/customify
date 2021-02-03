import $ from 'jquery';

const COLOR_SCHEME_BUTTON = '.is-color-scheme-switcher-button';
const STORAGE_ITEM = 'color-scheme-dark';
const TEMP_STORAGE_ITEM = 'color-scheme-dark-temp'
const $html = $( 'html' );
const api = window?.wp?.customize;
const ignoreStorage = !! api;

export default class DarkMode {

  constructor( element ) {

    this.$element = $( element );

    this.$colorSchemeButtons = $( COLOR_SCHEME_BUTTON );
    this.$colorSchemeButtonsLink = this.$colorSchemeButtons.children( 'a' );

    this.matchMedia = window.matchMedia( '(prefers-color-scheme: dark)' );
    this.darkModeSetting = $html.data( 'dark-mode-advanced' );

    this.theme = null;

    this.initialize();
  }

  initialize() {
    localStorage.removeItem( TEMP_STORAGE_ITEM );

    this.bindEvents();
    this.bindCustomizer();
    this.update();
  }

  bindEvents() {
    $( document ).on( 'click', COLOR_SCHEME_BUTTON, this.onClick.bind( this ) );

    this.matchMedia.addEventListener( 'change', () => {
      localStorage.removeItem( TEMP_STORAGE_ITEM );
      this.update();
    } );
  }

  bindCustomizer() {

    if ( ! api ) {
      return;
    }

    api.bind( 'ready', () => {
      const setting = api( 'sm_dark_mode_advanced' );

      localStorage.removeItem( TEMP_STORAGE_ITEM );
      this.darkModeSetting = setting();
      this.update();

      setting.bind( ( newValue, oldValue ) => {
        localStorage.removeItem( TEMP_STORAGE_ITEM );
        this.darkModeSetting = newValue;
        this.update();
      } );
    } );
  }

  onClick( e ) {
    e.preventDefault();
    let isDark = this.isCompiledDark();

    localStorage.setItem( this.getStorageItemKey(), !! isDark ? 'light' : 'dark' );

    this.update();
  };

  getStorageItemKey() {
    return ! ignoreStorage ? STORAGE_ITEM : TEMP_STORAGE_ITEM;
  };

  isSystemDark() {
    let isDark = this.darkModeSetting === 'on';

    if ( this.darkModeSetting === 'auto' && this.matchMedia.matches ) {
      isDark = true;
    }

    return isDark;
  }

  isCompiledDark() {
    let isDark = this.isSystemDark();
    let colorSchemeStorageValue = localStorage.getItem( this.getStorageItemKey() );

    if ( colorSchemeStorageValue !== null ) {
      isDark = colorSchemeStorageValue === 'dark';
    }

    return isDark;
  }

  update() {
    $html.toggleClass( 'is-dark', this.isCompiledDark() );
  }
}

const Dark = new DarkMode();

window.myApi = {};
window.myApi.isDark = Dark.isCompiledDark.bind( Dark );
