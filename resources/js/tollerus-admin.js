import Alpine from 'alpinejs';
import { Editor, Mark, mergeAttributes } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';

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
 * <a href="/tollerus?id=AAR3" data-tollerus="word" data-id="AAR3" data-lang="myconlang">
 */
const TollerusWord = Mark.create({
    name: 'tollerusWord',
    inclusive: false,

    addAttributes() {
        return {
            'data-id': { default: null },
            'data-lang': { default: null },
            href: { default: null },
        };
    },

    parseHTML() {
        return [
            { tag: 'a[data-tollerus="word"]' },
            { tag: 'span[data-tollerus="word"]' },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'a',
            mergeAttributes(HTMLAttributes, {
                'data-tollerus': 'word',
            }),
            0,
        ];
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

/**
 * Modify Tiptap's `link` extension to play nice with Tollerus conlang words
 */
const TollerusLink = Link.extend({
    parseHTML() {
        return [
            {
                tag: 'a[href]:not([data-tollerus="word"])',
            },
        ];
    },
});

function registerAdminComponents(A) {
    A.data('tollerusWysiwyg', (opts = {}) => {
        let editor = null;
        return {
            state: opts.state,
            debounceMs: opts.debounceMs ?? 250,
            _t: null,
            syncingFromEditor: false,
            syncingFromLivewire: false,
            rawMode: false,
            toolbarHighlights: {
                bold: false,
                italic: false,
                smallcaps: false,
                phonemic: false,
                link: false,
                tollerusWord: false,
            },
            getEditor() {
                return editor;
            },
            init() {
                const mountEl = this.$el.querySelector('[data-tollerus-wysiwyg-mount]');
                if (!mountEl) {
                    throw new Error('[Tollerus] WYSIWYG mount element not found ([data-tollerus-wysiwyg-mount]).');
                }
                editor = new Editor({
                    element: mountEl,
                    extensions: [
                        StarterKit.configure({
                            heading: false,
                            blockquote: false,
                            codeBlock: false,
                            code: false,
                            hardBreak: false,
                            underline: false,
                            link: false,
                        }),
                        TollerusSmallcaps,
                        TollerusLink,
                        TollerusWord,
                        TollerusPhonemic,
                        TollerusNative,
                    ],
                    content: this.state ?? '',
                    onSelectionUpdate: () => {
                        this.refreshToolbar();
                    },
                    onUpdate: ({ editor: ed }) => {
                        if (this.syncingFromLivewire) return;
                        const html = ed.getHTML();
                        clearTimeout(this._t);
                        this._t = setTimeout(() => {
                            this.syncingFromEditor = true;
                            this.state = html;
                            this.syncingFromEditor = false;
                            this.$dispatch('tollerus-wysiwyg-input');
                        }, this.debounceMs);
                        this.refreshToolbar();
                    },
                    onCreate: () => {
                        this.refreshToolbar();
                    },
                });
                this.$watch('state', (html) => {
                    if (!editor) return;
                    if (this.syncingFromEditor) return;
                    const next = html ?? '';
                    if (next === editor.getHTML()) return;
                    this.syncingFromLivewire = true;
                    clearTimeout(this._t);
                    queueMicrotask(() => {
                        if (!editor) return;
                        editor.commands.setContent(next, false);
                        this.syncingFromLivewire = false;
                    });
                });
            },
            destroy() {
                if (editor) {
                    editor.destroy();
                    editor = null;
                }
            },
            refreshToolbar() {
                if (!editor) return;
                this.toolbarHighlights.bold = editor.isActive('bold');
                this.toolbarHighlights.italic = editor.isActive('italic');
                this.toolbarHighlights.smallcaps = editor.isActive('tollerusSmallcaps');
                this.toolbarHighlights.phonemic = editor.isActive('tollerusPhonemic');
                this.toolbarHighlights.link = editor.isActive('link');
                this.toolbarHighlights.tollerusWord = editor.isActive('tollerusWord');
            },
            isActive(name) {
                return !!this.toolbarHighlights[name];
            },
            handleToolbar(action) {
                if (!editor || this.rawMode) return;
                switch (action) {
                    case 'bold':
                        editor.chain().focus().toggleBold().run();
                    break;
                    case 'italic':
                        editor.chain().focus().toggleItalic().run();
                    break;
                    case 'smallcaps':
                        editor.chain().focus().toggleMark('tollerusSmallcaps').run();
                    break;
                    case 'phonemic':
                        editor.chain().focus().toggleMark('tollerusPhonemic').run();
                    break;
                    default:
                        if (import.meta?.env?.DEV) console.warn('[Tollerus] Unknown toolbar action:', action);
                    break;
                }
            },
        };
    });
}

if (!window.Alpine) {
    window.Alpine = Alpine;
}

document.addEventListener('alpine:init', () => {
    registerAdminComponents(window.Alpine);
});
