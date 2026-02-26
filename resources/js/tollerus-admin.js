import Alpine from 'alpinejs';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';

function registerAdminComponents(A) {
    A.data('tollerusWysiwyg', (opts = {}) => ({
        state: opts.state,
        debounceMs: opts.debounceMs ?? 250,
        _t: null,
        syncingFromEditor: false,
        syncingFromLivewire: false,
        init() {
            const mountEl = this.$el.querySelector('[data-tollerus-wysiwyg-mount]');
            if (!mountEl) {
                throw new Error('[Tollerus] WYSIWYG mount element not found ([data-tollerus-wysiwyg-mount]).');
            }
            this.$el._tollerusEditor = new Editor({
                element: mountEl,
                extensions: [StarterKit],
                content: this.state ?? '',
                onUpdate: ({ editor }) => {
                    if (this.syncingFromLivewire) return;
                    const html = editor.getHTML();
                    clearTimeout(this._t);
                    this._t = setTimeout(() => {
                        this.syncingFromEditor = true;
                        this.state = html;
                        this.syncingFromEditor = false;
                        this.$dispatch('tollerus-wysiwyg-input');
                    }, this.debounceMs);
                },
            });
            this.$watch('state', (html) => {
                const editor = this.$el._tollerusEditor;
                if (!editor) return;
                if (this.syncingFromEditor) return;
                const next = html ?? '';
                if (next === editor.getHTML()) return;
                this.syncingFromLivewire = true;
                editor.commands.setContent(next, false);
                this.syncingFromLivewire = false;
            });
        },
        destroy() {
            const editor = this.$el._tollerusEditor;
            if (editor) {
                editor.destroy();
                delete this.$el._tollerusEditor;
            }
        },
    }));
}

if (!window.Alpine) {
    window.Alpine = Alpine;
}

document.addEventListener('alpine:init', () => {
    registerAdminComponents(window.Alpine);
});
