import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import ImageAddForm from "./ImageAddForm";
import GalleryListItem from "./GalleryListItem";
import {Tooltip} from "../../../components/Tooltip";

class GalleryMain extends Component
{
    constructor(props) {
        super(props);
        this.state = {

        }
    }



    render() {
        return (
            <div className="main">
                <div id="tooltip-root">
                    <Tooltip
                        text="Ten moduł służy do obsługi zdjęć zawartych w galerii zdjęć dostępnej stronie internetowej."
                    />
                </div>
                <ImageAddForm />
                <GalleryListItem />
            </div>
        );
    }
}

const root = ReactDOM.createRoot(document.getElementById("main-root"));

root.render(<GalleryMain />);