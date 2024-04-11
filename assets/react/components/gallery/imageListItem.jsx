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
        <td className="image-list-table-tbody-item-image">
            <img src={"/uploads/photos/"+props.safeFileName} alt={props.name}  />
            <div className="image-list-table-tbody-item-image-link">
                <span className="image-list-table-tbody-item-image-link-href">{`${location.protocol}//${window.location.host}/uploads/photos/${props.safeFileName}`}</span>
                <div className="image-list-table-tbody-item-image-link-icon"
                     onClick={ (event) => {
                        navigator.clipboard.writeText( `${location.protocol}//${window.location.host}/uploads/photos/${props.safeFileName}`)
                            .then( () => { event.target.src = "/build/icons/dashboard/confirm.svg" } );

                         delay(1000).then(() => event.target.src = "/build/icons/dashboard/copy.svg");
                     }}
                >
                    <img src="/build/icons/dashboard/copy.svg" alt="kopiuj" />
                </div>
            </div>
        </td>
        <td className="image-list-table-tbody-item-addedBy"><span>{props.addedBy}</span></td>
        <td className="image-list-table-tbody-item-fileType"><span>{props.fileType}</span></td>
        <td className="image-list-table-tbody-item-fileType"><span>{props.dateAdded}</span></td>
        <td className="image-list-table-tbody-item-delete" onClick={ () => {props.deleteItem(props.id, props.index) } }>
            <a>
                <img className="image-list-table-tbody-item-delete-icon" src="/build/icons/dashboard/trashCan.svg" alt="usuń zdjęcie"/>
            </a>
        </td>
    </tr>
}

function delay(time) {
    return new Promise(resolve => setTimeout(resolve, time));
}

export default ImageListItem;