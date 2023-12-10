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
        const body = {
            username: username,
            password: password,
        }
        const fetchAddress = `${location.protocol}//${window.location.host}/api/login`;

        try {
            const response = await fetch( fetchAddress, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(body),
            });
            const jsonResponse = await response.json();

            if ( jsonResponse['status'] === "success")
            {
                window.location.href = `${location.protocol}//${window.location.host}/dashboard`;
            } else {
                await this.showError();
            }
        } catch (error) {
            await this.showError();
        }
    }
    delay(time) {
        return new Promise(function(resolve){ setTimeout(resolve, time); });
    }

    async showError()
    {
        // short animation of shaking and after a while hiding back elements
        document.getElementById("form-submit").classList.add("error");

        await this.delay(300);
        document.getElementById("form-submit").classList.remove("error");
        document.getElementById("form-submit").removeAttribute('disabled');
        document.getElementById("loader").style.visibility = "hidden";
    }

    render() {
        return (
            <form id="form" className="login-form">
                <p>
                    <label htmlFor="form-username">Nazwa użytkownika</label>
                    <input id="form-username" className="login-form-text" type="text"/>
                </p>
                <p>
                    <label htmlFor="form-password">Hasło</label>
                    <input id="form-password" className="login-form-text" type="password"/>
                </p>
                <p>
                    <input id="form-submit" className="login-form-submit" type="button" value="Zaloguj" onClick={ () => {
                        this.submitHandler();

                        // showing loading and disabling further calling form api
                        document.getElementById("loader").style.visibility = "initial";
                        document.getElementById("form-submit").setAttribute('disabled', 'true');
                    }}/>
                </p>
                <p>
                    <div id="loader" className="loader"></div>
                </p>

            </form>
        );
    }

}

const root = ReactDOM.createRoot(document.getElementById("form-root"));

root.render(<LoginForm />);

export default LoginForm;