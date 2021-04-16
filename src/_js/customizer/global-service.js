import _ from "lodash";

let callbacks = {};
let settings = {};

let overrideCustomizerBack = [];

export const loadSettings = () => {
  settings = JSON.parse( JSON.stringify( wp.customize.settings.settings ) );
}

export const getSettings = () => {
  return settings;
}

export const setSettings = ( newSettings ) => {
  settings = newSettings;
}

export const getSetting = ( settingID ) => {
  return settings[settingID];
}

export const getSettingConfig = ( settingID ) => {
  return customify.config.settings[ settingID ];
}

export const setSetting = ( settingID, value ) => {
  settings[settingID] = value;
}

export const getCallback = ( settingID ) => {
  return callbacks[settingID];
}

export const setCallback = ( settingID, callback ) => {
  callbacks[settingID] = callback;
}

export const getCallbacks = () => {
  return callbacks;
}

export const deleteCallbacks = ( settingIDs ) => {
  settingIDs.forEach( settingID => {
    delete callbacks[settingID];
  } );
}

export const getBackArray = () => {
  return overrideCustomizerBack;
}

export const addToBackArray = ( section ) => {
  overrideCustomizerBack.push( section );
}

export const setBackArray = ( newArray ) => {
  overrideCustomizerBack = newArray.slice();
}

export const bindConnectedFields = function( settingIDs, filter = noop ) {

  settingIDs.forEach( settingID => {
    wp.customize( settingID, parentSetting => {

      setCallback( settingID, newValue => {
        const settingConfig = getSetting( settingID );
        const connectedFields = settingConfig.connected_fields || {};

        Object.keys( connectedFields ).map( key => connectedFields[key].setting_id ).forEach( connectedSettingID => {
          wp.customize( connectedSettingID, connectedSetting => {
            connectedSetting.set( filter( newValue ) );
          } );
        } );
      } );

      parentSetting.bind( getCallback( settingID ) );
    } );
  } );
}

export const unbindConnectedFields = function( settingIDs ) {
  const globalCallbacks = _.pick( getCallbacks(), settingIDs );

  _.each( globalCallbacks, ( callback, settingID ) => {
    wp.customize( settingID, setting => {
      setting.unbind( callback );
    } );
  } );

  deleteCallbacks( settingIDs );
}

const noop = x => x;
