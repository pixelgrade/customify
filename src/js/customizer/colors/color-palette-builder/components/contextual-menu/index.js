import "./style.scss"
import useOutsideClick from "../../../../utils/use-outside-click";

const { useEffect, useState, useRef } = wp.element;

const ContextualMenu = ( props ) => {

  const {
    actions,
  } = props;

  const [ isOpen, setIsOpen ] = useState( false );

  const onToggle = typeof props.onToggle === 'function' ? props.onToggle : ( isOpen ) => {};

  useEffect( () => {
    onToggle( isOpen );
  }, [ isOpen ] )

  const ref = useRef( null );

  useOutsideClick( ref, () => {
    setIsOpen( false );
  } );

  return (
    <div ref={ ref } className={ `c-contextual-menu c-contextual-menu--${ isOpen ? 'visible' : 'hidden' }`}>
      <button className="c-contextual-menu__toggle" onClick={ (e) => {
        e.preventDefault();
        setIsOpen( ! isOpen ) } }>
        <span>Toggle Menu</span>
      </button>
      <div className="c-contextual-menu__list">
        { actions.map( ( { label, callback }, index ) => {

          const onClick = ( e ) => {
            e.preventDefault();
            setIsOpen( false );
            callback();
          };

          return (
            <div key={ index } className="c-contextual-menu__list-item" onClick={ onClick }>
              { label }
            </div>
          )
        } ) }
      </div>
    </div>
  )
}

export { ContextualMenu }
