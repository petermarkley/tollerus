import Alpine from 'alpinejs';
import { Editor, Mark, mergeAttributes } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';

/**
 * <span data-tollerus="smallcaps">
 */
const TollerusSmallcaps = Mark.create({
    name: 'tollerusSmallcaps',
    inclusive: true,

    parseHTML() {
        return [{ tag: 'span[data-tollerus="smallcaps"]' }];
    },

    renderHTML({ HTMLAttributes }) {
        return ['span', mergeAttributes(HTMLAttributes, { 'data-tollerus': 'smallcaps' }), 0];
    },
});

/**
 * <span data-tollerus="phonemic">
 */
const TollerusPhonemic = Mark.create({
    name: 'tollerusPhonemic',
    inclusive: true,

    parseHTML() {
        return [{ tag: 'span[data-tollerus="phonemic"]' }];
    },

    renderHTML({ HTMLAttributes }) {
        return ['span', mergeAttributes(HTMLAttributes, { 'data-tollerus': 'phonemic' }), 0];
    },
});

/**
 * <span data-tollerus="native" data-neography="myneography" class="tollerus_custom_myneography">
 */
const TollerusNative = Mark.create({
    name: 'tollerusNative',
    inclusive: true,

    addAttributes() {
        return {
            'data-neography': { default: null },
            class: { default: null },
        };
    },

    parseHTML() {
        return [{ tag: 'span[data-tollerus="native"]' }];
    },

    renderHTML({ HTMLAttributes }) {
        return ['span', mergeAttributes(HTMLAttributes, { 'data-tollerus': 'native' }), 0];
    },
});

function registerAdminComponents(A) {
    A.data('tollerusWysiwyg', (opts = {}) => ({
        state: opts.state,
        debounceMs: opts.debounceMs ?? 250,
        _t: null,
        syncingFromEditor: false,
        syncingFromLivewire: false,
        rawMode: false,
        init() {
            const mountEl = this.$el.querySelector('[data-tollerus-wysiwyg-mount]');
            if (!mountEl) {
                throw new Error('[Tollerus] WYSIWYG mount element not found ([data-tollerus-wysiwyg-mount]).');
            }
            this.$el._tollerusEditor = new Editor({
                element: mountEl,
                extensions: [
                    StarterKit.configure({
                        heading: false,
                        blockquote: false,
                        codeBlock: false,
                        code: false,
                        hardBreak: false,
                        underline: false,
                    }),
                    TollerusSmallcaps,
                    TollerusPhonemic,
                    TollerusNative,
                ],
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
