import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import SettingsListItem from "../../../components/settings/settingsListItem";

class SettingsMain extends Component
{
    constructor(props) {
        super(props);
        this.state = {
            settings: [],
        }
        this.getSettings();
    }

    async getSettings()
    {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/settings/get-settings?`;
        try {
            const response = await fetch(fetchAddress)
                .then((response) => response.json())
                .then((responseJson) => {
                    console.log(responseJson);
                    this.setState({settings: responseJson.response.items})
                })
        } catch (error) {
        }
    }

    changeValue()
    {

    }


    render() {

        let settingsList = this.state.settings.map((setting, index) => {
            let settingId = Object.keys(setting);
            let settingObject = Object.values(setting)[0];

            console.log(settingObject);

            return <SettingsListItem key={settingId} id={settingId} name={settingObject.name} description={settingObject.description} value={settingObject.value} changeValue={ this.changeValue } />
        });

        return (
            <div>
                <table className="settings-list-table">
                    <thead className="settings-list-table-thead"><tr>
                        <th>Nazwa techniczna</th>
                        <th>Opis</th>
                        <th>wartość</th>
                    </tr></thead>
                    <tbody className="settings-list-table-tbody">
                        {settingsList}
                    </tbody>
                </table>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<SettingsMain />);