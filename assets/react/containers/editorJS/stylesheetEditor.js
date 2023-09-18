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
        const editor = new EditorJS({
            holder: EDITOR_HOLDER_ID,
            logLevel: "ERROR",
            data: editorData,
            onReady: () => {
                ejInstance.current = editor;
            },
            onChange: async () => {
                let content = await this.editorjs.saver.save();
                // Put your logic here to save this data to your DB
                setEditorData(content);
                console.log("test")
            },
            autofocus: true,
        });
    };

    return (
        <React.Fragment>
            <div id={EDITOR_HOLDER_ID}> </div>
        </React.Fragment>
    );
}

export default StylesheetsEditor;