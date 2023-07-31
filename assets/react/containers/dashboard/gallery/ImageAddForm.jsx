import React, {Component} from "react";
import AddedFile from "../../../components/gallery/addedFile";


let fileHandle;

class ImageAddForm extends Component{

	constructor(props) {
		super(props);
		this.state = {
			photos: [],
			note: "Przeciągnij i upuść zdjęcia do dodania. (max 5MB)",
			error: "",
			formImage: "/build/icons/dashboard/add.svg",
		}
	}

	deleteHandler(index){
		let currentPhotos = this.state.photos;

		currentPhotos.splice(index, 1)
		this.setState({photos: currentPhotos});
	}

	async dropHandler (event) {
		event.preventDefault();
		this.showError("");

		// dopuszczalne typy plików
		const validImageTypes = ['image/gif', 'image/jpeg', 'image/png', 'image/svg'];

		if (event.dataTransfer.items) {
			[...event.dataTransfer.items].forEach((item, i) => {
				// odrzucamy obiekty niebędące plikami
				if (item.kind === "file" ) {
					const file = item.getAsFile();

					if (file.size > 5000000) {
						this.showError("Plik przekracza limit 5MB.")
					} else if ( !validImageTypes.includes(file.type) ) {
						this.showError("Typ pliku jest nieodpowiedni. Dopuszczalne formaty to: jpg, svg,  png, gif")
					}
					else {
						// plik jest odpowedni

						let reader = new FileReader();
						reader.readAsDataURL(file);

						reader.onload = () => {
							this.addFile(file, reader.result);
						};
					}
				}
			});
		}
	}

	async addFile(file, src)
	{
		let currentPhotos = this.state.photos;
		currentPhotos.push({
			src: src,
			file: file
		});

		this.setState({photos: currentPhotos});
		this.dragLeaveHandler();
	}

	dragOverHandler (event) {
		event.preventDefault();
		this.setState({formImage: "/build/icons/dashboard/download.svg"});
	}
	dragLeaveHandler () {
		this.setState({formImage: "/build/icons/dashboard/add.svg"});
	}

	showError(errorString)
	{
		this.dragLeaveHandler();
		this.setState({error: errorString});
	}

	async uploadPhotosHandler()
	{
		const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/dashboard/gallery/upload-photo`;
		const uploaderUsername = await this.getUsername()

		this.state.photos.map((photo, index) => {

			const formData = new FormData();
			formData.append('file', photo.file);
			formData.append('username', uploaderUsername);
			formData.append('filename', photo.file.name);

			fetch(fetchAddress, {
				method: "POST",
				body: formData
			})
				.then(res => res.json())
				.then(data => {
						console.log(data);
					}
				);
		});
	}

	async getUsername()
	{
		const fetchAddress = `${location.protocol}//${window.location.host}/admin-api/get-user`;

		try {
			const response = await fetch(fetchAddress);
			const jsonResponse = await response.json();

			if ( jsonResponse['status'] === "success")
			{
				return jsonResponse['response']['user']['username'];
			}
		} catch (error) {
		}
	}

	render() {

		let addedPhotos = this.state.photos.map((photo, index) => {
			return <AddedFile key={index} src={photo.src} name={photo.file.name} deleteHandler={ () => this.deleteHandler(index)}></AddedFile>
		});

		let button = (this.state.photos.length > 0 )?<button className="gallery-added-photos-button" onClick={ () => this.uploadPhotosHandler()}>Dodaj</button>:null;

		return (
			<div className="gallery">
				<div className="gallery-form" onDrop={ () => this.dropHandler(event)} onDragOver={ () => this.dragOverHandler(event)} onDragLeave={ () => this.dragLeaveHandler() }>
					<img id="gallery-form-icon" className="gallery-form-icon" src={this.state.formImage} alt="ikona dodawania zdjęcia"/>
					<span className="gallery-form-note">{this.state.note}</span>
					<span id="gallery-form-errors" className="gallery-form-errors">{this.state.error}</span>
				</div>
				<div className="gallery-added-photos">
					{addedPhotos}
				</div>
				{button}
			</div>
		)
	}
}

export default ImageAddForm;