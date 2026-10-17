import { Controller } from '@hotwired/stimulus';

import * as Hooks from '../builder/hooktree.js';

/**
 * Picks the twig hooks a content block is attached to: a collapsible, searchable tree of the hooks, and the list
 * of the selected ones with their priority and the content already in each hook. The selection is written as
 * JSON in the hidden input of the form.
 */
export default class extends Controller {
    static targets = ['input', 'search', 'tree', 'selected', 'count', 'custom', 'customError'];

    static values = {
        tree: Array,
        infoUrl: String,
        labels: Object,
    };

    connect() {
        this.selected = Hooks.parse(this.inputTarget.value);
        this.info = new Map();
        this.query = '';
        this.expandAll = false;
        // Paths the user expanded by hand, kept when the tree is rendered again (selection, search).
        this.openPaths = new Set();

        this.renderTree();
        this.renderSelected();
    }

    // ------------------------------------------------------------------ tree

    search() {
        this.query = this.searchTarget.value;
        this.renderTree();
    }

    expand() {
        this.expandAll = true;
        this.renderTree();
    }

    collapse() {
        this.expandAll = false;
        this.openPaths.clear();
        this.query = '';
        this.searchTarget.value = '';
        this.renderTree();
    }

    renderTree() {
        const nodes = Hooks.filterTree(this.treeValue, this.query);
        const root = document.createElement('ul');
        root.className = 'akh-tree';
        nodes.forEach((node) => root.append(this.renderNode(node, true)));

        this.treeTarget.replaceChildren(root);
        this.countTarget.textContent = String(Hooks.countHooks(nodes));
    }

    renderNode(node, top = false) {
        const item = document.createElement('li');
        const label = this.renderLabel(node, top);

        if ((node.children ?? []).length === 0) {
            item.append(label);

            return item;
        }

        const details = document.createElement('details');
        details.open = Boolean(node.open) || this.expandAll || top || this.openPaths.has(node.path);
        details.addEventListener('toggle', () => {
            if (details.open) {
                this.openPaths.add(node.path);
            } else {
                this.openPaths.delete(node.path);
            }
        });
        const summary = document.createElement('summary');
        summary.append(label);
        const list = document.createElement('ul');
        node.children.forEach((child) => list.append(this.renderNode(child)));
        details.append(summary, list);
        item.append(details);

        return item;
    }

    renderLabel(node, top) {
        // A node with children is a <span> inside the <summary>: clicking its text expands it (a <label> would
        // toggle the checkbox instead). Only the checkbox selects. A leaf keeps a <label>, so its text selects too.
        const label = document.createElement((node.children ?? []).length > 0 ? 'span' : 'label');
        label.className = 'akh-node' + (node.hook ? ' akh-node--hook' : '');
        label.title = node.path;

        if (node.hook) {
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'form-check-input';
            checkbox.checked = Hooks.isSelected(this.selected, node.path);
            checkbox.addEventListener('change', () => this.toggle(node.path));
            label.append(checkbox);
        }

        const text = document.createElement('span');
        text.textContent = top ? node.label : node.label.replace(/^\./, '');
        label.append(text);

        return label;
    }

    // ----------------------------------------------------------- selection

    toggle(hook) {
        this.selected = Hooks.toggle(this.selected, hook);
        this.commit();
        this.renderTree();
    }

    addCustom(event) {
        event.preventDefault();
        const before = this.selected;
        this.selected = Hooks.addCustom(this.selected, this.customTarget.value);

        if (this.selected === before) {
            this.customErrorTarget.textContent = this.labelsValue.invalid_custom;
            this.customErrorTarget.hidden = false;

            return;
        }

        this.customErrorTarget.hidden = true;
        this.customTarget.value = '';
        this.commit();
    }

    // Enter in the custom field must add the hook, not submit the whole page form.
    customKeydown(event) {
        if (event.key === 'Enter') {
            this.addCustom(event);
        }
    }

    // Enter in the search field must not submit the page form.
    preventSubmit(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    }

    commit() {
        this.inputTarget.value = Hooks.serialize(this.selected);
        this.renderSelected();
    }

    renderSelected() {
        if (this.selected.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'text-secondary mb-0';
            empty.textContent = this.labelsValue.none;
            this.selectedTarget.replaceChildren(empty);

            return;
        }

        this.selectedTarget.replaceChildren(...this.selected.map((entry) => this.renderEntry(entry)));
        this.selected.forEach((entry) => this.loadInfo(entry.hook));
    }

    renderEntry(entry) {
        const wrapper = document.createElement('div');
        wrapper.className = 'akh-entry';
        wrapper.dataset.hook = entry.hook;

        const header = document.createElement('div');
        header.className = 'akh-entry__header';

        const name = document.createElement('code');
        name.textContent = entry.hook;

        const priorityLabel = document.createElement('label');
        priorityLabel.className = 'akh-entry__priority';
        priorityLabel.append(document.createTextNode(this.labelsValue.priority + ' '));
        const priority = document.createElement('input');
        priority.type = 'number';
        priority.className = 'form-control form-control-sm';
        priority.min = String(Hooks.MIN_PRIORITY);
        priority.max = String(Hooks.MAX_PRIORITY);
        priority.value = String(entry.priority);
        priority.addEventListener('input', () => {
            this.selected = Hooks.setPriority(this.selected, entry.hook, priority.value);
            this.inputTarget.value = Hooks.serialize(this.selected);
            this.renderContent(wrapper, entry.hook);
        });
        // Typing in the priority must not submit the page form.
        priority.addEventListener('keydown', (event) => event.key === 'Enter' && event.preventDefault());
        priorityLabel.append(priority);

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn btn-sm btn-ghost-danger';
        remove.textContent = this.labelsValue.remove;
        remove.addEventListener('click', () => this.toggle(entry.hook));

        header.append(name, priorityLabel, remove);

        const content = document.createElement('div');
        content.className = 'akh-entry__content';
        wrapper.append(header, content);
        this.renderContent(wrapper, entry.hook);

        return wrapper;
    }

    // -------------------------------------------- content already in a hook

    async loadInfo(hook) {
        if (this.info.has(hook)) {
            return;
        }

        this.info.set(hook, { loading: true });
        try {
            const response = await fetch(this.infoUrlValue.replace('__hook__', encodeURIComponent(hook)), { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                throw new Error(String(response.status));
            }
            this.info.set(hook, { hookables: (await response.json()).hookables });
        } catch {
            this.info.set(hook, { error: true });
        }

        const wrapper = this.selectedTarget.querySelector(`[data-hook="${CSS.escape(hook)}"]`);
        if (wrapper) {
            this.renderContent(wrapper, hook);
        }
    }

    /** Lists what the hook already renders (highest priority first) and where this block lands among it. */
    renderContent(wrapper, hook) {
        const container = wrapper.querySelector('.akh-entry__content');
        const info = this.info.get(hook);
        const entry = this.selected.find((candidate) => candidate.hook === hook);

        if (!info || info.loading || !entry) {
            container.replaceChildren();

            return;
        }

        if (info.error) {
            container.textContent = this.labelsValue.load_error;
            container.className = 'akh-entry__content text-danger';

            return;
        }

        const title = document.createElement('div');
        title.className = 'akh-entry__title';
        title.textContent = info.hookables.length === 0 ? this.labelsValue.empty_hook : this.labelsValue.existing;

        const list = document.createElement('ol');
        list.className = 'akh-existing';
        const sorted = [...info.hookables].sort((a, b) => b.priority - a.priority);
        const index = Hooks.insertionIndex(sorted, entry.priority);

        const marker = () => {
            const item = document.createElement('li');
            item.className = 'akh-existing__marker';
            item.textContent = '▶ ' + this.element.dataset.blockLabel;

            return item;
        };

        sorted.forEach((hookable, position) => {
            if (position === index) {
                list.append(marker());
            }

            const item = document.createElement('li');
            item.className = hookable.type === 'disabled' ? 'text-secondary text-decoration-line-through' : '';
            item.textContent = `${hookable.priority} · ${hookable.name}`;
            item.title = hookable.target;
            list.append(item);
        });

        if (index >= sorted.length) {
            list.append(marker());
        }

        container.className = 'akh-entry__content';
        container.replaceChildren(title, list);
    }
}
