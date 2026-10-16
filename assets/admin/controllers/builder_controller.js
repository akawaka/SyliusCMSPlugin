import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

import { History } from '../builder/history.js';
import * as Tree from '../builder/tree.js';
import { createWysiwyg } from '../builder/wysiwyg.js';

const GROUP = 'akawaka-cms-blocks';
const INSPECTOR_DEBOUNCE = 250;

/**
 * Block based page builder: palette | canvas | inspector.
 * The block tree is kept in memory and serialized as JSON into the hidden input on every change.
 */
export default class extends Controller {
    static targets = ['input', 'palette', 'canvas', 'inspector', 'undo', 'redo', 'error', 'fullscreen'];

    static values = {
        catalog: Array,
        formUrl: String,
        labels: Object,
    };

    connect() {
        this.catalog = Object.fromEntries(this.catalogValue.map((entry) => [entry.code, entry]));
        this.formCache = new Map();
        this.sortables = [];
        this.editors = [];
        this.selectedId = null;

        const parsed = Tree.parse(this.inputTarget.value);
        if (parsed === null) {
            this.errorTarget.textContent = this.labelsValue.invalid_json;
            this.errorTarget.hidden = false;
            this.element.classList.add('akb--broken');

            return;
        }

        this.tree = parsed;
        this.history = new History(this.serialize());

        this.renderPalette();
        this.render();
        this.renderInspector();
        this.updateHistoryButtons();

        this.onKeydown = this.onKeydown.bind(this);
        this.element.addEventListener('keydown', this.onKeydown);
    }

    disconnect() {
        this.setFullscreen(false);
        this.destroyEditors();
        this.destroySortables();
        this.paletteSortable?.destroy();
        this.element.removeEventListener('keydown', this.onKeydown);
        clearTimeout(this.inspectorTimer);
    }

    // ------------------------------------------------------------- fullscreen

    toggleFullscreen() {
        this.setFullscreen(!this.fullscreen);
    }

    /** CSS based (not the Fullscreen API) so editor popups and dialogs keep working. */
    setFullscreen(enabled) {
        const wrapper = this.element.closest('.akb-wrap');
        if (!wrapper || enabled === Boolean(this.fullscreen)) {
            return;
        }

        this.fullscreen = enabled;
        wrapper.classList.toggle('akb-wrap--fullscreen', enabled);
        document.documentElement.classList.toggle('akb-fullscreen-active', enabled);

        if (this.hasFullscreenTarget) {
            this.fullscreenTarget.setAttribute('aria-pressed', String(enabled));
            this.fullscreenTarget.title = enabled ? this.labelsValue.exit_fullscreen : this.labelsValue.fullscreen;
        }

        if (enabled) {
            this.onEscape = (event) => {
                if (event.key === 'Escape' && !event.defaultPrevented) {
                    this.setFullscreen(false);
                }
            };
            document.addEventListener('keydown', this.onEscape);
        } else if (this.onEscape) {
            document.removeEventListener('keydown', this.onEscape);
            this.onEscape = null;
        }
    }

    // ---------------------------------------------------------------- palette

    renderPalette() {
        this.paletteTarget.replaceChildren(
            ...this.catalogValue
                .filter((entry) => !entry.internal)
                .map((entry) => {
                    // Not a <button>: buttons cannot be natively dragged in every browser.
                    const item = document.createElement('div');
                    item.className = 'akb-palette__item';
                    item.setAttribute('role', 'button');
                    item.tabIndex = 0;
                    item.dataset.type = entry.code;
                    item.innerHTML = `<span class="akb-icon">${entry.icon}</span><span></span>`;
                    item.lastElementChild.textContent = entry.label;
                    item.addEventListener('click', () => this.addFromPalette(entry.code));
                    item.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            this.addFromPalette(entry.code);
                        }
                    });

                    return item;
                }),
        );

        this.paletteSortable = new Sortable(this.paletteTarget, {
            group: { name: GROUP, pull: 'clone', put: false },
            sort: false,
            animation: 150,
            draggable: '.akb-palette__item',
            fallbackOnBody: true,
        });
    }

    /** Click-to-add: inside the selected container, else after the selected block, else at the end. */
    addFromPalette(type) {
        const block = Tree.createBlock(this.catalog, type);
        const selected = this.selectedId ? Tree.find(this.tree, this.selectedId) : null;

        let placed = false;
        if (selected && Tree.canContain(this.catalog, selected.node.type, type)) {
            placed = Tree.insert(this.tree, selected.node.id, selected.node.children?.length ?? 0, block);
        } else if (selected && Tree.canContain(this.catalog, selected.parent?.type ?? null, type)) {
            placed = Tree.insert(this.tree, selected.parent?.id ?? null, selected.index + 1, block);
        }

        if (!placed) {
            Tree.insert(this.tree, null, this.tree.length, block);
        }

        this.selectedId = block.id;
        this.commit();
    }

    // ----------------------------------------------------------------- canvas

    render() {
        this.destroySortables();

        const scroll = this.canvasTarget.scrollTop;
        const root = this.createList(this.tree, null);
        root.classList.add('akb-list--root');
        root.dataset.empty = this.labelsValue.empty_canvas;
        this.canvasTarget.replaceChildren(root);
        this.canvasTarget.scrollTop = scroll;
    }

    createList(blocks, parent) {
        const list = document.createElement('div');
        list.className = 'akb-list';
        list.dataset.parentId = parent?.id ?? '';
        list.dataset.parentType = parent?.type ?? '';
        list.dataset.empty = this.labelsValue.empty_container;
        if (parent?.type === Tree.COLUMNS) {
            list.classList.add('akb-list--columns');
        }

        blocks.forEach((block) => list.append(this.createBlockElement(block)));

        this.sortables.push(
            new Sortable(list, {
                group: { name: GROUP, pull: true, put: (to, from, dragEl) => this.canDrop(to.el, dragEl) },
                animation: 150,
                handle: '.akb-handle',
                draggable: '.akb-block',
                fallbackOnBody: true,
                swapThreshold: 0.65,
                emptyInsertThreshold: 12,
                onAdd: (event) => this.onAdd(event),
                onUpdate: (event) => this.onUpdate(event),
            }),
        );

        return list;
    }

    createBlockElement(block) {
        const entry = this.catalog[block.type];
        const element = document.createElement('div');
        element.className = 'akb-block';
        element.dataset.id = block.id;
        element.dataset.type = block.type;
        element.classList.toggle('is-selected', block.id === this.selectedId);
        element.classList.toggle('akb-block--internal', Boolean(entry?.internal));
        element.classList.toggle('akb-block--unknown', !entry);

        const bar = document.createElement('div');
        bar.className = 'akb-block__bar';
        bar.innerHTML = `<span class="akb-handle" title="${this.escape(this.labelsValue.move)}">${entry?.icon ?? ''}</span><span class="akb-block__label"></span><span class="akb-block__summary"></span>`;
        bar.querySelector('.akb-block__label').textContent = entry?.label ?? block.type;
        bar.querySelector('.akb-block__summary').textContent = Tree.summarize(block);

        if (entry && !entry.internal) {
            bar.append(this.createAction('duplicate', this.labelsValue.duplicate, 'akb-action--duplicate'));
            bar.append(this.createAction('remove', this.labelsValue.remove, 'akb-action--remove'));
        }

        element.append(bar);

        if (entry && entry.children.length > 0) {
            element.append(this.createList(block.children ?? [], block));
        }

        return element;
    }

    createAction(action, title, className) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `akb-action ${className}`;
        button.dataset.action = action;
        button.title = title;
        button.setAttribute('aria-label', title);
        button.textContent = action === 'remove' ? '✕' : '⧉';

        return button;
    }

    destroySortables() {
        this.sortables.forEach((sortable) => sortable.destroy());
        this.sortables = [];
    }

    canDrop(listElement, dragElement) {
        return Tree.canContain(this.catalog, listElement.dataset.parentType || null, dragElement.dataset.type);
    }

    onAdd(event) {
        const parentId = event.to.dataset.parentId || null;
        const id = event.item.dataset.id;

        if (id) {
            Tree.move(this.tree, id, parentId, event.newIndex);
            this.selectedId = id;
        } else {
            // Dropped from the palette: the clone is replaced by a real block.
            const block = Tree.createBlock(this.catalog, event.item.dataset.type);
            event.item.remove();
            Tree.insert(this.tree, parentId, event.newIndex, block);
            this.selectedId = block.id;
        }

        this.commit();
    }

    onUpdate(event) {
        const id = event.item.dataset.id;
        Tree.move(this.tree, id, event.to.dataset.parentId || null, event.newIndex);
        this.commit();
    }

    onCanvasClick(event) {
        const actionButton = event.target.closest('[data-action]');
        const blockElement = event.target.closest('.akb-block');
        if (!blockElement) {
            return;
        }

        const id = blockElement.dataset.id;

        if (actionButton?.dataset.action === 'remove') {
            if (window.confirm(this.labelsValue.confirm_remove)) {
                Tree.remove(this.tree, id);
                if (this.selectedId === id || !Tree.find(this.tree, this.selectedId)) {
                    this.selectedId = null;
                }
                this.commit();
            }

            return;
        }

        if (actionButton?.dataset.action === 'duplicate') {
            const copy = Tree.duplicate(this.tree, id);
            this.selectedId = copy?.id ?? id;
            this.commit();

            return;
        }

        this.select(id);
    }

    select(id) {
        if (this.selectedId === id) {
            return;
        }

        this.selectedId = id;
        this.canvasTarget.querySelectorAll('.akb-block.is-selected').forEach((element) => element.classList.remove('is-selected'));
        this.canvasTarget.querySelector(`.akb-block[data-id="${CSS.escape(id)}"]`)?.classList.add('is-selected');
        this.renderInspector();
    }

    // -------------------------------------------------------------- inspector

    destroyEditors() {
        this.editors.forEach((editor) => editor.destroy());
        this.editors = [];
    }

    /** Fields flagged with data-akawaka-wysiwyg (e.g. rich text) are edited with SunEditor. */
    enhanceForm(container) {
        container.querySelectorAll('textarea[data-akawaka-wysiwyg]').forEach((textarea) => {
            this.editors.push(
                createWysiwyg(textarea, (html) => {
                    textarea.value = html;
                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                }),
            );
        });
    }

    async renderInspector() {
        this.destroyEditors();

        const selected = this.selectedId ? Tree.find(this.tree, this.selectedId) : null;
        const token = (this.inspectorToken = Symbol());

        if (!selected) {
            this.inspectorTarget.innerHTML = '';
            const hint = document.createElement('p');
            hint.className = 'akb-inspector__hint';
            hint.textContent = this.labelsValue.select_hint;
            this.inspectorTarget.append(hint);

            return;
        }

        const { node } = selected;
        const entry = this.catalog[node.type];

        const title = document.createElement('h4');
        title.className = 'akb-inspector__title';
        title.innerHTML = `<span class="akb-icon">${entry?.icon ?? ''}</span><span></span>`;
        title.lastElementChild.textContent = entry?.label ?? node.type;
        this.inspectorTarget.replaceChildren(title);

        let html;
        try {
            html = await this.loadForm(node.type);
        } catch {
            html = `<p class="text-danger">${this.escape(this.labelsValue.load_error)}</p>`;
        }

        // The selection may have changed while the form was loading.
        if (token !== this.inspectorToken) {
            return;
        }

        const form = document.createElement('div');
        form.className = 'akb-inspector__form';
        form.innerHTML = html;
        this.inspectorTarget.append(form);
        this.fillForm(form, node.props ?? {});
        this.enhanceForm(form);

        if (form.querySelector('input, select, textarea') === null) {
            const none = document.createElement('p');
            none.className = 'akb-inspector__hint';
            none.textContent = this.labelsValue.no_props;
            form.append(none);
        }
    }

    async loadForm(type) {
        if (!this.formCache.has(type)) {
            const response = await fetch(this.formUrlValue.replace('__type__', encodeURIComponent(type)), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            this.formCache.set(type, await response.text());
        }

        return this.formCache.get(type);
    }

    fillForm(container, props) {
        container.querySelectorAll('[name^="props["]').forEach((field) => {
            const key = field.name.slice('props['.length, -1);
            const value = props[key];

            if (field.type === 'checkbox') {
                field.checked = Boolean(value);
            } else if (value !== undefined && value !== null) {
                field.value = String(value);
            }
        });
    }

    readForm(container) {
        const props = {};

        container.querySelectorAll('[name^="props["]').forEach((field) => {
            const key = field.name.slice('props['.length, -1);

            if (field.type === 'checkbox') {
                props[key] = field.checked;
            } else if ((field.tagName === 'SELECT' || field.type === 'number') && /^-?\d+$/.test(field.value)) {
                props[key] = Number(field.value);
            } else {
                props[key] = field.value;
            }
        });

        return props;
    }

    onInspectorInput(event) {
        if (!event.target.closest('.akb-inspector__form') || !this.selectedId) {
            return;
        }

        const selected = Tree.find(this.tree, this.selectedId);
        if (!selected) {
            return;
        }

        const form = this.inspectorTarget.querySelector('.akb-inspector__form');
        const previousColumns = selected.node.props?.columns;
        Tree.setProps(this.tree, this.selectedId, this.readForm(form));
        this.writeInput();

        if (selected.node.type === Tree.COLUMNS && selected.node.props.columns !== previousColumns) {
            Tree.syncColumns(selected.node, this.catalog);
            this.commit({ keepInspector: true });

            return;
        }

        const summary = this.canvasTarget.querySelector(`.akb-block[data-id="${CSS.escape(this.selectedId)}"] > .akb-block__bar .akb-block__summary`);
        if (summary) {
            summary.textContent = Tree.summarize(selected.node);
        }

        clearTimeout(this.inspectorTimer);
        this.inspectorTimer = setTimeout(() => this.snapshot(), INSPECTOR_DEBOUNCE);
    }

    // ------------------------------------------------------ state & history

    serialize() {
        return JSON.stringify(this.tree);
    }

    writeInput() {
        this.inputTarget.value = JSON.stringify(this.tree, null, 2);
        this.inputTarget.dispatchEvent(new Event('input', { bubbles: true }));
        this.dispatch('change', { detail: { blocks: this.tree } });
    }

    snapshot() {
        this.history.push(this.serialize());
        this.updateHistoryButtons();
    }

    /** Applies a structural change: re-render, serialize and record in history. */
    commit({ keepInspector = false } = {}) {
        clearTimeout(this.inspectorTimer);
        this.render();
        this.writeInput();
        this.snapshot();

        if (!keepInspector) {
            this.inspectorToken = null;
            this.renderInspector();
        }
    }

    undo() {
        this.restore(this.history.undo());
    }

    redo() {
        this.restore(this.history.redo());
    }

    restore(snapshot) {
        if (snapshot === null) {
            return;
        }

        this.tree = JSON.parse(snapshot);
        if (this.selectedId && !Tree.find(this.tree, this.selectedId)) {
            this.selectedId = null;
        }

        this.render();
        this.writeInput();
        this.renderInspector();
        this.updateHistoryButtons();
    }

    updateHistoryButtons() {
        this.undoTarget.disabled = !this.history.canUndo;
        this.redoTarget.disabled = !this.history.canRedo;
    }

    onKeydown(event) {
        if (!(event.ctrlKey || event.metaKey) || event.altKey) {
            return;
        }

        // Let text fields use their own native undo.
        if (event.target.closest('input, textarea, select')) {
            return;
        }

        const key = event.key.toLowerCase();
        if (key === 'z') {
            event.preventDefault();
            event.shiftKey ? this.redo() : this.undo();
        } else if (key === 'y') {
            event.preventDefault();
            this.redo();
        }
    }

    escape(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';

        return div.innerHTML.replace(/"/g, '&quot;');
    }
}
