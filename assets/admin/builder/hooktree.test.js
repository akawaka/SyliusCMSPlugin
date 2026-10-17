import test from 'node:test';
import assert from 'node:assert/strict';

import { addCustom, clampPriority, countHooks, filterTree, insertionIndex, parse, remove, serialize, setPriority, toggle } from './hooktree.js';

const tree = [
    {
        label: 'sylius_shop', path: 'sylius_shop', hook: false, children: [
            { label: '.cart', path: 'sylius_shop.cart', hook: true, children: [
                { label: '.summary', path: 'sylius_shop.cart.summary', hook: true, children: [] },
            ] },
            { label: '.homepage', path: 'sylius_shop.homepage', hook: false, children: [
                { label: '.index', path: 'sylius_shop.homepage.index', hook: true, children: [] },
            ] },
        ],
    },
];

test('countHooks counts every node flagged as a hook', () => {
    assert.equal(countHooks(tree), 3);
});

test('filterTree returns the tree untouched for an empty query', () => {
    assert.equal(filterTree(tree, '  '), tree);
});

test('filterTree keeps matches with their ancestors and opens them', () => {
    const filtered = filterTree(tree, 'HOMEPAGE');

    assert.equal(filtered.length, 1);
    assert.equal(filtered[0].open, true);
    assert.deepEqual(filtered[0].children.map((child) => child.path), ['sylius_shop.homepage']);
    assert.equal(filtered[0].children[0].children[0].path, 'sylius_shop.homepage.index');
});

test('filterTree keeps the whole subtree of a matching node', () => {
    const filtered = filterTree(tree, 'cart');

    assert.deepEqual(filtered[0].children[0].children.map((child) => child.path), ['sylius_shop.cart.summary']);
});

test('filterTree returns nothing when nothing matches', () => {
    assert.deepEqual(filterTree(tree, 'nope'), []);
});

test('toggle adds a hook with priority 0 and removes it on the second call', () => {
    const added = toggle([], 'sylius_shop.cart');
    assert.deepEqual(added, [{ hook: 'sylius_shop.cart', priority: 0 }]);
    assert.deepEqual(toggle(added, 'sylius_shop.cart'), []);
});

test('setPriority only changes the given hook and clamps the value', () => {
    const selected = [{ hook: 'a', priority: 0 }, { hook: 'b', priority: 5 }];

    assert.deepEqual(setPriority(selected, 'a', '250'), [{ hook: 'a', priority: 250 }, { hook: 'b', priority: 5 }]);
    assert.equal(setPriority(selected, 'a', 99999)[0].priority, 1000);
    assert.equal(setPriority(selected, 'a', -99999)[0].priority, -1000);
    assert.equal(clampPriority('abc'), 0);
    assert.equal(clampPriority(12.9), 12);
});

test('remove drops a hook', () => {
    assert.deepEqual(remove([{ hook: 'a', priority: 0 }, { hook: 'b', priority: 0 }], 'a'), [{ hook: 'b', priority: 0 }]);
});

test('addCustom accepts well formed unseen names only', () => {
    const selected = [{ hook: 'sylius_shop.cart', priority: 0 }];

    assert.equal(addCustom(selected, 'sylius_shop.cart'), selected);
    assert.equal(addCustom(selected, 'Not valid!'), selected);
    assert.equal(addCustom(selected, '../etc'), selected);
    assert.deepEqual(addCustom(selected, ' app.footer#links '), [...selected, { hook: 'app.footer#links', priority: 0 }]);
});

test('parse and serialize round trip and tolerate bad input', () => {
    const selected = [{ hook: 'a.b', priority: -10 }];

    assert.deepEqual(parse(serialize(selected)), selected);
    assert.deepEqual(parse('not json'), []);
    assert.deepEqual(parse('{"a":1}'), []);
    assert.deepEqual(parse('[{"hook":"a","priority":"x"},{"hook":"b","priority":3},null]'), [{ hook: 'b', priority: 3 }]);
});

test('insertionIndex places the block among existing hookables by priority', () => {
    const hookables = [{ priority: 0 }, { priority: 300 }, { priority: 100 }];

    assert.equal(insertionIndex(hookables, 500), 0);
    assert.equal(insertionIndex(hookables, 200), 1);
    assert.equal(insertionIndex(hookables, 50), 2);
    assert.equal(insertionIndex(hookables, 0), 3, 'equal priority renders after the existing one');
    assert.equal(insertionIndex([], 10), 0);
});
