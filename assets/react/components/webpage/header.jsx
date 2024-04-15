import React, {Component} from "react";

const header = (props) => {
    return <header>
        <a href={`${location.protocol}//${window.location.host}`}>
            <img src={`${location.protocol}//${window.location.host}/uploads/settings/${props.logo}`} alt="główne logo"/>
        </a>
        <div>
            <ul>
                <li><a href={`${location.protocol}//${window.location.host}/form`}><button>Kontakt</button></a></li>
                <li><a href={`${location.protocol}//${window.location.host}/gallery`}><button>Galeria</button></a></li>
            </ul>
        </div>
    </header>
}

export default header;