const updating = {};
const loading = {};

export const isLoading = ( settingID ) => {
  return !! updating[ settingID ];
}

export const isUpdating = ( settingID ) => {
  return !! loading[ settingID ]
}

export const setLoading = ( settingID, value ) => {
  loading[ settingID ] = value;
}

export const setUpdating = ( settingID, value ) => {
  updating[ settingID ] = value;
}
