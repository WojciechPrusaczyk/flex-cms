import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import StylesheetsListItem from "../../../components/stylesheets/stylesheetsListItem";

class StylesheetsMain extends Component
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
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/stylesheets/get-stylesheets`;
        try {
            const response = await fetch(fetchAddress)
                .then((response) => response.json())
                .then((responseJson) => {
                    this.setState({settings: responseJson.response.items})
                })
        } catch (error) {
        }
    }

    addNewStylesheet()
    {
        window.location.href = `${location.protocol}//${window.location.host}/dashboard/stylesheets/new`
    }

    render() {

        let settingsList = this.state.settings.map((setting, index) => {
            let settingId = Object.keys(setting);
            let settingObject = Object.values(setting)[0];

            return <StylesheetsListItem key={settingId} id={settingId} name={settingObject.name} lastEditedBy={settingObject.lastEditedBy} active={settingObject.active} />
        });

        return (
            <div>
                <div className="stylesheets-head"><input type="button" value="Dodaj" className="stylesheets-head-button" onClick={ this.addNewStylesheet }/></div>
                <table className="stylesheets-list-table">
                    <thead className="stylesheets-list-table-thead"><tr>
                        <th>Nazwa</th>
                        <th>Ostatnio zmienione przez</th>
                        <th>Aktywny</th>
                        <th>Edytuj</th>
                        <th>Usuń</th>
                    </tr></thead>
                    <tbody className="stylesheets-list-table-tbody">
                        {settingsList}
                    </tbody>
                </table>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<StylesheetsMain />);