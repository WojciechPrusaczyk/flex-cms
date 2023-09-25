import ReactDOM from "react-dom/client";
import React, { Component } from "react";
import ScriptsListItem from "../../../components/scripts/scriptsListItem";
import Confirmation from "../../../components/confirmation";
import {Tooltip} from "../../../components/Tooltip";

class ScriptsMain extends Component {
    constructor(props) {
        super(props);
        this.state = {
            scripts: [],
            showConfirmation: false,
            itemToDelete: 0,
        };

        // Fetch scripts data when the component is initialized
        this.getSettings();

        // Bind methods to the current instance
        this.showConfirmation = this.showConfirmation.bind(this);
        this.hideConfirmation = this.hideConfirmation.bind(this);
        this.deleteHandler = this.deleteHandler.bind(this);
        this.deleteScript = this.deleteScript.bind(this);
    }

    // Fetch scripts data from the server
    async getSettings() {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/scripts/get-scripts`;
        try {
            const response = await fetch(fetchAddress);
            if (response.ok) {
                const responseJson = await response.json();
                this.setState({ scripts: responseJson.response.items });
            } else {
                // Handle fetch error here
                console.error("Failed to fetch scripts data.");
            }
        } catch (error) {
            // Handle any other errors that may occur during the fetch
            console.error("An error occurred while fetching scripts data:", error);
        }
    }

    // Redirect to the page for adding a new script
    addNewScript() {
        window.location.href = `${location.protocol}//${window.location.host}/dashboard/scripts/new`;
    }

    // Show delete confirmation modal
    showConfirmation() { this.setState({showConfirmation: true}) }

    // Hide the delete confirmation modal
    hideConfirmation() { this.setState({showConfirmation: false}) }

    deleteHandler(id)
    {
        this.setState( {itemToDelete: id} );
        this.showConfirmation();
    }

    deleteScript() {
        window.location.href = `${location.protocol}//${window.location.host}/admin-api/dashboard/scripts/delete?` + new URLSearchParams({
            id: this.state.itemToDelete,
        });
    }

    render() {
        let scriptsList = this.state.scripts.map((setting, index) => {
            let scriptId = Object.keys(setting);
            let scriptObject = Object.values(setting)[0];

            return (
                <ScriptsListItem
                    key={scriptId}
                    id={scriptId}
                    name={scriptObject.name}
                    lastEditedBy={scriptObject.lastEditedBy}
                    active={scriptObject.active}
                    deleteHandler={this.deleteHandler}
                />
            );
        });

        return (
            <div>
                <div className="scripts-head">
                    <div id="tooltip-root">
                        <Tooltip
                            text="Moduł dla zaawansowanych użytkowników, którzy chcą dodać interaktywności strony, czy zmieniać treści strony głównej."
                        />
                    </div>
                    <input
                        type="button"
                        value="Dodaj"
                        className="scripts-head-button"
                        onClick={this.addNewScript}
                    />
                </div>
                {this.state.showConfirmation && (
                    <Confirmation
                        action={this.deleteScript}
                        close={this.hideConfirmation}
                        text="Czy na pewno chcesz usunąć?"
                    />
                )}
                <table className="scripts-list-table">
                    <thead className="scripts-list-table-thead">
                    <tr>
                        <th>Nazwa</th>
                        <th>Ostatnio zmienione przez</th>
                        <th>Status</th>
                        <th>Edytuj</th>
                        <th>Usuń</th>
                    </tr>
                    </thead>
                    <tbody className="scripts-list-table-tbody">{scriptsList}</tbody>
                </table>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<ScriptsMain />);
