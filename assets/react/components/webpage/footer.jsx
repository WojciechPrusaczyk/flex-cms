import React, {Component} from "react";

const footer = (props) => {
    return <footer>
        <div id="footer-links">
            <div>
                <ul>
                    <li>{props.companyEmailAddress}</li>
                    <li>{props.companyPhoneNumber}</li>
                    <li>{props.companyAddress}</li>
                </ul>
            </div>
            <div>
                <ul>
                    <li><a href={`${location.protocol}//${window.location.host}`}>Strona główna</a></li>
                    <li><a href={`${location.protocol}//${window.location.host}/form`}>Kontakt</a></li>
                    <li><a href={`${location.protocol}//${window.location.host}/gallery`}>Galeria</a></li>
                </ul>
            </div>
        </div>
        <div id="creator-sign">
            <span>Designed & Developed by Wojciech Prusaczyk 2024</span>
        </div>
    </footer>
}

export default footer;