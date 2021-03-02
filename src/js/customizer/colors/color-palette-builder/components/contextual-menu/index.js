import "./style.scss"
import useOutsideClick from "../../../../utils/use-outside-click";

const { useState, useRef } = wp.element;

const ContextualMenu = ( props ) => {

  const { actions } = props;
  const [ showMenu, setShowMenu ] = useState( false );

  const ref = useRef( null );

  useOutsideClick( ref, () => {
    setShowMenu( false );
  } );

  return (
    <div ref={ ref } className={ `c-contextual-menu c-contextual-menu--${ showMenu ? 'visible' : 'hidden' }`}>
      <button className="c-contextual-menu__toggle" onClick={ (e) => {
        e.preventDefault();
        setShowMenu( ! showMenu ) } }>
        <span>Toggle Menu</span>
      </button>
      <div className="c-contextual-menu__list">
        { actions.map( ( { label, callback }, index ) => {

          const onClick = ( e ) => {
            e.preventDefault();
            setShowMenu( false );
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
