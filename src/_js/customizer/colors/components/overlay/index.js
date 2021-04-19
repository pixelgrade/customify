import React from "react";

const Overlay = ( props ) => {
  const { show } = props;

  return (
    <div className={ `sm-overlay sm-overlay--${ show ? 'visible' : 'hidden' }` }>
      { props.children }
    </div>
  )
}

export default Overlay;
