// Snapshot based undo/redo stack.
export class History {
    constructor(initial, limit = 100) {
        this.limit = limit;
        this.stack = [initial];
        this.cursor = 0;
    }

    push(snapshot) {
        if (snapshot === this.stack[this.cursor]) {
            return false;
        }

        this.stack = this.stack.slice(0, this.cursor + 1);
        this.stack.push(snapshot);

        if (this.stack.length > this.limit) {
            this.stack.shift();
        }

        this.cursor = this.stack.length - 1;

        return true;
    }

    get canUndo() {
        return this.cursor > 0;
    }

    get canRedo() {
        return this.cursor < this.stack.length - 1;
    }

    undo() {
        return this.canUndo ? this.stack[--this.cursor] : null;
    }

    redo() {
        return this.canRedo ? this.stack[++this.cursor] : null;
    }
}
