import React, {Component} from "react";
import ImageListItem from "../../../components/gallery/imageListItem";
import PageControls from "../../../components/gallery/pageControls";
import {json} from "react-router-dom";

class GalleryListItem extends Component {

    constructor(props) {
        super(props);
        this.state = {
            photos: [],
            currentPage: 0,
            totalItems: 0,
            pagesCount: 1,
            quantity: 5,
        }
        this.nextPage = this.nextPage.bind(this);
        this.prevPage = this.prevPage.bind(this);
        this.changeQuantityPerPage = this.changeQuantityPerPage.bind(this);
        this.changePage = this.changePage.bind(this);
        this.deleteItem = this.deleteItem.bind(this);
    }

    componentDidMount() {
        this.nextPage();
    }

    nextPage(){
        let nextPage = this.state.currentPage + 1;
        if ( nextPage <= this.state.pagesCount)
        {
            this.setState({ currentPage: nextPage});
            this.getPhotos(nextPage, this.state.quantity);
        }
    }

    prevPage(){
        let prevPage = this.state.currentPage - 1;
        if ( prevPage > 0 )
        {
            this.setState({ currentPage: prevPage});
            this.getPhotos(prevPage, this.state.quantity);
        }
    }

    changeQuantityPerPage(quantity)
    {
        if ( isNaN(quantity) || quantity <= 0 ) {
            this.setState({quantity: 1});
            this.getPhotos(this.state.currentPage, 1);
        } else if (quantity > this.state.totalItems )
        {
            this.setState({quantity: this.state.totalItems});
            this.getPhotos(this.state.currentPage, this.state.totalItems);
        } else if ( quantity > 50 ) {
            this.setState({quantity: 50 });
            this.getPhotos(this.state.currentPage, 50);
        } else {
            this.setState({quantity: quantity});
            this.getPhotos(this.state.currentPage, quantity);
        }
    }

    changePage(page){
        if ( isNaN(page) || page <= 0 )
        {
            this.setState({currentPage: 1});
            this.getPhotos(1, this.state.quantity);

        } else if ( page > this.state.pagesCount )
        {
            this.setState({currentPage: this.state.pagesCount});
            this.getPhotos(this.state.pagesCount, this.state.quantity);
        } else {
            this.setState({currentPage: page});
            this.getPhotos(page, this.state.quantity);
            document.getElementById("page-number").value = page;
        }
    }

    async getPhotos(requestedPage, requestedQuantity)
    {
        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/gallery/get-photos?` + new URLSearchParams({
            page: requestedPage,
            quantity: requestedQuantity,
        });

        try {
            const response = await fetch(fetchAddress);
            const jsonResponse = await response.json();

            if ( jsonResponse['status'] === "success")
            {
                let photosList = jsonResponse["response"]["items"];
                let currentPage = jsonResponse["response"]["currentPage"];
                let pagesCount = jsonResponse["response"]["pagesCount"];
                let totalItems = jsonResponse["response"]["totalItems"];

                this.setState({
                    photos: photosList,
                    currentPage: currentPage,
                    totalItems: totalItems,
                    pagesCount: pagesCount,
                });
            } else {
            }
        } catch (error) {
        }
    }

    async deleteItem(id, index)
    {
        let currentPage = this.state.currentPage;
        let currentPhotosPerPage = this.state.quantity;
        console.log(currentPage, currentPhotosPerPage);

        const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/gallery/delete-photo?` + new URLSearchParams({
            id: id
        });
        try {
            const response = await fetch(fetchAddress)
                .then((response) => response.json())
                .then((responseJson) => {
                    this.getPhotos(currentPage, currentPhotosPerPage);
                })
        } catch (error) {
        }
    }

    render(){

        // tworzenie tabeli z zdjęciami
        let photos = this.state.photos.map((photo, index) => {
            let photoId = Object.keys(photo);
            let photoObject = Object.values(photo)[0];
            const dateObject = new Date(photoObject["addedDatetime"]["date"]);

            return <ImageListItem key={photoId} index={index} dateAdded={ dateObject.toLocaleString() } deleteItem={this.deleteItem} id={photoId} name={photoObject["name"]} fileType={photoObject["fileType"]} addedBy={photoObject["addedBy"]} safeFileName={photoObject["safeFileName"]} />
        });

        let photosTable = null;
        let pageControls = null

        if ( this.state.totalItems > 0 )
        {
            photosTable = <table className="image-list-table">
                <thead className="image-list-table-thead"><tr>
                    <th>Id</th>
                    <th>Nazwa</th>
                    <th>Zdjęcie</th>
                    <th>Dodane przez</th>
                    <th>Typ pliku</th>
                    <th>Data dodania</th>
                    <th>Usuń</th>
                </tr></thead>
                <tbody className="image-list-table-tbody">
                {photos}
                </tbody>
            </table>;
            pageControls = <PageControls pagesCount={this.state.pagesCount} changePage={this.changePage} currentPage={this.state.currentPage} nextPage={this.nextPage} prevPage={this.prevPage} elementsPerPage={this.state.quantity} changeQuantityPerPage={this.changeQuantityPerPage} />
        }

        return (
            <div className="image-list">
                {photosTable}
                {pageControls}
                </div>
        );
    }
}

export default GalleryListItem;