import React, { useState } from "react";


const SettingsListItem = (props) => {
    /*
    id
    name
    description
    value
    type
     */
    const id = "setting-" + props.id;

    const [isChecked] = useState(props.value == 1);

    switch (props.type)
    {
        case "file":
            return <tr id={ id } className="settings-list-table-tbody-item">
                <td className="settings-list-table-tbody-item-name"><h2>{props.name}</h2></td>
                <td className="settings-list-table-tbody-item-description"><span>{props.description}</span></td>
                <td className="settings-list-table-tbody-item-value">
                    <label htmlFor={ id+"-image" } className="settings-list-table-tbody-item-value-fileInputLabel" style={ {backgroundImage: `url(/uploads/settings/${props.value})`} }></label>
                    <input id={id + "-image"} className="settings-list-table-tbody-item-value-fileInput" type="file" accept="image/*"
                           onChange={ (e) => {
                               props.changeValue(props.id, e);
                           } }
                    />
                </td>
            </tr>
        case "boolean":

            return <tr id={ id } className="settings-list-table-tbody-item" >
                <td className="settings-list-table-tbody-item-name"><h2>{props.name}</h2></td>
                <td className="settings-list-table-tbody-item-description"><span>{props.description}</span></td>
                <td className="settings-list-table-tbody-item-value">
                    <input className="page-controls-elementsPerPage" defaultChecked={isChecked} type="checkbox" onChange={ (e) => { props.changeValue(props.id, e) } } />
                </td>
            </tr>
        case "text":
        default:
            return <tr id={ id } className="settings-list-table-tbody-item">
                <td className="settings-list-table-tbody-item-name"><h2>{props.name}</h2></td>
                <td className="settings-list-table-tbody-item-description"><span>{props.description}</span></td>
                <td className="settings-list-table-tbody-item-value">
                    <input className="page-controls-elementsPerPage" type="text" defaultValue={props.value} onBlur={ (e) => { props.changeValue(props.id, e) } } />
                </td>
            </tr>
    }
}

export default SettingsListItem;