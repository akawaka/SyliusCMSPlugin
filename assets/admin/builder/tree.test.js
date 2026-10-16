import test from 'node:test';
import assert from 'node:assert/strict';

import { canContain, count, createBlock, duplicate, find, insert, move, parse, remove, summarize, syncColumns } from './tree.js';
import { History } from './history.js';

const catalog = {
    rich_text: { code: 'rich_text', defaults: { html: '' }, internal: false, children: [] },
    button: { code: 'button', defaults: { label: 'Go' }, internal: false, children: [] },
    columns: { code: 'columns', defaults: { columns: 2 }, internal: false, children: ['column'] },
    column: { code: 'column', defaults: {}, internal: true, children: ['rich_text', 'button', 'columns'] },
};

test('canContain respects internal blocks and accepted children', () => {
    assert.equal(canContain(catalog, null, 'rich_text'), true);
    assert.equal(canContain(catalog, null, 'column'), false);
    assert.equal(canContain(catalog, 'columns', 'column'), true);
    assert.equal(canContain(catalog, 'columns', 'rich_text'), false);
    assert.equal(canContain(catalog, 'rich_text', 'button'), false);
    assert.equal(canContain(catalog, null, 'unknown'), false);
});

test('createBlock builds the columns of a columns block', () => {
    const block = createBlock(catalog, 'columns');

    assert.equal(block.children.length, 2);
    assert.ok(block.children.every((child) => child.type === 'column'));
    assert.notEqual(block.children[0].id, block.children[1].id);
});

test('syncColumns merges removed columns into the last kept one', () => {
    const block = createBlock(catalog, 'columns');
    block.props.columns = 3;
    syncColumns(block, catalog);
    assert.equal(block.children.length, 3);

    block.children[2].children.push(createBlock(catalog, 'button'));
    block.props.columns = 2;
    syncColumns(block, catalog);

    assert.equal(block.children.length, 2);
    assert.equal(block.children[1].children.length, 1);
});

test('insert, move and remove work across nested lists', () => {
    const tree = [];
    const text = createBlock(catalog, 'rich_text');
    const columns = createBlock(catalog, 'columns');
    insert(tree, null, 0, text);
    insert(tree, null, 1, columns);

    assert.equal(move(tree, text.id, columns.children[1].id, 0), true);
    assert.equal(tree.length, 1);
    assert.equal(find(tree, text.id).parent.id, columns.children[1].id);

    assert.equal(move(tree, columns.id, columns.children[0].id, 0), false, 'cannot move into itself');

    remove(tree, text.id);
    assert.equal(count(tree), 3);
});

test('move reorders inside the same list using the final index', () => {
    const tree = [];
    const [a, b, c] = ['rich_text', 'button', 'rich_text'].map((type) => createBlock(catalog, type));
    [a, b, c].forEach((block, index) => insert(tree, null, index, block));

    move(tree, a.id, null, 2);

    assert.deepEqual(tree.map((block) => block.id), [b.id, c.id, a.id]);
});

test('duplicate deep clones with fresh unique ids', () => {
    const tree = [createBlock(catalog, 'columns')];
    const copy = duplicate(tree, tree[0].id);

    assert.equal(tree.length, 2);
    const ids = [];
    const collect = (nodes) => nodes.forEach((node) => { ids.push(node.id); collect(node.children); });
    collect(tree);
    assert.equal(new Set(ids).size, ids.length);
    assert.equal(copy.children.length, 2);
});

test('summarize strips tags and truncates', () => {
    assert.equal(summarize({ props: { html: '<p>Hello <b>world</b></p>' } }), 'Hello world');
    assert.equal(summarize({ props: { title: 'x'.repeat(100) } }).length, 71);
    assert.equal(summarize({ props: {} }), '');
});

test('parse accepts only JSON lists', () => {
    assert.deepEqual(parse(''), []);
    assert.deepEqual(parse('[]'), []);
    assert.equal(parse('{"a":1}'), null);
    assert.equal(parse('not json'), null);
});

test('history supports undo and redo and drops the redo branch on push', () => {
    const history = new History('a');
    history.push('b');
    history.push('c');
    assert.equal(history.push('c'), false);

    assert.equal(history.undo(), 'b');
    assert.equal(history.undo(), 'a');
    assert.equal(history.undo(), null);
    assert.equal(history.redo(), 'b');

    history.push('d');
    assert.equal(history.canRedo, false);
    assert.equal(history.undo(), 'b');
});
