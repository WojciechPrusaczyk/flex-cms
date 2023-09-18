import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import StylesheetsEditor from "../../editorJS/stylesheetEditor";

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
                <StylesheetsEditor></StylesheetsEditor>
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<StylesheetsFormNew />);