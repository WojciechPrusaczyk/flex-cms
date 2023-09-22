import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import SettingsListItem from "../../../components/sections/sectionsListItem";
import SectionsListItem from "../../../components/sections/sectionsListItem";
import Confirmation from "../../../components/confirmation";
import {Tooltip} from "../../../components/Tooltip";

class SectionsMain extends Component
{
    // https://dev.to/h8moss/build-a-reorderable-list-in-react-29on
    constructor(props) {
        super(props);
        this.state = {
            sections: [],
            showConfirmation: false,
            itemToDelete: 0,
        }
        this.getSections = this.getSections.bind(this);
        this.getSections();
        this.deleteHandler = this.deleteHandler.bind(this);
        this.deleteSection = this.deleteSection.bind(this);
        this.showConfirmation = this.showConfirmation.bind(this);
        this.hideConfirmation = this.hideConfirmation.bind(this);
    }

    async getSections()
    {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/sections/get-sections?`;
        try {
            const response = await fetch(fetchAddress)
                .then((response) => response.json())
                .then((responseJson) => {
                    this.setState({sections: responseJson.response.items})
                })
        } catch (error) {
            // Handle any other errors that may occur during the fetch
            console.error("An error occurred while fetching stylesheets data: ", error);
        }
    }
    deleteHandler(id)
    {
        this.setState( {itemToDelete: id} );
        this.showConfirmation();
    }

    // Show delete confirmation modal
    showConfirmation() { this.setState({showConfirmation: true}) }

    // Hide the delete confirmation modal
    hideConfirmation() { this.setState({showConfirmation: false}) }

    deleteSection() {
        window.location.href = `${location.protocol}//${window.location.host}/dashboard/sections/delete?` + new URLSearchParams({
            id: this.state.itemToDelete,
        });
    }

    // Redirect to the page for adding a new section
    addNewSection() {
        window.location.href = `${location.protocol}//${window.location.host}/dashboard/sections/new`;
    }

    render() {

        let sectionsList = this.state.sections.map((setting, index) => {
            let sectionId = Object.keys(setting);
            let sectionObject = Object.values(setting)[0];

            return <SectionsListItem
                        key={sectionId}
                        id={sectionId}
                        name={sectionObject.name}
                        lastEditedBy={sectionObject.lastEditedBy}
                        description={sectionObject.description}
                        value={sectionObject.value}
                        changeValue={ this.changeValue }
                        type={sectionObject.type}
                        deleteHandler={this.deleteHandler} />
        });

        return (
            <div>
                {this.state.showConfirmation && (
                    <Confirmation
                        action={this.deleteSection}
                        close={this.hideConfirmation}
                        text="Czy na pewno chcesz usunąć sekcję?"
                    />
                )}
                <div className="sections-head">
                    <div id="tooltip-root">
                        <Tooltip
                            text="Sekcje to moduł odpowiadający za zawartość strony głównej. Można tutaj dowolnie zmieniać wybrane treści na stronie głównej."
                        />
                    </div>
                    <input
                        type="button"
                        value="Dodaj"
                        className="sections-head-button"
                        onClick={this.addNewSection}
                    />
                </div>
                <table className="sections-list-table">
                    <thead className="sections-list-table-thead"><tr>
                        <th>Nazwa</th>
                        <th>Ostatnio zmieniał</th>
                        <th>Kolejność</th>
                        <th>Aktywny</th>
                        <th>Edytuj</th>
                        <th>Usuń</th>
                    </tr></thead>
                    <tbody className="sections-list-table-tbody">
                        {sectionsList}
                    </tbody>
                </table>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<SectionsMain />);