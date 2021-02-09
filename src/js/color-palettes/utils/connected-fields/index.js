import { globalService } from '../../global-service';

export { moveConnectedFields } from './move-connected-fields'
export { swapConnectedFields } from './swap-connected-fields'
export { updateConnectedFieldsValue } from './update-connected-fields-value';

export const bindConnectedFields = function( settingIDs ) {

  _.each( settingIDs, ( settingID ) => {
    const parentSettingData = globalService.getSetting( settingID );

    if ( typeof parentSettingData !== 'undefined' ) {
      _.each( parentSettingData.connected_fields, ( connectedFieldData ) => {
        const connectedSettingID = connectedFieldData.setting_id;
        const connectedSetting = wp.customize( connectedSettingID );

        if ( typeof connectedSetting !== 'undefined' ) {
          connectedSetting.bind( globalService.getCallback( connectedSettingID ) );
        }
      } )
    }
  } );
}

export const unbindConnectedFields = function( settingIDs ) {
  const globalCallbacks = _.pick( globalService.getCallbacks(), settingIDs );

  _.each( globalCallbacks, ( callback, settingID ) => {
    const setting = wp.customize( settingID );
    setting.unbind( callback );
  } );

  globalService.deleteCallbacks( settingIDs );
}
