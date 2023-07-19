import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import userLogo from '../../../icons/user.svg'

class Dashboard extends Component
{
    constructor(props) {
        super(props);
        this.state = {
            username: "Loading",
        }
        this.updateUsername();
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



    render() {
        return (
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
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("header-root"));

root.render(<Dashboard />);