import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import userLogo from '../../../icons/dashboard/user.svg'

class Dashboard extends Component
{
    constructor(props) {
        super(props);
        this.state = {
            username: "Loading",
            settingsPaths: [],
            currentPath: null,
        }
        this.updateUsername();
        this.getSettingsPaths();
    }
    async updateUsername()
    {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/get-user`;

        try {
            const response = await fetch(fetchAddress);
            const jsonResponse = await response.json();

            if ( jsonResponse['status'] === "success")
            {
                const username = jsonResponse['response']['user']['username'];

                this.setState({username: username.charAt(0).toUpperCase() + username.slice(1)});
            }
        } catch (error) {
        }
    }

    async getSettingsPaths()
    {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/get-dashboard-settings`;

        try {
            const response = await fetch(fetchAddress)
                .then((response) => response.json())
                .then((jsonResponse) => {

                    if ( jsonResponse['status'] === "success")
                    {
                        this.setState({settingsPaths: jsonResponse["response"][0]});

                        this.findCurrentPath(jsonResponse["response"][0]);
                    }
                })
        } catch (error) {
        }
    }

    findCurrentPath(jsonResponse)
    {
        let currentAbsolutePath = window.location.href;

        jsonResponse.forEach( (setting) => {
            if (currentAbsolutePath.includes("/dashboard/"+setting.href))
            {
                this.setState({ currentPath: setting });
            }
        } );
    }



    render() {
        let currentPath = null;

        if ( null != this.state.currentPath && window.location.href.includes("/edit")){
            currentPath = <p>
                <a className="breadcrumbs-path" onClick={ () => { window.location.href = `${location.protocol}//${window.location.host}/dashboard`; } } alt="przejdź do dashboard">dashboard</a>
                <span className="breadcrumbs-separator">/</span>
                <a className="breadcrumbs-path" onClick={ () => { window.location.href = `${location.protocol}//${window.location.host}/dashboard/${this.state.currentPath.href}` } } alt={`znajdujesz się właśnie w ${this.state.currentPath.name}`}>{this.state.currentPath.name}</a>
                <span className="breadcrumbs-separator">/</span>
                <span className="breadcrumbs-path" alt={`znajdujesz się właśnie w ${this.state.currentPath.name}`}>Edycja</span>
            </p>;
        }
        else if ( null != this.state.currentPath )
        {
            currentPath = <p>
                <a className="breadcrumbs-path" onClick={ () => { window.location.href = `${location.protocol}//${window.location.host}/dashboard`; } } alt="przejdź do dashboard">dashboard</a>
                <span className="breadcrumbs-separator">/</span>
                <a className="breadcrumbs-path" onClick={ () => { window.location.href = `${location.protocol}//${window.location.host}/dashboard/${this.state.currentPath.href}` } } alt={`znajdujesz się właśnie w ${this.state.currentPath.name}`}>{this.state.currentPath.name}</a>
            </p>;
        }
        else {
            currentPath = <p>
                <a className="breadcrumbs-path breadcrumbs-currentPath" onClick={ () => { window.location.href = `${location.protocol}//${window.location.host}/dashboard`; } } alt="przejdź do dashboard">dashboard</a>
            </p>;
        }

        return (
            <div>
                <div className="header">
                    <h1 className="header-title" onClick={ () => { window.location.href = `${location.protocol}//${window.location.host}/dashboard`; } }>Dashboard</h1>
                    <div className="header-user">
                        <img className="header-user-logo" src={userLogo} alt="*" />
                        <span className="header-user-name">{this.state.username}</span>
                    </div>
                    <div className="header-logout">
                        <button className="header-logout-button" onClick={ () => { window.location.href = `${location.protocol}//${window.location.host}/admin-api/logout`; } }>Wyloguj</button>
                    </div>
                </div>
                <div className="breadcrumbs">
                    {currentPath}
                </div>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("header-root"));

root.render(<Dashboard />);