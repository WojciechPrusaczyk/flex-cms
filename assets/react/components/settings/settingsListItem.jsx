import React, {Component} from "react";

const SettingsListItem = (props) => {
    /*
    id
    name
    description
    value
    type
     */
    const id = "setting-"+props.id;

    switch (props.type)
    {
        case "image":
            return <tr className="settings-list-table-tbody-item">
                <td className="settings-list-table-tbody-item-name"><h2>{props.name}</h2></td>
                <td className="settings-list-table-tbody-item-description"><span>{props.description}</span></td>
                <td className="settings-list-table-tbody-item-value">
                    <input id="elements-per-page" className="page-controls-elementsPerPage" type="text" value={props.value} />
                </td>
            </tr>
        case "email":
            return <tr className="settings-list-table-tbody-item">
                <td className="settings-list-table-tbody-item-name"><h2>{props.name}</h2></td>
                <td className="settings-list-table-tbody-item-description"><span>{props.description}</span></td>
                <td className="settings-list-table-tbody-item-value">
                    <input id="elements-per-page" className="page-controls-elementsPerPage" type="text" value={props.value} />
                </td>
            </tr>
        case"text":
        default:
            return <tr id={ id } className="settings-list-table-tbody-item">
                <td className="settings-list-table-tbody-item-name"><h2>{props.name}</h2></td>
                <td className="settings-list-table-tbody-item-description"><span>{props.description}</span></td>
                <td className="settings-list-table-tbody-item-value">
                    <input id="elements-per-page" className="page-controls-elementsPerPage" type="text" defaultValue={props.value} onChange={ () => { props.changeValue(document.getElementById(id)) } } />
                </td>
            </tr>
    }
}

export default SettingsListItem;