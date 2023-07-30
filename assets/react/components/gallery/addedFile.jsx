import React, {Component} from "react";

const addedFile = (props) => {

    const shortName = (props.name.length > 15)?props.name.substring(0,15)+"...":props.name;

    return <div className="gallery-added-photos-file">
        <img className="gallery-added-photos-file-image" src={props.src} alt={props.name}/>
        <h2 className="gallery-added-photos-file-title">{shortName}</h2>
        <a className="gallery-added-photos-file-delete" onClick={props.deleteHandler}>
            <img className="gallery-added-photos-file-delete-icon" src="/build/icons/trashCan.svg" alt="usuń zdjęcie"/>
        </a>
    </div>
}

export default addedFile;