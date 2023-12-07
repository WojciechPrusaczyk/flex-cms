import React, { useEffect, useState } from "react";

const ColorPicker = (props) => {
    const [colorValues, setColorValues] = useState({
        red: "0",
        green: "0",
        blue: "0",
        alpha: "1",
    });

    const [isHovered, setIsHovered] = useState(false);

    const handleMouseEnter = () => {
        setIsHovered(true);
    };

    const handleMouseLeave = () => {
        setIsHovered(false);
    };

    const parseColor = (color) => {
        const match = color.match(/rgba?\((\d+), (\d+), (\d+)(, (\d+(\.\d+)?))?\)/);

        if (match) {
            setColorValues((prevColorValues) => ({
                ...prevColorValues,
                red: match[1],
                green: match[2],
                blue: match[3],
                alpha: match[5] ? parseFloat(match[5]).toFixed(2) : "1.00",
            }));
        } else {
            const rgbMatch = color.match(/rgb\((\d+), (\d+), (\d+)\)/);
            if (rgbMatch) {
                setColorValues((prevColorValues) => ({
                    ...prevColorValues,
                    red: rgbMatch[1],
                    green: rgbMatch[2],
                    blue: rgbMatch[3],
                    alpha: "1.00",
                }));
            }
        }
    };

    let [colorValue, setColorValue] = useState("");

    useEffect(() => {
        const initialColor = props.initialColor;

        if (initialColor != null) {
            parseColor(initialColor);
        } else {
            parseColor("rgba(0, 0, 0, 1)");
        }
    }, []); // Pusty array oznacza, że useEffect zostanie wykonany tylko raz przy inicjalizacji

    useEffect(() => {
        document.getElementById("picked-color").style.color = colorValue;
    }, [colorValue]);

    function changeColor(event, color, value) {
        setColorValues((prevColorValues) => {
            const updatedColorValues = { ...prevColorValues, [color]: value };

            if (color === "alpha" && value >= 100) {
                updatedColorValues.alpha = 1;
            } else if (color === "alpha" && value <= 0) {
                updatedColorValues.alpha = 0;
            } else if (color === "alpha" && value.length === 1) {
                updatedColorValues.alpha = `0.0${value}`;
            } else if (color === "alpha") {
                updatedColorValues.alpha = `0.${value}`;
            }

            const updatedColorValue = `rgba(${updatedColorValues.red}, ${updatedColorValues.green}, ${updatedColorValues.blue}, ${updatedColorValues.alpha} )`;
            const colorBarElement = document.getElementById("picked-color");
            colorBarElement.style.backgroundColor = updatedColorValue;
            colorBarElement.innerHTML = updatedColorValue;

            return updatedColorValues;
        });
    }

    return (
        <div id="color-picker" className="colors-picker">
            <p className="colors-picker-close">
                <input
                    className="colors-picker-close-button"
                    type="button"
                    value="X"
                    onClick={() => props.closeColorPicker()}
                />
            </p>
            <p className="colors-picker-value-red">
                <label htmlFor="colors-picker-value-red-label"></label>
                <input
                    id="colors-picker-value-red-input"
                    type="range"
                    min={0}
                    max={255}
                    value={colorValues.red}
                    onChange={(event) => changeColor(event, "red", event.target.value)}
                />
            </p>
            <p className="colors-picker-value-green">
                <label htmlFor="colors-picker-value-green-label"></label>
                <input
                    id="colors-picker-value-green-input"
                    type="range"
                    min={0}
                    max={255}
                    value={colorValues.green}
                    onChange={(event) => changeColor(event, "green", event.target.value)}
                />
            </p>
            <p className="colors-picker-value-blue">
                <label htmlFor="colors-picker-value-blue-label"></label>
                <input
                    id="colors-picker-value-blue-input"
                    type="range"
                    min={0}
                    max={255}
                    value={colorValues.blue}
                    onChange={(event) => changeColor(event, "blue", event.target.value)}
                />
            </p>
            <p className="colors-picker-value-alpha">
                <label htmlFor="colors-picker-value-alpha-label"></label>
                <input
                    id="colors-picker-value-alpha-input"
                    type="range"
                    min={0}
                    max={100}
                    value={
                        colorValues.alpha === 0
                            ? 0
                            : colorValues.alpha === 1
                                ? 100
                                : colorValues.alpha * 100
                    }
                    onChange={(event) => changeColor(event, "alpha", event.target.value)}
                />
            </p>
            <p
                className="colors-picker-color"
                onMouseEnter={handleMouseEnter}
                onMouseLeave={handleMouseLeave}
            >
        <span
            id="picked-color"
            style={{
                backgroundColor: `rgba(${colorValues.red}, ${colorValues.green}, ${colorValues.blue}, ${colorValues.alpha} )`,
                color: isHovered ? "white" : "transparent",
                textShadow: isHovered ? '2px 2px 2px rgba(0, 0, 0, 1)' : "none",
            }}
            className="colors-picker-color-value"
            aria-label="choosen color"
        >
          {`rgba(${colorValues.red}, ${colorValues.green}, ${colorValues.blue}, ${colorValues.alpha} )`}
        </span>
            </p>
            <p className="colors-picker-submit">
                <input
                    className="colors-picker-submit-button"
                    type="button"
                    value="Wybierz kolor"
                    onClick={() => {
                        props.onSubmit(`rgba(${colorValues.red}, ${colorValues.green}, ${colorValues.blue}, ${colorValues.alpha} )`);
                    }}
                />
            </p>
        </div>
    );
};

export default ColorPicker;
