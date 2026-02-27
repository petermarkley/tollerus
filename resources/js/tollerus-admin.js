import Alpine from 'alpinejs';
import { Editor, Mark, mergeAttributes } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Bold from '@tiptap/extension-bold';
import Italic from '@tiptap/extension-italic';

/**
 * <span data-tollerus="smallcaps">
 */
const TollerusSmallcaps = Mark.create({
    name: 'tollerusSmallcaps',
    inclusive: true,
    excludes: 'tollerusWord tollerusPhonemic',

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
    excludes: '_',

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
    excludes: '_',

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
    excludes: 'tollerusWord tollerusPhonemic',

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
    excludes: 'tollerusWord tollerusPhonemic',
    parseHTML() {
        return [
            {
                tag: 'a[href]:not([data-tollerus="word"])',
            },
        ];
    },
});

/**
 * Modify Tiptap's bold and italic extensions to say they can't have a conlang word inside them
 */
const TollerusBold = Bold.extend({
    excludes: 'tollerusWord tollerusPhonemic',
});
const TollerusItalic = Italic.extend({
    excludes: 'tollerusWord tollerusPhonemic',
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
                link: false,
                tollerusSmallcaps: false,
                tollerusPhonemic: false,
                tollerusWord: false,
                tollerusNative: false,
            },
            toolbarExcludes: {
                bold: false,
                italic: false,
                link: false,
                tollerusSmallcaps: false,
                tollerusPhonemic: false,
                tollerusWord: false,
                tollerusNative: false,
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
                            bold: false,
                            italic: false,
                        }),
                        TollerusBold,
                        TollerusItalic,
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
                this.toolbarHighlights.bold      = editor.isActive('bold');
                this.toolbarHighlights.italic    = editor.isActive('italic');
                this.toolbarHighlights.link      = editor.isActive('link');
                this.toolbarHighlights.tollerusSmallcaps = editor.isActive('tollerusSmallcaps');
                this.toolbarHighlights.tollerusPhonemic  = editor.isActive('tollerusPhonemic');
                this.toolbarHighlights.tollerusWord      = editor.isActive('tollerusWord');
                this.toolbarHighlights.tollerusNative    = editor.isActive('tollerusNative');
                this.toolbarExcludes.bold      = this.calculateExcluded('bold');
                this.toolbarExcludes.italic    = this.calculateExcluded('italic');
                this.toolbarExcludes.link      = this.calculateExcluded('link');
                this.toolbarExcludes.tollerusSmallcaps = this.calculateExcluded('tollerusSmallcaps');
                this.toolbarExcludes.tollerusPhonemic  = this.calculateExcluded('tollerusPhonemic');
                this.toolbarExcludes.tollerusWord      = this.calculateExcluded('tollerusWord');
                this.toolbarExcludes.tollerusNative    = this.calculateExcluded('tollerusNative');
            },
            isActive(name) {
                return !!this.toolbarHighlights[name];
            },
            isExcluded(name) {
                return !!this.toolbarExcludes[name];
            },
            calculateExcluded(markName) {
                if (!editor) return false;
                const activeMarks = editor.state.selection.$from.marks();
                return activeMarks.some(mark => {
                    const excludes = mark.type.spec.excludes;
                    if (!excludes) return false;
                    if (excludes === '_') return mark.type.name !== markName;
                    return excludes.split(' ').includes(markName);
                });
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
