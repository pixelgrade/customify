export const Preview = ( props ) => {

  const {
    palettes
  } = props;

  return palettes.map( ( palette, index ) => {
    const description = index === 0 ? 'Each column from the color palette below represent a state where a component could be. The first row is the main surface or background color, while the other two rows are for the content.' : '';

    return <PalettePreview key={ palette.id } palette={ { description, ...palette } } />
  } )
}

const PalettePreview = ( props ) => {
  const { palette } = props;
  const { colors, id, description } = palette;

  return (
    <div className="palette-preview">
      <div className="palette-preview-header">
        <div className="palette-preview-source">
          {
            palette.source.map( ( source, index ) => {
              return <div key={ index } className="palette-preview-source-color" style={ { color: source } }></div>
            } )
          }
        </div>
        <div className="palette-preview-title">
          <h4>{ `${ palette.label } color palette` }</h4>
        </div>
      </div>
      { description && <div className="palette-preview-description">
        <p>{ description }</p>
      </div> }
      <div className={ `palette-preview-set` }>
        { colors.map( ( color, colorIndex ) => (
          <div key={ colorIndex } className={ `palette-preview-swatches sm-palette-${ id } sm-variation-${ colorIndex + 1 }`}>
            <div style={ { color: `var(--sm-current-bg-color)` } }></div>
            <div style={ { color: `var(--sm-current-accent-color)` } }></div>
            <div style={ { color: `var(--sm-current-fg1-color)` } }></div>
            <div style={ { color: `var(--sm-current-fg2-color)` } }></div>
          </div>
        ) ) }
        <div className="palette-preview-accent" style={ { color: `var(--sm-color-palette-${ id }-accent-color-11)` } }></div>
      </div>
    </div>
  )
}
