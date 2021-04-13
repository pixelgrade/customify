import React, { useCallback, useState } from 'react';
import './style.scss';

const Blinds = ( props ) => {
  const { title, children } = props;
  const [open, setOpen] = useState( false );
  const toggle = useCallback( () => { setOpen( ! open ) }, [ open ] )

  return (
    <div className={ `sm-blinds sm-blinds--${ open ? 'open' : 'closed' }` }>
      <div className="sm-blinds__header" onClick={ toggle }>
        <div className="sm-blinds__title">{ title }</div>
        <div className="sm-blinds__toggle" />
      </div>
      <div className="sm-blinds__body">
        { children }
      </div>
    </div>
  )
}

export default Blinds;
