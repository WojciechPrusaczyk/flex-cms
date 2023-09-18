import React, {Component} from "react";

const confirmation = (props) => {
    let confirmationText = null;
    if ( undefined !== props.text && null !== props.text && props.text.length > 1)
    {
        confirmationText = props.text;
    } else { confirmationText = "Czy na pewno?" }

    return <div id="confirmation" className="confirmation">
            <div className="confirmation-content">
                <div className="confirmation-content-close"><button className="confirmation-content-close-button" onClick={props.close}>X</button></div>
                <h2 className="confirmation-content-text">{confirmationText}</h2>
                <div className="confirmation-content-buttons"><button className="confirmation-content-buttons-close" onClick={props.close}>Nie</button><button className="confirmation-content-buttons-action" onClick={() => props.action()}>Tak</button></div>
            </div>
        </div>
}

export default confirmation;