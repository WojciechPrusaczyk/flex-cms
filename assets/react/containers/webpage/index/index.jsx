import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import { Helmet } from "react-helmet";

class Index extends Component
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
        //try {

            // Scripts
            const response = await fetch(`${location.protocol}//${window.location.host}/api/get-scripts`);
            const jsonResponse = await response.json();

            if ( jsonResponse['status'] === "success")
            {
                let javascript = "";
                Object.values(jsonResponse.response).forEach((element) => {
                    javascript += `/*${element.name}*/${element.value}`
                })

                this.setState((prevState) => ({
                    scripts: javascript,
                    helmetKey: prevState.helmetKey + 1
                }));
            }

            // Stylesheets
            const stylesheetsResponse = await fetch(`${location.protocol}//${window.location.host}/api/get-stylesheets`);
            const stylesheetsJsonResponse = await stylesheetsResponse.json();

            if ( stylesheetsJsonResponse['status'] === "success")
            {
                let stylesheets = null;
                Object.values(stylesheetsJsonResponse.response).forEach((element) => {
                    stylesheets += `/*${element.name}*/${element.value}`
                })

                this.setState((prevState) => ({
                    stylesheets: stylesheets,
                    helmetKey: prevState.helmetKey + 1
                }));

            }

            // Colors
            const colorsResponse = await fetch(`${location.protocol}//${window.location.host}/api/get-colors`);
            const colorsJsonResponse = await colorsResponse.json();

            if ( colorsJsonResponse['status'] === "success")
            {

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

            // Settings
            const settingsResponse = await fetch(`${location.protocol}//${window.location.host}/api/get-settings`);
            const settingsJsonResponse = await settingsResponse.json();

            if ( settingsJsonResponse['status'] === "success")
            {
                this.setState({
                    settings: settingsJsonResponse.response
                });
            }

            // Sections
            const sectionsResponse = await fetch(`${location.protocol}//${window.location.host}/api/get-sections`);
            const sectionsJsonResponse = await sectionsResponse.json();

            if ( sectionsJsonResponse['status'] === "success")
            {
                this.setState({
                    sections: Object.values(sectionsJsonResponse.response)
                });
            }
    }

    render() {

        const loadingScreen = <main id="loadingScreen">
            <div>
                <h1> Loading </h1>
                <div className="loader"></div>
            </div>
        </main>

        const readyToGoWebpage = <div>
            <header>

            </header>
            <main>
                Działa!
            </main>
            <footer>

            </footer>
        </div>

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

root.render(<Index />);