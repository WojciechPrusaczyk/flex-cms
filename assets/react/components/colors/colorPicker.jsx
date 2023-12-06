import React, { useState } from "react";


const ColorPicker = (props) => {
    let colorValues = {
        red: "00",
        green: "00",
        blue: "00",
        alpha: "99",
    }

    function changeColor(event, color, value) {
        colorValues[color] = value;

        let alpha = "";
        if ( colorValues.alpha >= 100 )
        {
            alpha = 1;
        } else if (colorValues.alpha <= 0 ){
            alpha = 0;
        } else if (colorValues.alpha.length == 1)
        {
            alpha = `0.0${colorValues.alpha}`;
        } else {
            alpha = `0.${colorValues.alpha}`;
        }

        let colorHash = `rgba(${colorValues.red}, ${colorValues.green}, ${colorValues.blue}, ${alpha} )`;

        console.log(colorHash);

        document.getElementById("picked-color").style.backgroundColor = colorHash;
    }

    return <div id="color-picker" className="colors-picker">
        <p className="colors-picker-title"><h2>{props.name}</h2></p>
        <p className="colors-picker-value-red">
            <label htmlFor="colors-picker-value-red-label"></label>
            <input id="colors-picker-value-red-input"
                type="range"
                min={0}
                max={255}
                onChange={(event) => changeColor(event, "red", event.target.value)}
            />
        </p>
        <p className="colors-picker-value-green">
            <label htmlFor="colors-picker-value-green-label"></label>
            <input id="colors-picker-value-green-input"
                   type="range"
                   min={0}
                   max={255}
                   onChange={(event) => changeColor(event, "green", event.target.value)}

            />
        </p>
        <p className="colors-picker-value-blue">
            <label htmlFor="colors-picker-value-blue-label"></label>
            <input id="colors-picker-value-blue-input"
                type="range"
                min={0}
                max={255}
               onChange={(event) => changeColor(event, "blue", event.target.value)}
            />
        </p>
        <p className="colors-picker-value-alpha">
            <label htmlFor="colors-picker-value-alpha-label"></label>
            <input id="colors-picker-value-alpha-input"
                type="range"
                min={0}
                max={100}
               onChange={(event) => changeColor(event, "alpha", event.target.value)}
            />
        </p>
        <p className="colors-picker-color"><span id="picked-color" style={{ height: 30, display: "block", backgroundColor: "black"}} className="colors-picker-color-value" aria-label="choosen color"></span></p>
        <p className="colors-picker-submit">
            <input className="page-controls-elementsPerPage" type="button" value="Wybierz kolor"/>
        </p>
    </div>
}


export default ColorPicker;