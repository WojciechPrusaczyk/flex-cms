import React, {useRef} from "react";
import SectionsEditor from "../../containers/editorJS/sectionsEditor";
const Form = (props) => {
    const formRef = useRef(null);

    // Pamiętaj, żeby użyć useState, aby przechowywać dane w stanie komponentu
    const [value, setValue] = React.useState(null);

    // Method to update the "value" state with new data
    const handleDataChange = (newData) => {
        setValue(newData);
    };

    const handleChange = () => {
        const form = formRef.current;
        let formData = {
            name: form.name.value,
            active: form.active.checked,
            isWide: form.wide.checked,
            start_being_active: form.startBeingActive.value,
            stop_being_active: form.stopBeingActive.value,
            isTitleVisible: form.isTitleVisible.checked,
        }
        props.dataHandler(formData);
    };

    return <form
            id="form"
            className="editor-form"
            ref={formRef}
            onSubmit={ (e) => { e.preventDefault(); props.submitHandler(); } }>
        <p>
            <label htmlFor="form-name">Nazwa</label>
            <input
                id="form-name"
                name="name"
                type="text"
                onChange={handleChange}
                defaultValue={props.name}
            />
        </p>
        <p>
            <label htmlFor="form-active">Aktywny</label>
            <input
                id="form-active"
                name="active"
                type="checkbox"
                onChange={handleChange}
                defaultChecked={props.active}
            />
        </p>
        <p>
            <label htmlFor="form-wide">Szeroka sekcja</label>
            <input
                id="form-wide"
                name="wide"
                type="checkbox"
                onChange={handleChange}
                defaultChecked={props.isWide}
            />
        </p>
        <p>
            <span className="warning">Szeroka sekcja będzie zajmowała całą szerokość. Jeśli natomiast po sobie nastąpią 2 sekcje które nie są szerokie, ułożą się one obok siebie.</span>
        </p>
        <p>
            <label htmlFor="form-start-being-active">Aktywny od</label>
            <input
                id="form-start-being-active"
                name="startBeingActive"
                type="datetime-local"
                onChange={handleChange}
                defaultValue={props.start_being_active}
            />
        </p>
        <p>
            <label htmlFor="form-stop-being-active">Aktywny do</label>
            <input
                id="form-stop-being-active"
                name="stopBeingActive"
                type="datetime-local"
                onChange={handleChange}
                defaultValue={props.stop_being_active}
            />
        </p>

        <p>
            <label htmlFor="form-isTitleVisible">Czy tytuł jest widoczny?</label>
            <input
                id="form-isTitleVisible"
                name="isTitleVisible"
                type="checkbox"
                onChange={handleChange}
                defaultChecked={props.isTitleVisible}
            />
        </p>

        <p>
            <span className="information">Pamiętaj, że możesz wkleić odnośnik do zdjęcia z galerii, lub z poza aplikacji!</span>
        </p>

        <SectionsEditor
            className="editor-form-field"
            onDataChange={ props.handleDataChange }
            defaultData={props.defaultData}
        />

        <p>
            <input
                id="form-submit"
                className="editor-form-field-submit"
                type="submit"
                value="Zapisz"
            />
        </p>
    </form>
}

export default Form;