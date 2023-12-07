import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import ColorsListItem from "../../../components/colors/colorsListItem";
import {Tooltip} from "../../../components/Tooltip";
import ColorPicker from "../../../components/colors/colorPicker";
import Confirmation from "../../../components/confirmation";

class SettingsMain extends Component
{
    constructor(props) {
        super(props);
        this.state = {
            settings: [],
            isUserChangingColor: false,
            color: null,
        }
        this.getSettings();
        this.changeValue = this.changeValue.bind(this);
        this.changeColor = this.changeColor.bind(this);
        this.submitColor = this.submitColor.bind(this);
    }

    async getSettings()
    {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/colors/get-colors?`;
        try {
            const response = await fetch(fetchAddress)
                .then((response) => response.json())
                .then((responseJson) => {
                    this.setState({settings: responseJson.response.items});
                })
        } catch (error) {
        }
    }

    async changeValue(colorObject)
    {
        console.log(colorObject);
        const matchingSettings = this.state.settings.filter(setting => {
            return Object.keys(setting)[0] === colorObject.id;
        });

        if (matchingSettings.length === 1)
        {
            const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/colors/set-value?`;

            let fetchTextUrl = fetchAddress + new URLSearchParams({
                id: colorObject.id,
                value: colorObject.finalColor,
            });

            try {
                const response = await fetch(fetchTextUrl)
                    .then((response) => response.json())
                    .then((responseJson) => {
                        this.closeColorPicker();
                        this.getSettings();
                    })
            } catch (error) {
            }
        }
    }

    changeColor(id, event)
    {
        const initialColor = event.target.style.backgroundColor;
        this.setState({ isUserChangingColor: true });

        const colorObject = {
            id: id[0],
            value: initialColor,
            finalColor: null
        };

        this.setState({ color: colorObject})
    }

    submitColor(color)
    {
        console.log(color);
        const colorObject = { id: this.state.color.id, value: this.state.color.initialColor, finalColor: color };
        this.setState({color: colorObject})

        this.changeValue(colorObject);
    }

    closeColorPicker()
    {
        this.setState({ isUserChangingColor: false });
        this.setState({ color: null })
    }

    render() {

        let settingsList = this.state.settings.map((setting, index) => {
            let settingId = Object.keys(setting);
            let settingObject = Object.values(setting)[0];

            return <ColorsListItem key={settingId} id={settingId} name={settingObject.name} description={settingObject.description} value={settingObject.value} changeColor={ this.changeColor } type={settingObject.type} />
        });

        return (
            <div className="main">
                <div id="tooltip-root">
                    <Tooltip
                        text="Tutaj można dowolnie zmieniać kolory całej strony i dopasować ją do swoich upodobań."
                    />
                </div>

                { ( this.state.isUserChangingColor && null != this.state.color) && <ColorPicker onSubmit={this.submitColor} initialColor={this.state.color.value} closeColorPicker={ () => this.closeColorPicker() } /> }

                <table className="colors-list-table">
                    <thead className="colors-list-table-thead">
                    <tr>
                        <th>Nazwa techniczna</th>
                        <th>Opis</th>
                        <th>Wartość</th>
                    </tr>
                    </thead>
                    <tbody className="colors-list-table-tbody">
                    {settingsList}
                    </tbody>
                </table>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<SettingsMain />);