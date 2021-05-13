import React, { useEffect } from 'react';

const useCustomizeSettingCallback = ( settingID, callback ) => {

  if ( typeof callback !== "function" ) {
    return;
  }

  wp.customize( settingID, setting => {

    useEffect( () => {

      setting.bind( callback );

      return () => {
        setting.unbind( callback );
      }

    }, [] );

  } )

}

export default useCustomizeSettingCallback;
