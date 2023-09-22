import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import SettingsListItem from "../../../components/sections/sectionsListItem";
import SectionsListItem from "../../../components/sections/sectionsListItem";
import Confirmation from "../../../components/confirmation";
import {Tooltip} from "../../../components/Tooltip";
import { DragDropContext, Droppable, Draggable } from "react-beautiful-dnd";

class SectionsMain extends Component
{
    // https://dev.to/h8moss/build-a-reorderable-list-in-react-29on
    constructor(props) {
        super(props);
        this.state = {
            sections: [],
            showConfirmation: false,
            itemToDelete: 0,
            isOrderChanged: false,
            draggedObject: null
        }
        this.getSections = this.getSections.bind(this);
        this.getSections();
        this.deleteHandler = this.deleteHandler.bind(this);
        this.deleteSection = this.deleteSection.bind(this);
        this.showConfirmation = this.showConfirmation.bind(this);
        this.hideConfirmation = this.hideConfirmation.bind(this);
        this.saveOrder = this.saveOrder.bind(this);
        this.onDragStart = this.onDragStart.bind(this);
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

    saveOrder()
    {
        let validOrder = [];

        this.state.sections.map((section, index) => {
            validOrder[index] = {
                id: Object.keys(section)[0],
                position: Object.values(section)[0].position,
            }
        });

        this.sendData(validOrder, `${location.protocol}//${window.location.host}/dashboard/sections/change-order`);
    }

    onDragEnd = (result) => {
        if (!result.destination) return;

        this.setState({draggedObject: null});
        this.setState({isOrderChanged: true});

        const reorderedSections = [...this.state.sections];
        const [reorderedSection] = reorderedSections.splice(result.source.index, 1);
        reorderedSections.splice(result.destination.index, 0, reorderedSection);

        // Updating sections.position
        reorderedSections.forEach((section, index) => {
            Object.values(section)[0].position = index;
        });

        this.setState({ sections: reorderedSections });
    };

    onDragStart(e)
    {
        this.setState({draggedObject: e.draggableId});
    }

    async sendData(data, address)
    {
        try {
            const response = await fetch(address, {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            });
            window.location.href = `${location.protocol}//${window.location.host}/dashboard/sections`;
        } catch (error) {
            // Handle any errors that may occur during the fetch
            console.error("An error occurred while saving data:", error);
        }
    }

    render() {

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
                    <p>
                        <input
                        type="button"
                        value="Dodaj"
                        className="sections-head-button"
                        onClick={this.addNewSection}
                        />
                        {this.state.isOrderChanged && <input
                            type="button"
                            value="Zapisz kolejność"
                            className="sections-head-button"
                            onClick={this.saveOrder}
                        />}
                    </p>
                </div>
                <table className="sections-list-table">
                    <thead className="sections-list-table-thead"><tr>
                        <th>Nazwa</th>
                        <th>Ostatnio zmieniał</th>
                        <th>Aktywny</th>
                        <th>Edytuj</th>
                        <th>Usuń</th>
                    </tr></thead>
                    <DragDropContext onDragEnd={this.onDragEnd} onDragStart={this.onDragStart}>
                        <Droppable droppableId="sections-list">
                            {(provided) => (
                                <tbody
                                    className="sections-list-table-tbody"
                                    {...provided.droppableProps}
                                    ref={provided.innerRef}
                                >
                                {this.state.sections.map((section, index) => (
                                    <Draggable
                                        key={Object.keys(section)[0]}
                                        draggableId={Object.keys(section)[0]}
                                        index={index}
                                    >
                                        {(provided) => (
                                            <tr
                                                {...provided.draggableProps}
                                                {...provided.dragHandleProps}
                                                ref={provided.innerRef}
                                                className={(this.state.draggedObject == Object.keys(section))? "sections-list-table-tbody-item draggedObject" : "sections-list-table-tbody-item"}
                                            >
                                                <SectionsListItem
                                                key={Object.keys(section)}
                                                id={Object.keys(section)}
                                                name={Object.values(section)[0].name}
                                                lastEditedBy={Object.values(section)[0].lastEditedBy}
                                                description={Object.values(section)[0].description}
                                                value={Object.values(section)[0].value}
                                                changeValue={ this.changeValue }
                                                type={Object.values(section)[0].type}
                                                deleteHandler={this.deleteHandler} />
                                            </tr>
                                        )}
                                    </Draggable>
                                ))}
                                {provided.placeholder}
                                </tbody>
                            )}
                        </Droppable>
                    </DragDropContext>
                </table>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<SectionsMain />);