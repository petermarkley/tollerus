import Alpine from 'alpinejs';
import { Editor, Mark, mergeAttributes, getMarkRange } from '@tiptap/core';
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
                    case 'link':
                        this.openLinkDialog();
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
            openLinkDialog() {
                if (!editor || this.rawMode) return;
                const ctx = this.getLinkContext();
                let href = '';
                let text = '';
                let active = false;
                if (ctx) {
                    href = ctx.href ?? '';
                    text = ctx.text ?? '';
                    active = true;
                } else {
                    // No link under cursor/selection: prefill with selected text (if any)
                    const { from, to, empty } = editor.state.selection;
                    text = empty ? '' : editor.state.doc.textBetween(from, to, ' ');
                }
                window.dispatchEvent(new CustomEvent('tollerus-wysiwyg-link-dialog-open', {
                    detail: { href, text, active },
                }));
            },
            getLinkContext() {
                if (!editor) return null;
                const { state } = editor;
                const linkType = editor.schema.marks.link;
                const { from, to, empty } = state.selection;
                /**
                 * Case A
                 * ======
                 * Cursor is inside a link (or selection anchor is)
                 */
                const directRange = getMarkRange(state.selection.$from, linkType);
                if (directRange) {
                    const href = editor.getAttributes('link')?.href ?? '';
                    const text = state.doc.textBetween(directRange.from, directRange.to, ' ');
                    return { href, text, range: directRange };
                }
                /**
                 * Case B
                 * ======
                 * Selection spans content and includes one or more
                 * links. Pick the first link we encounter, and
                 * expand to its full mark range.
                 */
                let found = null;
                state.doc.nodesBetween(from, to, (node, pos) => {
                    if (found) return false;
                    if (!node.isText) return;
                    const linkMark = node.marks.find(m => m.type === linkType);
                    if (!linkMark) return;
                    // Resolve a position inside this text node so getMarkRange can expand properly
                    const inside = state.doc.resolve(pos + 1);
                    const range = getMarkRange(inside, linkType);
                    if (!range) return;
                    const text = state.doc.textBetween(range.from, range.to, ' ');
                    found = { href: linkMark.attrs?.href ?? '', text, range };
                    return false;
                });
                return found;
            },
            applyLink({ href, text }) {
                if (!editor || this.rawMode) return;
                const url = (href ?? '').trim();
                if (!url) return;
                const label = (text ?? '').trim(); // (Nobody wants trailing whitespace on their links)
                const { state } = editor;
                const { from, to, empty } = state.selection;
                const ctx = this.getLinkContext();
                /**
                 * If we're inside a link, or selection includes
                 * one, we are editing that link.
                 */
                if (ctx?.range) {
                    // Update href/text on the existing link range
                    if (label.length > 0) {
                        editor.chain()
                            .focus()
                            .setTextSelection(ctx.range)
                            .unsetLink() // Avoid overlapping links
                            .insertContent(label)
                            .setTextSelection({ from: ctx.range.from, to: ctx.range.from + label.length })
                            .setLink({ href: url })
                            .run();
                    } else {
                        editor.chain()
                            .focus()
                            .setTextSelection(ctx.range)
                            .setLink({ href: url })
                            .run();
                    }
                    this.refreshToolbar();
                    return;
                }
                /**
                 * If no links in/around selection, then we are
                 * creating a new link. If there's multiple, then
                 * first we normalize to avoid overlapping or
                 * nested links.
                 */
                editor.chain().focus().unsetLink().run();
                /**
                 * If user provided text, replace entire selection
                 * with that. If user left it blank, we keep
                 * existing selection text.
                 */
                const wantsReplace = label.length > 0;
                if (empty) {
                    const insertText = wantsReplace ? label : url;
                    /**
                     * Insert, then select the inserted text using
                     * a stable reference: selection.from is after
                     * insertion, so we compute start from that.
                     */
                    editor.chain()
                        .insertContent(insertText)
                        .setTextSelection({
                            from: editor.state.selection.from - insertText.length,
                            to: editor.state.selection.from,
                        }).setLink({ href: url })
                        .run();
                    this.refreshToolbar();
                    return;
                }
                // Range selection
                if (wantsReplace) {
                    /**
                     * Replace the exact original range, then
                     * reselect the inserted label at that same
                     * start.
                     */
                    editor.chain()
                        .insertContentAt({ from, to }, label)
                        .setTextSelection({ from, to: from + label.length })
                        .setLink({ href: url })
                        .run();
                } else {
                    // Keep selected text; just apply link to the selection
                    editor.chain().setLink({ href: url }).run();
                }
                this.refreshToolbar();
            },
            removeLink() {
                if (!editor || this.rawMode) return;
                editor.chain().focus().extendMarkRange('link').unsetLink().run();
                this.refreshToolbar();
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
