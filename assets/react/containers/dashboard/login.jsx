import React, {Component} from "react";
import ReactDOM from "react-dom/client";

class LoginForm extends Component{
    constructor(props) {
        super(props);
        this.state = {
            username: "",
            password: ""
        }
    }

    submitHandler()
    {
        const username = document.getElementById("form-username").value;
        const password = document.getElementById("form-password").value;

        this.setState({
            username: username,
            password: password
        }, () =>
            this.loginUser(username, password)
        );
    }

    async loginUser(username, password)
    {
        console.log(username, password);

        const fetchAddress = `${location.protocol}//${window.location.host}/api/login?` + new URLSearchParams({
            username: username,
            password: password,
        });
        console.log(fetchAddress)

        const response = await fetch(fetchAddress);
        const jsonResponse = await response.json();

        if (jsonResponse['status'] == "success")
        {
            window.location.href = `${location.protocol}//${window.location.host}/dashboard`;
        }
    }

    render() {
        return (
            <form id="form" className="login-form">
                <label htmlFor="form-username">Nazwa użytkownika</label>
                <input id="form-username" className="login-form-text" type="text"/>

                <label htmlFor="form-password">Hasło</label>
                <input id="form-password" className="login-form-text" type="password"/>

                <input id="form-submit" className="login-form-submit" type="button" value="Zaloguj" onClick={ () => this.submitHandler()}/>
            </form>
        );
    }

}

const root = ReactDOM.createRoot(document.getElementById("form-root"));

root.render(<LoginForm />);