import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import { Helmet } from "react-helmet";
import Header from "../../../components/webpage/header";
import Footer from "../../../components/webpage/footer";
import Caption from "../../../components/webpage/caption";

class Gallery extends Component
{
    constructor(props) {
        super(props);
        this.state = {
            isDataLoaded: false,
            scripts: null,
            stylesheets: null,
            colors: null,
            settings: null,
            sections: null,
            helmetKey: 0
        }
        this.getDataFromApi();
    }

    async getDataFromApi()
    {
        try {

            await this.loadScripts().then( () => {
                if ( null == this.state.scripts ) { console.log("Error occured while fetching scripts data. Try again later.") }
            })

            await this.loadStylesheets().then( () => {
                if ( null == this.state.style ) { console.log("Error occured while fetching stylesheets data. Try again later.") }
            })

            await this.loadColors().then( () => {
                if ( null == this.state.colors ) { console.log("Error occured while fetching colors data. Try again later.") }
            })

            await this.loadSettings().then( () => {
                if ( null == this.state.settings ) { console.log("Error occured while fetching webpage settings data. Try again later.") }
            })

            await this.loadSections().then( () => {
                if ( null == this.state.sections ) { console.log("Error occured while fetching webpage data. Try again later.") }
            })
        }
        catch(exception)
        {
        }
    }

    async loadScripts()
    {
        // Scripts
        const response = await fetch(`${location.protocol}//${window.location.host}/api/get-scripts`);
        if (response.ok){
            const jsonResponse = await response.json();

            if (jsonResponse['status'] === "success") {
                let javascript = "";
                Object.values(jsonResponse.response).forEach((element) => {
                    javascript += `/*${element.name}*/${element.value}`
                })

                this.setState((prevState) => ({
                    scripts: javascript,
                    helmetKey: prevState.helmetKey + 1
                }));
            }
        }
        else {
            console.log("Error occured while fetching scripts data. Try again later.")
            await this.loadScripts();
        }
    }

    async loadStylesheets()
    {
        // Stylesheets
        const stylesheetsResponse = await fetch(`${location.protocol}//${window.location.host}/api/get-stylesheets`);
        if (stylesheetsResponse.ok) {
            const stylesheetsJsonResponse = await stylesheetsResponse.json();

            if (stylesheetsJsonResponse['status'] === "success") {
                let stylesheets = "";
                Object.values(stylesheetsJsonResponse.response).forEach((element) => {
                    stylesheets += `/*${element.name}*/${element.value}`
                })
                this.setState((prevState) => ({
                    stylesheets: stylesheets,
                    helmetKey: prevState.helmetKey + 1
                }));

            }
        }
        else {
            console.log("Error occured while fetching stylesheets data. Try again later.")
            await this.loadStylesheets();
        }
    }

    async loadColors()
    {

        // Colors
        const colorsResponse = await fetch(`${location.protocol}//${window.location.host}/api/get-colors`);
        if (colorsResponse.ok) {
            const colorsJsonResponse = await colorsResponse.json();

            if (colorsJsonResponse['status'] === "success") {

                let colors = ":root{"
                Object.values(colorsJsonResponse.response).forEach((element) => {
                    colors += `--${element.name.replace(/[^a-z]/gi, '')}:${element.value};`
                })
                colors += "}";
                this.setState((prevState) => ({
                    colors: colors,
                    helmetKey: prevState.helmetKey + 1
                }));
            }
        }
        else {
            console.log("Error occured while fetching colors data. Try again later.")
            await this.loadColors();
        }
    }

    async loadSettings()
    {
        // Settings
        const settingsResponse = await fetch(`${location.protocol}//${window.location.host}/api/get-settings`);
        if (settingsResponse.ok) {
            const settingsJsonResponse = await settingsResponse.json();

            if (settingsJsonResponse['status'] === "success") {

                let settings = [];
                for (const [key, value] of Object.entries(settingsJsonResponse.response)) {
                    settings[value.name] = value.value
                }

                this.setState({
                    settings: settings
                });
            }
        }
        else {
            console.log("Error occured while fetching webpage settings data. Try again later.")
            await this.loadSettings();
        }
    }

    async loadSections()
    {
        // Sections
        const sectionsResponse = await fetch(`${location.protocol}//${window.location.host}/api/get-sections`);
        if (sectionsResponse.ok) {
            const sectionsJsonResponse = await sectionsResponse.json();

            if (sectionsJsonResponse['status'] === "success") {
                this.setState({
                    sections: Object.values(sectionsJsonResponse.response)
                });
            }
        }
        else {
            console.log("Error occured while fetching webpage data. Try again later.")
            await this.loadSections();
        }
    }

    render() {
        const loadingScreen = <main id="loadingScreen">
            <div>
                <h1> Loading </h1>
                <div className="loader"></div>
            </div>
        </main>

        let readyToGoWebpage = null;
        if (this.state.isDataLoaded)
        {

            readyToGoWebpage = <div>
                <Header logo={this.state.settings.headerLogo} />
                <main>
                    <div id="main-content">
                        <Caption header={this.state.settings.galleryHeader} description={this.state.settings.galleryDescription} />
                        Galeria
                    </div>
                </main>
                <Footer companyEmailAddress={this.state.settings.companyEmailAddress} companyPhoneNumber={this.state.settings.companyPhoneNumber} companyAddress={this.state.settings.companyAddress} />
            </div>
        }

        if (
            null != this.state.scripts &&
            null != this.state.stylesheets &&
            null != this.state.colors &&
            null != this.state.settings &&
            null != this.state.sections
        )
        {
            this.setState( { isDataLoaded: true } )
        }

        return (
            <div>

                { !this.state.isDataLoaded && ( loadingScreen )}
                { this.state.isDataLoaded && ( readyToGoWebpage )}

                <Helmet key={this.state.helmetKey}>
                    <script>
                        {this.state.scripts}
                    </script>
                    <style>
                        {this.state.stylesheets}
                    </style>
                    <style>
                        {this.state.colors}
                    </style>
                </Helmet>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("root"));

root.render(<Gallery />);