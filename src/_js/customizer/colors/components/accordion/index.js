import React, { useState } from 'react';
import './style.scss';

const Accordion = ( props ) => {
  const sections = React.Children.toArray( props.children ).filter( child => child.type === AccordionSection );
  const [active, setActive] = useState( null );

  return sections.map( ( section, index ) => {
    const { title, children } = section.props;

    return (
      <div className={ `sm-blinds sm-blinds--${ active === index ? 'open' : 'closed' }` }>
        <div className="sm-blinds__header" onClick={ () => {
          setActive( active !== index ? index : null );
        } }>
          <div className="sm-blinds__title">{ title }</div>
          <div className="sm-blinds__toggle" />
        </div>
        <div className="sm-blinds__body">
          { children }
        </div>
      </div>
    )
  } )
}

const AccordionSection = ( props ) => {
  return null;
}

export {
  Accordion,
  AccordionSection
};
