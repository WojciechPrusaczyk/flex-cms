import ReactDOM from "react-dom/client";
import React, { Component } from "react";
import { Helmet } from "react-helmet";
import Header from "../../../components/webpage/header";
import Footer from "../../../components/webpage/footer";
import Caption from "../../../components/webpage/caption";

class Gallery extends Component {
    constructor(props) {
        super(props);
        this.state = {
            isDataLoaded: false,
            scripts: null,
            stylesheets: null,
            colors: null,
            settings: null,
            photos: null,
            helmetKey: 0,
            photoInspectorActive: false,
            inspectedPhoto: null,
        };
        this.showPhotoInspector = this.showPhotoInspector.bind(this);
        this.hidePhotoInspector = this.hidePhotoInspector.bind(this);
        this.prevPhoto = this.prevPhoto.bind(this);
        this.nextPhoto = this.nextPhoto.bind(this);
    }

    async componentDidMount() {
        await this.getDataFromApi();
    }

    async getDataFromApi() {
        await this.loadScripts();
        await this.loadStylesheets();
        await this.loadColors();
        await this.loadSettings();
        await this.loadPhotos();
    }

    async loadScripts() {
        const response = await fetch(`${location.protocol}//${window.location.host}/api/get-scripts`);
        if (response.ok) {
            const jsonResponse = await response.json();

            if (jsonResponse['status'] === "success") {
                let javascript = "";
                Object.values(jsonResponse.response).forEach((element) => {
                    javascript += `/*${element.name}*/${element.value}`;
                });

                this.setState((prevState) => ({
                    scripts: javascript,
                    helmetKey: prevState.helmetKey + 1,
                }));
            }
        }
    }

    async loadStylesheets() {
        const stylesheetsResponse = await fetch(`${location.protocol}//${window.location.host}/api/get-stylesheets`);
        if (stylesheetsResponse.ok) {
            const stylesheetsJsonResponse = await stylesheetsResponse.json();

            if (stylesheetsJsonResponse['status'] === "success") {
                let stylesheets = "";
                Object.values(stylesheetsJsonResponse.response).forEach((element) => {
                    stylesheets += `/*${element.name}*/${element.value}`;
                });
                this.setState((prevState) => ({
                    stylesheets: stylesheets,
                    helmetKey: prevState.helmetKey + 1,
                }));
            }
        }
    }

    async loadColors() {
        const colorsResponse = await fetch(`${location.protocol}//${window.location.host}/api/get-colors`);
        if (colorsResponse.ok) {
            const colorsJsonResponse = await colorsResponse.json();

            if (colorsJsonResponse['status'] === "success") {
                let colors = ":root{";
                Object.values(colorsJsonResponse.response).forEach((element) => {
                    colors += `--${element.name.replace(/[^a-z]/gi, '')}:${element.value};`;
                });
                colors += "}";
                this.setState((prevState) => ({
                    colors: colors,
                    helmetKey: prevState.helmetKey + 1,
                }));
            }
        }
    }

    async loadSettings() {
        const settingsResponse = await fetch(`${location.protocol}//${window.location.host}/api/get-settings`);
        if (settingsResponse.ok) {
            const settingsJsonResponse = await settingsResponse.json();

            if (settingsJsonResponse['status'] === "success") {
                let settings = [];
                for (const [key, value] of Object.entries(settingsJsonResponse.response)) {
                    settings[value.name] = value.value;
                }

                this.setState({
                    settings: settings,
                });
            }
        }
    }

    async loadPhotos() {
        const photosResponse = await fetch(`${location.protocol}//${window.location.host}/api/get-photos`);
        if (photosResponse.ok) {
            const photosJsonResponse = await photosResponse.json();

            if (photosJsonResponse['status'] === "success") {
                this.setState({
                    photos: Object.values(photosJsonResponse.response),
                    isDataLoaded: true,
                });
            }
        }
    }

    showPhotoInspector(index) {
        this.setState({ photoInspectorActive: true, inspectedPhoto: index });
    }

    hidePhotoInspector() {
        this.setState({ photoInspectorActive: false });
    }

    prevPhoto() {
        const { inspectedPhoto } = this.state;
        const prevIndex = inspectedPhoto - 1;
        if (prevIndex >= 0) {
            this.showPhotoInspector(prevIndex);
        }
    }

    nextPhoto() {
        const { inspectedPhoto, photos } = this.state;
        const nextIndex = inspectedPhoto + 1;
        if (nextIndex < photos.length) {
            this.showPhotoInspector(nextIndex);
        }
    }

    render() {
        const loadingScreen = (
            <main id="loadingScreen">
                <div>
                    <h1> Loading </h1>
                    <div className="loader"></div>
                </div>
            </main>
        );

        let readyToGoWebpage = null;
        if (this.state.isDataLoaded && this.state.photos) {
            const handlePhotoClick = (index) => {
                this.showPhotoInspector(index);
            };

            const photos = this.state.photos.map((photo, index) => {
                return (
                    <div
                        key={index}
                        id={index}
                        className="photo"
                        style={{ backgroundImage: `url(uploads/photos/${photo.fileName})` }}
                        onClick={ () => handlePhotoClick(index)}
                    />
                );
            });

            readyToGoWebpage = (
                <div>
                    <Header logo={this.state.settings.headerLogo} />
                    <main>
                        <div id="main-content">
                            <Caption header={this.state.settings.galleryHeader} description={this.state.settings.galleryDescription} />
                            <div className="photos">{photos}</div>
                        </div>
                    </main>
                    <Footer
                        companyEmailAddress={this.state.settings.companyEmailAddress}
                        companyPhoneNumber={this.state.settings.companyPhoneNumber}
                        companyAddress={this.state.settings.companyAddress}
                    />
                </div>
            );
        }

        if (this.state.photoInspectorActive)
            document.body.style.overflow = "hidden";
        else document.body.style.overflow = "visible";

        return (
            <div>
                {(this.state.photoInspectorActive && null != this.state.inspectedPhoto ) && (
                    <div id="photo-inspector">
                        <p className="close">
                            <button className="close-button" onClick={this.hidePhotoInspector} >{"X"}</button>
                        </p>
                        <p className="photo-inspector">
                            <p>
                                <button
                                className="photo-inspector-prev-button"
                                onClick={this.prevPhoto}
                                style={{ visibility: (this.state.inspectedPhoto > 0 )?"visible":"hidden" }}
                                >{"<"}</button>
                            </p>
                            <p>
                                <img className="photo-inspector-image" src={`uploads/photos/${this.state.photos[this.state.inspectedPhoto].fileName}`} />
                            </p>
                            <p>
                                <button
                                className="photo-inspector-next-button"
                                onClick={this.nextPhoto}
                                style={{ visibility: (this.state.inspectedPhoto < this.state.photos.length - 1 )?"visible":"hidden" }}
                                >{">"}</button>
                            </p>
                        </p>
                        </div>
                )}
                {!this.state.isDataLoaded && loadingScreen}
                {readyToGoWebpage && readyToGoWebpage}

                <Helmet key={this.state.helmetKey}>
                    <script>{this.state.scripts}</script>
                    <style>{this.state.stylesheets}</style>
                    <style>{this.state.colors}</style>
                </Helmet>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("root"));

root.render(<Gallery />);
