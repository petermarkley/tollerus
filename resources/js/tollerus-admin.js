import Alpine from 'alpinejs';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';

function registerAdminComponents(A) {
    A.data('tollerusWysiwyg', (opts = {}) => ({
        editor: null,
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
            this.editor = new Editor({
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
                    }, this.debounceMs);
                },
            });
            this.$watch('state', (html) => {
                if (!this.editor) return;
                if (this.syncingFromEditor) return;
                const next = html ?? '';
                // Avoid resetting selection/history if content didn't change
                if (next === this.editor.getHTML()) return;
                this.syncingFromLivewire = true;
                this.editor.commands.setContent(next, false);
                this.syncingFromLivewire = false;
            });
        },
        destroy() {
            if (this.editor) {
                this.editor.destroy();
                this.editor = null;
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
