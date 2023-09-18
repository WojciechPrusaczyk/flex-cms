import { default as React, useEffect, useRef } from 'react';
import EditorJS from '@editorjs/editorjs';

const DEFAULT_INITIAL_DATA = () => {
    return {
        "time": new Date().getTime()
    }
}

const EDITOR_HOLDER_ID = 'editorjs';

const StylesheetsEditor = (props) => {
    const ejInstance = useRef();
    const [editorData, setEditorData] = React.useState(DEFAULT_INITIAL_DATA);


    // This will run only once
    useEffect(() => {
        if (!ejInstance.current) {
            initEditor();
        }
        return () => {
            ejInstance.current.destroy();
            ejInstance.current = null;
        }
    }, []);

    const initEditor = () => {
        let editor = new EditorJS({
            holder: EDITOR_HOLDER_ID,
            logLevel: "ERROR",
            data: props.defaultData,
            onReady: async () => {
                ejInstance.current = editor;
            },
            onChange: async () => {
                let content = await editor.saver.save();
                // Put your logic here to save this data to your DB
                setEditorData(content);
                handleChange(content);
            },
            autofocus: true,
        });
    };

    // method to extract data from component
    const handleChange = (data) => {
        props.onDataChange(data);
    };

    return (
        <React.Fragment>
            <div id={EDITOR_HOLDER_ID} className={props.className}> </div>
        </React.Fragment>
    );
}

export default StylesheetsEditor;