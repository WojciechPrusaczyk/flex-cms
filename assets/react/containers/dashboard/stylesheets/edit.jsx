import ReactDOM from "react-dom/client";
import React, {Component, createRef, useRef, useState} from "react";
import StylesheetsEditor from "../../editorJS/stylesheetEditor";
import moment from 'moment-timezone';

class StylesheetsForm extends Component
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
        }
        this.handleDataChange = this.handleDataChange.bind(this);
        this.logAllData = this.logAllData.bind(this);
    }
    componentDidMount() {
        this.syncData();
    }

    // debug method
    logAllData()
    {
        console.log(this.state);
    }

    // method to import data from editorjs component
    handleDataChange = (newData) => {
        this.setState({ value: newData });
    };

    syncData()
    {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get("id");
        if( undefined != id && null != id)
        {
            this.setState({id: id});
            this.getInitialData(id);
        }
    }

    async getInitialData(id)
    {
        const fetchAddress = `${location.protocol}//${window.location.host}/dashboard/stylesheets/get-stylesheet?` + new URLSearchParams({
            id: id,
        });

        // try {
            const response = await fetch(fetchAddress);
            const jsonResponse = await response.json();

            if ( jsonResponse['status'] === "success")
            {
                let returnedObject = jsonResponse["response"]["entity"];

                const startDate = convertToDatetimeLocalValue(returnedObject.start_being_active.date);
                const endDate = convertToDatetimeLocalValue(returnedObject.stop_being_active.date);

                this.setState({
                    name: returnedObject.name,
                    active: returnedObject.active,
                    value: returnedObject.value,
                    start_being_active: startDate,
                    stop_being_active: endDate,
                    isFormDataReady: true,
                })
            }
        // } catch (error) {
        // }

    }

    async saveData()
    {
        const fetchAddress = `${location.protocol}//${window.location.host}/dashboard/stylesheets/edit-stylesheet?` + new URLSearchParams({
            id: this.state.id,
            name: this.state.name,
            active: this.state.active,
            value: JSON.stringify(this.state.value),
            start_being_active: this.state.start_being_active,
            stop_being_active: this.state.stop_being_active,
        });

        const response = await fetch(fetchAddress);
        const jsonResponse = await response.json();

        if ( jsonResponse['status'] === "success")
        {
            window.location.href = `${location.protocol}//${window.location.host}/dashboard/stylesheets`
        }
    }

    render() {

        const FormComponent = <div className="editor-form">
                <input className="editor-form-name" type="text" defaultValue={this.state.name} onChange={ (e) => this.setState({name: e.target.value}) } />
                <input className="editor-form-active" type="checkbox" defaultChecked={this.state.active} onChange={ (e) => this.setState({active: e.target.checked}) } />
                <input className="editor-form-start_being_active" type="datetime-local" defaultValue={this.state.start_being_active}
                       onChange={ (e) => this.setState({start_being_active: (e.target.value.length<19)?e.target.value+":00":e.target.value}) } />
                <input className="editor-form-stop_being_active" type="datetime-local" defaultValue={this.state.stop_being_active}
                       onChange={ (e) => this.setState({stop_being_active: (e.target.value.length<19)?e.target.value+":00":e.target.value}) } />
            </div>;


        const textEditor = <StylesheetsEditor className="editor-field" defaultData={ this.state.value } onDataChange={this.handleDataChange} />

        return (
            <div className="editor">
                {this.state.isFormDataReady && FormComponent}
                {this.state.isFormDataReady && textEditor}
                <p className="editor-save">
                    <input className="editor-save-button" type="submit" onClick={ (e) => {
                        e.preventDefault();
                        this.saveData();
                    }
                    } value="Zapisz" />
                </p>
            </div>
        );
    }
}

function convertToDatetimeLocalValue(dateTimeString) {
    // Parsuj datę z informacją o strefie czasowej
    const momentDate = moment.tz(dateTimeString, 'Europe/Berlin');

    // Przekształć do formatu ISO 8601 (datetime-local)
    const isoString = momentDate.format('YYYY-MM-DDTHH:mm:ss');

    return isoString;
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<StylesheetsForm />);