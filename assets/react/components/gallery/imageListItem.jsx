import React, {Component} from "react";

const ImageListItem = (props) => {
    /*
    name
    safeFileName
    addedBY
    AddedDateTime
    fileType
     */
    const shortName = (props.name.length > 15)?props.name.substring(0,15)+"...":props.name;

    return <tr className="image-list-table-tbody-item">
        <td className="image-list-table-tbody-item-id"><span>{props.id}</span></td>
        <td className="image-list-table-tbody-item-name"><h2>{shortName}</h2></td>
        <td className="image-list-table-tbody-item-image"><img src={"/uploads/photos/"+props.safeFileName} alt={props.name}/></td>
        <td className="image-list-table-tbody-item-addedBy"><span>{props.addedBy}</span></td>
        <td className="image-list-table-tbody-item-fileType"><span>{props.fileType}</span></td>
        <td className="image-list-table-tbody-item-delete">
            <a onClick={ () => {props.deleteItem(props.id, props.index) } }>
                <img className="image-list-table-tbody-item-delete-icon" src="/build/icons/dashboard/trashCan.svg" alt="usuń zdjęcie"/>
            </a>
        </td>
    </tr>
}

export default ImageListItem;