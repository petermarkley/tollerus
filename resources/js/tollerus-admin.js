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

/**
 * List of marks that participate in UI hinting logic (highlight/exclusion)
 */
const TOOLBAR_MARKS = [
    'bold',
    'italic',
    'link',
    'tollerusSmallcaps',
    'tollerusPhonemic',
    'tollerusWord',
    'tollerusNative',
];

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
                bullet_list: false,
                numbered_list: false,
            },
            toolbarExcludes: {
                bold: false,
                italic: false,
                link: false,
                tollerusSmallcaps: false,
                tollerusPhonemic: false,
                tollerusWord: false,
                tollerusNative: false,
                bullet_list: false,
                numbered_list: false,
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
                for (const name of TOOLBAR_MARKS) {
                    this.toolbarHighlights[name] = editor.isActive(name);
                    this.toolbarExcludes[name] = this.calculateExcluded(name);
                }
                this.toolbarHighlights.bullet_list = editor.isActive('bulletList');
                this.toolbarHighlights.numbered_list = editor.isActive('orderedList');
            },
            isActive(name) {
                return !!this.toolbarHighlights[name];
            },
            isExcluded(name) {
                return !!this.toolbarExcludes[name];
            },
            calculateExcluded(markName) {
                if (!editor) return false;
                const marks = this.getSelectionMarksStrict();
                // If the mark is already present somewhere, do NOT consider it excluded
                if (marks.some(m => m.type.name === markName)) return false;
                for (const m of marks) {
                    const excludes = m.type.spec.excludes;
                    if (!excludes) continue;
                    if (excludes === '_') {
                        // Exclude everything except itself
                        return m.type.name !== markName;
                    }
                    const list = excludes.split(' ').filter(Boolean);
                    if (list.includes(markName)) return true;
                }
                return false;
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
                    case 'bullet_list':
                        editor.chain().focus().toggleBulletList().run();
                    break;
                    case 'numbered_list':
                        editor.chain().focus().toggleOrderedList().run();
                    break;
                    default:
                        if (import.meta?.env?.DEV) console.warn('[Tollerus] Unknown toolbar action:', action);
                    break;
                }
            },
            getSelectionMarksStrict() {
                if (!editor) return [];
                const { state } = editor;
                const { from, to, empty } = state.selection;
                // Cursor case: use storedMarks/marks at cursor
                if (empty) {
                    const marks =
                        state.storedMarks ??
                        state.selection.$from.marks();
                    return marks ?? [];
                }
                // Range case: gather marks from any text node that overlaps the selection.
                const markMap = new Map(); // key => markType
                state.doc.nodesBetween(from, to, (node) => {
                    if (!node.isText) return;
                    for (const m of node.marks) {
                        markMap.set(m.type.name, m);
                    }
                });
                return Array.from(markMap.values());
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
