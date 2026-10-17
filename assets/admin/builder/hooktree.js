// Pure logic of the hook picker (no DOM): filtering the tree of twig hooks and managing the selected placements.

export const MIN_PRIORITY = -1000;
export const MAX_PRIORITY = 1000;
export const HOOK_NAME_PATTERN = /^[a-z0-9_]+(?:[.#][a-z0-9_]+)*$/;

/** Number of hooks (nodes flagged `hook`) in a tree. */
export function countHooks(nodes) {
    return nodes.reduce((total, node) => total + (node.hook ? 1 : 0) + countHooks(node.children ?? []), 0);
}

/**
 * Keeps the nodes whose path matches the query, and the ancestors of those that do.
 * Every node of a matching branch is returned with `open: true` so the matches are visible.
 * An empty query returns the tree untouched.
 */
export function filterTree(nodes, query) {
    const needle = query.trim().toLowerCase();
    if (needle === '') {
        return nodes;
    }

    return nodes.flatMap((node) => {
        const children = filterTree(node.children ?? [], needle);
        const matches = node.path.toLowerCase().includes(needle);

        if (!matches && children.length === 0) {
            return [];
        }

        // A matching node keeps its whole subtree; a non matching one only the matching descendants.
        return [{ ...node, children: matches ? node.children ?? [] : children, open: true }];
    });
}

export function parse(json) {
    let data;
    try {
        data = JSON.parse(json || '[]');
    } catch {
        return [];
    }

    if (!Array.isArray(data)) {
        return [];
    }

    return data
        .filter((entry) => entry && typeof entry.hook === 'string' && Number.isInteger(entry.priority))
        .map((entry) => ({ hook: entry.hook, priority: entry.priority }));
}

export function serialize(selected) {
    return JSON.stringify(selected.map(({ hook, priority }) => ({ hook, priority })));
}

export function isSelected(selected, hook) {
    return selected.some((entry) => entry.hook === hook);
}

/** Adds the hook (priority 0) when absent, removes it otherwise. */
export function toggle(selected, hook) {
    if (isSelected(selected, hook)) {
        return remove(selected, hook);
    }

    return [...selected, { hook, priority: 0 }];
}

export function remove(selected, hook) {
    return selected.filter((entry) => entry.hook !== hook);
}

/** Clamps to the allowed range; anything that is not a number becomes 0. */
export function clampPriority(value) {
    const number = Math.trunc(Number(value));
    if (!Number.isFinite(number)) {
        return 0;
    }

    return Math.min(MAX_PRIORITY, Math.max(MIN_PRIORITY, number));
}

export function setPriority(selected, hook, priority) {
    return selected.map((entry) => (entry.hook === hook ? { ...entry, priority: clampPriority(priority) } : entry));
}

/** Adds a hook typed by hand when its name is well formed and not selected yet. Returns the same array otherwise. */
export function addCustom(selected, name) {
    const hook = name.trim();
    if (!HOOK_NAME_PATTERN.test(hook) || isSelected(selected, hook)) {
        return selected;
    }

    return [...selected, { hook, priority: 0 }];
}

/**
 * Where a block of the given priority lands among the hookables already in a hook (sorted by priority, highest
 * first): the index of the first existing hookable it renders before. Equal priorities render after the existing ones.
 */
export function insertionIndex(hookables, priority) {
    const sorted = [...hookables].sort((a, b) => b.priority - a.priority);
    const index = sorted.findIndex((hookable) => hookable.priority < priority);

    return index === -1 ? sorted.length : index;
}
