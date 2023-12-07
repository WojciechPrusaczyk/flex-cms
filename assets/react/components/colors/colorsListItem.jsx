import React, { useState } from "react";


const ColorsListItem = (props) => {
    /*
    id
    name
    description
    value
    type
     */
    const id = "setting-" + props.id;
    return <tr id={ id } className="colors-list-table-tbody-item">
        <td className="colors-list-table-tbody-item-name"><h2>{props.name}</h2></td>
        <td className="colors-list-table-tbody-item-description"><span>{props.description}</span></td>
        <td className="colors-list-table-tbody-item-value">
            <span className="page-controls-elementsPerPage" style={{ backgroundColor: props.value}} onClick={ (e) => { props.changeColor(props.id, e) } } />
        </td>
    </tr>
}

export default ColorsListItem;