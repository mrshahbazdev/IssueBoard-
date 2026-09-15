import './bootstrap';
import Sortable from 'sortablejs';

window.Sortable = Sortable;

window.issueboardRichTextEditor = (model, linkPrompt) => ({
    value: model ?? '',
    linkPrompt,

    init() {
        this.$refs.editor.innerHTML = this.value || '';

        this.$watch('value', (value) => {
            const nextValue = value || '';

            if (this.$refs.editor.innerHTML !== nextValue) {
                this.$refs.editor.innerHTML = nextValue;
            }
        });
    },

    focus() {
        this.$refs.editor.focus();
    },

    format(command, value = null) {
        this.focus();
        document.execCommand(command, false, value);
        this.syncFromEditor();
    },

    createLink() {
        const selection = window.getSelection()?.toString().trim();

        if (! selection) {
            this.focus();

            return;
        }

        const requestedUrl = window.prompt(this.linkPrompt);

        if (! requestedUrl) {
            return;
        }

        const url = /^[a-z][a-z0-9+.-]*:/i.test(requestedUrl)
            ? requestedUrl
            : `https://${requestedUrl}`;

        this.format('createLink', url);
    },

    clearFormatting() {
        this.format('removeFormat');
        document.execCommand('unlink');
        this.syncFromEditor();
    },

    pastePlainText(event) {
        const text = event.clipboardData?.getData('text/plain') ?? '';
        const safeText = text
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;')
            .replace(/\r?\n/g, '<br>');

        document.execCommand('insertHTML', false, safeText);
        this.syncFromEditor();
    },

    syncFromEditor() {
        this.value = this.$refs.editor.innerHTML;
    },
});
