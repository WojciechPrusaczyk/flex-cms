import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import ImageAddForm from "./ImageAddForm";
import GalleryListItem from "./GalleryListItem";

class GalleryMain extends Component
{
    constructor(props) {
        super(props);
        this.state = {

        }
    }



    render() {
        return (
            <div>
                <ImageAddForm />
                <GalleryListItem />
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<GalleryMain />);