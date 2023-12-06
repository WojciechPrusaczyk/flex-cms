import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import ColorsListItem from "../../../components/colors/colorsListItem";
import {Tooltip} from "../../../components/Tooltip";
import ColorPicker from "../../../components/colors/colorPicker";

class SettingsMain extends Component
{
    constructor(props) {
        super(props);
        this.state = {
            settings: [],
        }
        this.getSettings();
        this.changeValue = this.changeValue.bind(this);
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

    async uploadFile(id, file)
    {

    }

    async changeValue(id, event)
    {
        const matchingSettings = this.state.settings.filter(setting => {
            return Object.keys(setting)[0] === id[0];
        });

        if (matchingSettings.length === 1)
        {
            const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/colors/set-value?`;
            const requestedValue = event.target.value;

            let fetchTextUrl = fetchAddress + new URLSearchParams({
                id: id,
                value: requestedValue,
            });

            try {
                const response = await fetch(fetchTextUrl)
                    .then((response) => response.json())
                    .then((responseJson) => {
                        // console.log(responseJson);
                    })
            } catch (error) {
            }
            }
        //console.log(id, requestedElement, event);
    }

    async updatePhoto(id, newValue)
    {
        let currentSettings = this.state.settings;

        let requestedSetting = currentSettings.filter(setting => {
            return Object.keys(setting)[0] === id[0];
        })[0];
        Object.values(requestedSetting)[0].value = newValue;

        this.setState({photos: currentSettings});
    }

    render() {

        let settingsList = this.state.settings.map((setting, index) => {
            let settingId = Object.keys(setting);
            let settingObject = Object.values(setting)[0];

            return <ColorsListItem key={settingId} id={settingId} name={settingObject.name} description={settingObject.description} value={settingObject.value} changeValue={ this.changeValue } type={settingObject.type} />
        });

        return (
            <div className="main">
                <div id="tooltip-root">
                    <Tooltip
                        text="Tutaj można dowolnie zmieniać kolory całej strony i dopasować ją do swoich upodobań."
                    />
                </div>
                {/*<ColorPicker />*/}
                <table className="colors-list-table">
                    <thead className="colors-list-table-thead"><tr>
                        <th>Nazwa techniczna</th>
                        <th>Opis</th>
                        <th>Wartość</th>
                    </tr></thead>
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