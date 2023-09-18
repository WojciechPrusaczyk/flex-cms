import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import StylesheetsListItem from "../../../components/stylesheets/stylesheetsListItem";
import Confirmation from "../../../components/confirmation";

class StylesheetsMain extends Component
{
    constructor(props) {
        super(props);
        this.state = {
            stylesheets: [],
            showConfirmation: false,
            itemToDelete: 0,
        }
        this.getSettings();
        this.showConfirmation = this.showConfirmation.bind(this);
        this.hideConfirmation = this.hideConfirmation.bind(this);
        this.deleteHandler = this.deleteHandler.bind(this);
        this.deleteStylesheet = this.deleteStylesheet.bind(this);
    }

    async getSettings()
    {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/stylesheets/get-stylesheets`;
        try {
            const response = await fetch(fetchAddress)
                .then((response) => response.json())
                .then((responseJson) => {
                    this.setState({stylesheets: responseJson.response.items})
                })
        } catch (error) {
        }
    }

    addNewStylesheet()
    {
        window.location.href = `${location.protocol}//${window.location.host}/dashboard/stylesheets/new`
    }

    showConfirmation() { this.setState({showConfirmation: true}) }
    hideConfirmation() { this.setState({showConfirmation: false}) }

    deleteHandler(id)
    {
        this.setState( {itemToDelete: id} );
        this.showConfirmation();
    }

    deleteStylesheet() {
        window.location.href = `${location.protocol}//${window.location.host}/dashboard/stylesheets/delete?` + new URLSearchParams({
            id: this.state.itemToDelete,
        });
    }

    render() {
        let stylesheetsList = this.state.stylesheets.map((setting, index) => {
            let stylesheetId = Object.keys(setting);
            let stylesheetObject = Object.values(setting)[0];

            return <StylesheetsListItem key={stylesheetId} id={stylesheetId} name={stylesheetObject.name} lastEditedBy={stylesheetObject.lastEditedBy} active={stylesheetObject.active} deleteHandler={this.deleteHandler} />
        });

        return (
            <div>
                <div className="stylesheets-head"><input type="button" value="Dodaj" className="stylesheets-head-button" onClick={ this.addNewStylesheet }/></div>
                {this.state.showConfirmation && <Confirmation action={ this.deleteStylesheet } close={ this.hideConfirmation } text="Czy na pewno chcesz usunąć?" />}
                <table className="stylesheets-list-table">
                    <thead className="stylesheets-list-table-thead"><tr>
                        <th>Nazwa</th>
                        <th>Ostatnio zmienione przez</th>
                        <th>Aktywny</th>
                        <th>Edytuj</th>
                        <th>Usuń</th>
                    </tr></thead>
                    <tbody className="stylesheets-list-table-tbody">
                        {stylesheetsList}
                    </tbody>
                </table>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<StylesheetsMain />);