import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import "../../editorJS/stylesheetEditor"

class StylesheetsFormNew extends Component
{
    constructor(props) {
        super(props);
        this.state = {
        }
    }

    render() {
        return (
            <div>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<StylesheetsFormNew />);