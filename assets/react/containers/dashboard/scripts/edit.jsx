import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import ScriptsEditor from "../../editorJS/scriptsEditor";
import moment from 'moment-timezone';

class ScriptsForm extends Component
{
    constructor(props) {
        super(props);
        this.state = {
            id: 0,
            name: "...",
            active: false,
            value: {},
            start_being_active: "YYYY-DD-MM HH:MM:SS",
            stop_being_active: "YYYY-DD-MM HH:MM:SS",
            isFormDataReady: false,
        };

        // Binding methods to the current instance
        this.handleDataChange = this.handleDataChange.bind(this);
        this.logAllData = this.logAllData.bind(this);
    }

    componentDidMount() {
        // Fetch initial data when the component mounts
        this.syncData();
    }

    // Debug method to log all state data
    logAllData() {
        console.log(this.state);
    }

    // Method to update the "value" state with new data
    handleDataChange = (newData) => {
        this.setState({ value: newData });
    };

    // Synchronize data from the URL parameters
    syncData() {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get("id");
        if (id !== undefined && id !== null) {
            this.setState({ id: id });
            this.getInitialData(id);
        }
    }

    // Fetch initial data from the server
    async getInitialData(id) {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/scripts/get-script?` + new URLSearchParams({
            id: id,
        });

        try {
            const response = await fetch(fetchAddress);
            const jsonResponse = await response.json();

            if (jsonResponse['status'] === "success") {
                let returnedObject = jsonResponse["response"]["entity"];

                // Assuming convertToDatetimeLocalValue is defined elsewhere
                const startDate = convertToDatetimeLocalValue(returnedObject.start_being_active);
                const endDate = convertToDatetimeLocalValue(returnedObject.stop_being_active);

                this.setState({
                    name: returnedObject.name,
                    active: returnedObject.active,
                    value: returnedObject.value,
                    start_being_active: startDate,
                    stop_being_active: endDate,
                    isFormDataReady: true,
                });
            }
        } catch (error) {
            // Handle any errors that may occur during the fetch
            console.error("An error occurred while fetching initial data:", error);
        }
    }

    // Method to save data to the server
    async saveData() {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/scripts/edit-script?` + new URLSearchParams({
            id: this.state.id,
            name: this.state.name,
            active: this.state.active,
            value: JSON.stringify(this.state.value),
            start_being_active: this.state.start_being_active,
            stop_being_active: this.state.stop_being_active,
        });

        try {
            const response = await fetch(fetchAddress);
            const jsonResponse = await response.json();

            if (jsonResponse['status'] === "success") {
                window.location.href = `${location.protocol}//${window.location.host}/dashboard/scripts`;
            }
        } catch (error) {
            // Handle any errors that may occur during the fetch
            console.error("An error occurred while saving data:", error);
        }
    }

    render() {
        // Define FormComponent JSX
        const FormComponent = (
            <div className="editor-form">
                <p>
                    <label htmlFor="name">Nazwa</label>
                    <input
                        id="name"
                        className="editor-form-name"
                        type="text"
                        defaultValue={this.state.name}
                        onChange={(e) => this.setState({ name: e.target.value })}
                    />
                </p>
                <p>
                    <label htmlFor="active">Aktywny</label>
                    <input
                        id="active"
                        className="editor-form-active"
                        type="checkbox"
                        defaultChecked={this.state.active}
                        onChange={(e) => this.setState({ active: e.target.checked })}
                    />
                </p>
                <p>
                    <label htmlFor="start-being-active">Aktywny od</label>
                    <input
                        id="start-being-active"
                        className="editor-form-start_being_active"
                        type="datetime-local"
                        defaultValue={this.state.start_being_active}
                        onChange={(e) =>
                            this.setState({
                                start_being_active:
                                    e.target.value.length < 19
                                        ? e.target.value + ":00"
                                        : e.target.value,
                            })
                        }
                    />
                </p>
                <p>
                    <label htmlFor="stop-being-active">Aktywny do</label>
                    <input
                        id="stop-being-active"
                        className="editor-form-stop_being_active"
                        type="datetime-local"
                        defaultValue={this.state.stop_being_active}
                        onChange={(e) =>
                            this.setState({
                                stop_being_active:
                                    e.target.value.length < 19
                                        ? e.target.value + ":00"
                                        : e.target.value,
                            })
                        }
                    />
                </p>
                <ScriptsEditor
                    className="editor-field"
                    defaultData={this.state.value}
                    onDataChange={this.handleDataChange}
                />
                <p className="editor-save">
                    <input
                        className="editor-form-field-submit"
                        type="submit"
                        onClick={(e) => {
                            e.preventDefault();
                            this.saveData();
                        }}
                        value="Zapisz"
                    />
                </p>
            </div>
        );

        return (
            <div className="editor">
                {this.state.isFormDataReady && FormComponent}
            </div>
        );
    }
}

function convertToDatetimeLocalValue(dateTime) {
    if (null != dateTime)
    {
        // Parse the date with time zone information (assuming it's in 'Europe/Berlin' time zone)
        const momentDate = moment.tz(dateTime.date, 'Europe/Berlin');

        // Transform it to ISO 8601 format with 'T' character
        return momentDate.format('YYYY-MM-DDTHH:mm:ss');
    } else return null;
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<ScriptsForm />);