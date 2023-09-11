import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import SettingsListItem from "../../../components/settings/settingsListItem";

class StylesheetsMain extends Component
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
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/settings/get-settings?`;
        try {
            const response = await fetch(fetchAddress)
                .then((response) => response.json())
                .then((responseJson) => {
                    this.setState({settings: responseJson.response.items})
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
            const settingObject = Object.values(matchingSettings[0])[0];
            const settingId = Object.keys(matchingSettings[0])[0];
            const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/settings/set-value?`;
            let fetchTextUrl = "";

                switch (settingObject.type)
                {
                    case "file":

                        if (null != event.target.files[0]) {
                            let requestedFile = event.target.files[0];
                            let reader = new FileReader();
                            reader.readAsDataURL(requestedFile);

                            reader.onload = () => {

                                const formData = new FormData();

                                formData.append('id', settingId);
                                formData.append('file', requestedFile);

                                fetch(fetchAddress, {
                                    method: "POST",
                                    body: formData
                                })
                                    .then(res => res.json())
                                    .then(data => {
                                            if (data.status === "success")
                                            {
                                                this.updatePhoto(id, data.response.filename);
                                            }
                                        }
                                    );
                            };
                        }
                        break;
                    case "boolean":
                        const isChecked = (event.target.checked)?true:false;


                        fetchTextUrl = fetchAddress + new URLSearchParams({
                            id: id,
                            value: isChecked,
                        });

                        try {
                            const response = await fetch(fetchTextUrl)
                                .then((response) => response.json())
                                .then((responseJson) => {
                                    // console.log(responseJson);
                                })
                        } catch (error) {
                        }
                        break;
                    case "text":
                    default:
                        const requestedValue = event.target.value;

                        fetchTextUrl = fetchAddress + new URLSearchParams({
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
                        break;
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

            return <SettingsListItem key={settingId} id={settingId} name={settingObject.name} description={settingObject.description} value={settingObject.value} changeValue={ this.changeValue } type={settingObject.type} />
        });

        return (
            <div>
                <table className="settings-list-table">
                    <thead className="settings-list-table-thead"><tr>
                        <th>Nazwa techniczna</th>
                        <th>Opis</th>
                        <th>Wartość</th>
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