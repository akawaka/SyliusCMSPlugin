// Pure functions operating on the block tree: [{ id, type, props, children: [] }].
// No DOM access here so everything can be unit tested with `node --test`.

export const COLUMNS = 'columns';
export const COLUMN = 'column';

const SUMMARY_PROPS = ['title', 'label', 'html', 'src', 'url', 'code', 'taxon'];

export function newId() {
    const random = globalThis.crypto?.randomUUID?.() ?? `${Date.now().toString(16)}${Math.random().toString(16).slice(2)}`;

    return random.replace(/-/g, '').slice(0, 10);
}

export function clone(value) {
    return JSON.parse(JSON.stringify(value));
}

/** catalog: { [code]: { code, label, icon, defaults, internal, children: [codes] } } */
export function canContain(catalog, parentType, childType) {
    if (!catalog[childType]) {
        return false;
    }

    if (parentType === null || parentType === undefined || parentType === '') {
        return !catalog[childType].internal;
    }

    return (catalog[parentType]?.children ?? []).includes(childType);
}

export function createBlock(catalog, type) {
    const block = { id: newId(), type, props: clone(catalog[type]?.defaults ?? {}), children: [] };

    return syncColumns(block, catalog);
}

/** Keeps the number of column children of a "columns" block equal to its `columns` prop. */
export function syncColumns(block, catalog) {
    if (block.type !== COLUMNS) {
        return block;
    }

    const wanted = Math.min(Math.max(parseInt(block.props.columns, 10) || 2, 1), 6);
    block.children = block.children ?? [];

    while (block.children.length < wanted) {
        block.children.push(createBlock(catalog, COLUMN));
    }

    // Content of removed columns is merged into the last kept column instead of being lost.
    while (block.children.length > wanted) {
        const removed = block.children.pop();
        const last = block.children[block.children.length - 1];
        last.children = [...(last.children ?? []), ...(removed.children ?? [])];
    }

    return block;
}

/** Returns { node, siblings, index, parent } or null. `parent` is null for root blocks. */
export function find(tree, id, parent = null) {
    for (let index = 0; index < tree.length; index++) {
        const node = tree[index];
        if (node.id === id) {
            return { node, siblings: tree, index, parent };
        }

        const found = find(node.children ?? [], id, node);
        if (found) {
            return found;
        }
    }

    return null;
}

function childrenOf(tree, parentId) {
    if (parentId === null || parentId === undefined || parentId === '') {
        return tree;
    }

    const parent = find(tree, parentId)?.node;
    if (!parent) {
        return null;
    }

    parent.children = parent.children ?? [];

    return parent.children;
}

export function insert(tree, parentId, index, node) {
    const list = childrenOf(tree, parentId);
    if (!list) {
        return false;
    }

    list.splice(Math.min(Math.max(index, 0), list.length), 0, node);

    return true;
}

export function remove(tree, id) {
    const found = find(tree, id);
    if (!found) {
        return null;
    }

    found.siblings.splice(found.index, 1);

    return found.node;
}

/** `index` is the final position inside the destination list (as reported by SortableJS). */
export function move(tree, id, toParentId, index) {
    const found = find(tree, id);
    if (!found) {
        return false;
    }

    // A block can never be moved into itself or one of its own descendants.
    if (toParentId && (toParentId === id || find(found.node.children ?? [], toParentId))) {
        return false;
    }

    remove(tree, id);

    return insert(tree, toParentId, index, found.node);
}

export function regenerateIds(node) {
    node.id = newId();
    (node.children ?? []).forEach(regenerateIds);

    return node;
}

export function duplicate(tree, id) {
    const found = find(tree, id);
    if (!found) {
        return null;
    }

    const copy = regenerateIds(clone(found.node));
    found.siblings.splice(found.index + 1, 0, copy);

    return copy;
}

export function setProps(tree, id, props) {
    const found = find(tree, id);
    if (!found) {
        return false;
    }

    found.node.props = props;

    return true;
}

export function count(tree) {
    return tree.reduce((total, node) => total + 1 + count(node.children ?? []), 0);
}

export function summarize(block, max = 70) {
    for (const key of SUMMARY_PROPS) {
        const value = block.props?.[key];
        if (typeof value === 'string' && value.trim() !== '') {
            const text = value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            if (text !== '') {
                return text.length > max ? `${text.slice(0, max)}…` : text;
            }
        }
    }

    return '';
}

/** Parses the stored JSON; returns null when it is not a valid list of blocks. */
export function parse(json) {
    if (json === null || json === undefined || json.trim() === '') {
        return [];
    }

    try {
        const value = JSON.parse(json);

        return Array.isArray(value) ? value : null;
    } catch {
        return null;
    }
}
