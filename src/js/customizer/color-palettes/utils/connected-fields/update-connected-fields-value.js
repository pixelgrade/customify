import _ from "lodash";
import { globalService } from "../../global-service";

const updateConnectedFieldsValue = ( settingID, value ) => {
  let parentSettingData = globalService.getSetting( settingID )

  _.each( parentSettingData.connected_fields, function( connectedFieldData ) {

    if ( _.isUndefined( connectedFieldData ) || _.isUndefined( connectedFieldData.setting_id ) || !_.isString( connectedFieldData.setting_id ) ) {
      return
    }

    const connectedSetting = wp.customize( connectedFieldData.setting_id );

    if ( _.isUndefined( connectedSetting ) ) {
      return
    }

    connectedSetting.set( value );
  } )
}

export { updateConnectedFieldsValue }
