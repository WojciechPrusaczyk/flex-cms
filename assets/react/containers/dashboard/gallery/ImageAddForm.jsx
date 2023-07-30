import React, {Component} from "react";
import Tile from "../../../components/tile";
import AddedFile from "../../../components/gallery/addedFile";

class ImageAddForm extends Component{

	constructor(props) {
		super(props);
		this.state = {
			photos: [],
		}
	}

	deleteHandler(index){
		let currentPhotos = this.state.photos;

		currentPhotos.splice(index, 1)
		this.setState({photos: currentPhotos});
	}

	async dropHandler (event) {
		event.preventDefault();
		const errorTooLarge = document.getElementsByClassName("gallery-form-add-error-tooLarge")[0];
		errorTooLarge.style.display = "none"

		if (event.dataTransfer.items) {
			[...event.dataTransfer.items].forEach((item, i) => {
				// odrzucamy obiekty niebędące plikami
				if (item.kind === "file" ) {
					const file = item.getAsFile();

					if (file.size <= 5000000) {
						// plik jest odpowedni

						let reader = new FileReader();
						reader.readAsDataURL(file);

						reader.onload = () => {
							this.addFile(file, reader.result);
						};
					}
					else {
						// wyświetlenie informacji, że plik jest za duży
						errorTooLarge.style.display = "block";
					}
				}
			});
		}
	}

	async addFile(file, src)
	{
		let currentPhotos = this.state.photos;
		file['src'] = src;
		currentPhotos.push(file);

		this.setState({photos: currentPhotos});
	}

	dragOverHandler (event) {
		console.log("Plik znajduje się nad strefą dodawania");
		event.preventDefault();
	}

	render() {

		let addedPhotos = this.state.photos.map((photo, index) => {
			console.log(photo);
			return <AddedFile key={index} src={photo.src} name={photo.name} deleteHandler={ () => this.deleteHandler(index)}></AddedFile>
		});

		let button = (this.state.photos.length > 0 )?<button className="gallery-added-photos-button">Dodaj</button>:null;

		return (
			<div className="gallery">
				<div className="gallery-form" onDrop={ () => this.dropHandler(event)} onDragOver={this.dragOverHandler}>
					<img className="gallery-form-add-icon" src="/build/icons/add.svg" alt="ikona dodawania zdjęcia"/>
					<span className="gallery-form-add-note">Przeciągnij i upuść zdjęcia do dodania. (max 5MB)</span>
					<span className="gallery-form-add-error-tooLarge">Jeden z dodanych plików jest za duży!</span>
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