import React, {Component} from "react";

const caption = (props) => {

    if ( (null != props.header && "" !== props.header) && (null != props.description && "" !== props.description))
    {
        return <div className="form-caption">
            <h2>{props.header}</h2>
            <p>
                {props.description}
            </p>
        </div>;
    }
    else if (null != props.header && "" !== props.header)
    {
        return <div className="form-caption">
            <h2>{props.header}</h2>
        </div>;
    }
    else if (null != props.description && "" !== props.description)
    {
        return <div className="form-caption">
            <p>{props.description}</p>
        </div>;
    } else {
        return null;
    }
}

export default caption;