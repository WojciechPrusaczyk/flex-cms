import ReactDOM from "react-dom/client";
import React, { Component } from "react";
import StylesheetsListItem from "../../../components/stylesheets/stylesheetsListItem";
import Confirmation from "../../../components/confirmation";
import {Tooltip} from "../../../components/Tooltip";

class StylesheetsMain extends Component {
    constructor(props) {
        super(props);
        this.state = {
            stylesheets: [],
            showConfirmation: false,
            itemToDelete: 0,
        };

        // Fetch stylesheets data when the component is initialized
        this.getSettings();

        // Bind methods to the current instance
        this.showConfirmation = this.showConfirmation.bind(this);
        this.hideConfirmation = this.hideConfirmation.bind(this);
        this.deleteHandler = this.deleteHandler.bind(this);
        this.deleteStylesheet = this.deleteStylesheet.bind(this);
    }

    // Fetch stylesheets data from the server
    async getSettings() {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/stylesheets/get-stylesheets`;
        try {
            const response = await fetch(fetchAddress);
            if (response.ok) {
                const responseJson = await response.json();
                this.setState({ stylesheets: responseJson.response.items });
            } else {
                // Handle fetch error here
                console.error("Failed to fetch stylesheets data.");
            }
        } catch (error) {
            // Handle any other errors that may occur during the fetch
            console.error("An error occurred while fetching stylesheets data:", error);
        }
    }

    // Redirect to the page for adding a new stylesheet
    addNewStylesheet() {
        window.location.href = `${location.protocol}//${window.location.host}/dashboard/stylesheets/new`;
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

    deleteStylesheet() {
        window.location.href = `${location.protocol}//${window.location.host}/admin-api/dashboard/stylesheets/delete?` + new URLSearchParams({
            id: this.state.itemToDelete,
        });
    }

    render() {
        let stylesheetsList = this.state.stylesheets.map((setting, index) => {
            let stylesheetId = Object.keys(setting);
            let stylesheetObject = Object.values(setting)[0];

            return (
                <StylesheetsListItem
                    key={stylesheetId}
                    id={stylesheetId}
                    name={stylesheetObject.name}
                    lastEditedBy={stylesheetObject.lastEditedBy}
                    active={stylesheetObject.active}
                    deleteHandler={this.deleteHandler}
                />
            );
        });

        return (
            <div>
                <div className="stylesheets-head">
                    <div id="tooltip-root">
                        <Tooltip
                            text="Moduł dla średniozaawansowanych użytkowników, którzy z poziomu administratora mogą dowolnie dodawać i zmieniać już dodane style strony internetowej zmieniające cały jej wygląd."
                        />
                    </div>
                    <input
                        type="button"
                        value="Dodaj"
                        className="stylesheets-head-button"
                        onClick={this.addNewStylesheet}
                    />
                </div>
                {this.state.showConfirmation && (
                    <Confirmation
                        action={this.deleteStylesheet}
                        close={this.hideConfirmation}
                        text="Czy na pewno chcesz usunąć?"
                    />
                )}
                <table className="stylesheets-list-table">
                    <thead className="stylesheets-list-table-thead">
                    <tr>
                        <th>Nazwa</th>
                        <th>Ostatnio zmienione przez</th>
                        <th>Status</th>
                        <th>Edytuj</th>
                        <th>Usuń</th>
                    </tr>
                    </thead>
                    <tbody className="stylesheets-list-table-tbody">{stylesheetsList}</tbody>
                </table>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<StylesheetsMain />);
