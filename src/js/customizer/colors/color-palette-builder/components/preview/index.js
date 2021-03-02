import './style.scss';

export const Preview = ( props ) => {

  const {
    palettes
  } = props;

  return palettes.map( ( palette, index ) => {
    const { colors, id } = palette;

    return (
      <div className={ `palette-preview-set` }>
        <div className="palette-preview-set-header">
          <div className="palette-preview-source">
            {
              palette.source.map( ( source, index ) => {
                return <div key={ index } className="palette-preview-source-color" style={ { color: source } }></div>
              } )
            }
          </div>
          <div className="palette-preview-label">{ `${ palette.label } color palette` }</div>
        </div>
        <div className={ "palette-preview-swatches" }>
          { colors.map( ( color, colorIndex ) => <div key={ colorIndex } className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-color-palette-${ id }-bg-color-${ colorIndex + 1 })` } }></div> ) }
        </div>
        <div className={ "palette-preview-swatches" }>
          { colors.map( ( color, colorIndex ) => <div key={ colorIndex } className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-color-palette-${ id }-fg1-color-${ colorIndex + 1 })` } }></div> ) }
        </div>
        <div className={ "palette-preview-swatches" }>
          { colors.map( ( color, colorIndex ) => <div key={ colorIndex } className={ `sm-variation-${ colorIndex } `} style={ { color: `var(--sm-color-palette-${ id }-accent-color-${ colorIndex + 1 })` } }></div> ) }
        </div>
      </div>
    )
  } )
}
