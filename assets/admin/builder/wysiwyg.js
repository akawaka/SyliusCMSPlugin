import suneditor from 'suneditor';
import { align, blockquote, blockStyle, fontColor, hr, link, list_bulleted, list_numbered, table } from 'suneditor/plugins';
import fr from 'suneditor/langs/fr';

import 'suneditor/css/editor';
import 'suneditor/css/contents';

const LANGUAGES = { fr };

const BUTTON_LIST = [
    ['undo', 'redo'],
    ['blockStyle'],
    ['bold', 'underline', 'italic', 'strike'],
    ['fontColor'],
    ['align', 'list_bulleted', 'list_numbered', 'blockquote'],
    ['link', 'table', 'hr'],
    ['removeFormat', 'codeView'],
];

/**
 * Turns a textarea into a SunEditor WYSIWYG. `onChange` receives the HTML on every change.
 * Returns the editor instance (call `destroy()` to remove it).
 */
export function createWysiwyg(textarea, onChange) {
    const language = (document.documentElement.lang || 'en').slice(0, 2).toLowerCase();

    return suneditor.create(textarea, {
        plugins: [align, blockquote, blockStyle, fontColor, hr, link, list_bulleted, list_numbered, table],
        buttonList: BUTTON_LIST,
        lang: LANGUAGES[language],
        width: '100%',
        minHeight: '14rem',
        height: 'auto',
        statusbar: false,
        events: {
            onChange: ({ data }) => onChange(data),
        },
    });
}
