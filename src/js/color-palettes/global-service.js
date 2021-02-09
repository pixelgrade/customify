class globalServiceClass {

  constructor() {
    this.settings = {};
    this.callbacks = {};
  }

  loadSettings() {
    this.settings = JSON.parse( JSON.stringify( wp.customize.settings.settings ) );
  }

  getSettings() {
    return this.settings;
  }

  setSettings( settings ) {
    this.settings = settings;
  }

  getSetting( settingID ) {
    return this.settings[settingID];
  }

  setSetting( settingID, value ) {
    this.settings[settingID] = value;
  }

  getCallback( settingID ) {
    return this.callbacks[settingID];
  }

  setCallback( settingID, callback ) {
    this.callbacks[settingID] = callback;
  }

  getCallbacks() {
    return this.callbacks;
  }

  deleteCallbacks( settingIDs ) {
    settingIDs.forEach( settingID => {
      delete this.callbacks.settingID;
    } );
  }
}

const globalService = new globalServiceClass();

export { globalService };
