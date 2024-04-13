import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import StylesheetsEditor from "../../editorJS/stylesheetEditor";
import moment from 'moment-timezone';
import Form from "../../../components/sections/form";

class SectionsForm extends Component
{
    constructor(props) {
        super(props);
        this.state = {
            id: 0,
            name: "...",
            active: false,
            isWide: true,
            value: {},
            start_being_active: "2000-01-01T00:00:00",
            stop_being_active: "2030-01-01T00:12:00",
            isTitleVisible: true,
            isFormDataReady: false,
        };

        // Binding methods to the current instance
        this.logAllData = this.logAllData.bind(this);
        this.getData = this.getData.bind(this);
        this.dataHandler = this.dataHandler.bind(this);
        this.saveData = this.saveData.bind(this);
    }

    componentDidMount() {
        // Fetch initial data when the component mounts
        this.syncData();
    }

    // Debug method to log all state data
    logAllData() {
        console.log(this.state);
    }

    // Method to get data from sections form
    getData(data) {
        this.setState({value: data})
    };

    dataHandler(data)
    {
        this.setState({
            name: data.name,
            active: data.active,
            isWide: data.isWide,
            start_being_active: data.start_being_active,
            stop_being_active: data.stop_being_active,
            isTitleVisible: data.isTitleVisible,
        })
    }

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
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/sections/get-section?` + new URLSearchParams({
            id: id,
        });

        try {
            const response = await fetch(fetchAddress);
            const jsonResponse = await response.json();

            if (jsonResponse['status'] === "success") {
                let returnedObject = jsonResponse["response"]["entity"];

                // Function converting DateTime to valid value
                const startDate = convertToDatetimeLocalValue(returnedObject.start_being_active);
                const endDate = convertToDatetimeLocalValue(returnedObject.stop_being_active);
                this.setState({
                    name: returnedObject.name,
                    active: returnedObject.active,
                    isWide: returnedObject.isWide,
                    value: returnedObject.value,
                    start_being_active: startDate,
                    stop_being_active: endDate,
                    isTitleVisible: returnedObject.isTitleVisible,
                    isFormDataReady: true,
                });
            }
        } catch (error) {
            // Handle any errors that may occur during the fetch
            console.error("An error occurred while fetching initial data:", error);
        }
    }

    // Method to save data to the server
    saveData() {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/sections/edit-section`;
        const data = {
            id: this.state.id,
            name: this.state.name,
            active: this.state.active,
            isWide: this.state.isWide,
            value: JSON.stringify(this.state.value),
            start_being_active: this.state.start_being_active,
            stop_being_active: this.state.stop_being_active,
            isTitleVisible: this.state.isTitleVisible,
        };
        this.sendData(data, fetchAddress);
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
            const jsonResponse = await response.json();

            if (jsonResponse['status'] === "success") {
                window.location.href = `${location.protocol}//${window.location.host}/dashboard/sections`;
            }
        } catch (error) {
            // Handle any errors that may occur during the fetch
            console.error("An error occurred while saving data:", error);
        }
    }

    render() {
        return (
            <div className="editor">
                {this.state.isFormDataReady &&
                    <Form
                        name={this.state.name}
                        active={this.state.active}
                        isWide={this.state.isWide}
                        start_being_active={this.state.start_being_active}
                        stop_being_active={this.state.stop_being_active}
                        isTitleVisible={this.state.isTitleVisible}
                        handleDataChange={this.getData}
                        dataHandler={this.dataHandler}
                        submitHandler={this.saveData}
                        defaultData={this.state.value}
                    />}
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

root.render(<SectionsForm />);