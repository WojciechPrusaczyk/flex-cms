import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import Tile from "../../components/tile";

class Settings extends Component
{
    constructor(props) {
        super(props);
        this.state = {
            settings: []
        }
        this.getSettings();
    }
    async getSettings()
    {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/get-dashboard-settings`;

        try {
            const response = await fetch(fetchAddress);
            const jsonResponse = await response.json();

            if ( jsonResponse['status'] === "success")
            {
                this.setState({settings: jsonResponse["response"][0]})
            }
        } catch (error) {
        }
    }



    render() {

        let settings = this.state.settings.map(tile => {
            return <Tile key={tile.name} name={tile.name} icon={tile.icon} href={tile.href} isActive={tile.isActive}></Tile>
        });

        return (
            <div className="settings">
                <div className="settings-column">
                    {settings}
                </div>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<Settings />);