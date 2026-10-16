import { Controller } from '@hotwired/stimulus';

const REFRESH_DEBOUNCE = 500;

/**
 * Live preview of the page being edited: the unsaved content is posted to the server, which
 * returns the URL of the real storefront page rendering it. It is shown in an iframe whose
 * width emulates a mobile, tablet or desktop screen.
 */
export default class extends Controller {
    static targets = ['panel', 'stage', 'scaler', 'viewport', 'device', 'width', 'warnings', 'status', 'open', 'toggle', 'toggleLabel'];

    static values = {
        url: String,
        csrf: String,
        locale: String,
        fieldPrefix: String,
        devices: Object,
        labels: Object,
        device: { type: String, default: 'desktop' },
    };

    connect() {
        this.token = null;
        this.frameUrl = null;
        this.dirty = true;
        this.visible = false;
        this.requestId = 0;

        this.onFieldInput = () => this.schedule();
        this.fields().forEach((field) => field.addEventListener('input', this.onFieldInput));

        this.resizeObserver = new ResizeObserver(() => this.applyDevice());
        this.resizeObserver.observe(this.stageTarget);
    }

    disconnect() {
        this.fields().forEach((field) => field.removeEventListener('input', this.onFieldInput));
        this.resizeObserver?.disconnect();
        clearTimeout(this.timer);
        this.abort?.abort();
    }

    // ------------------------------------------------------------------ UI

    toggle() {
        this.visible = !this.visible;
        this.panelTarget.hidden = !this.visible;
        this.toggleTarget.classList.toggle('active', this.visible);
        this.toggleLabelTarget.textContent = this.visible ? this.labelsValue.edit : this.labelsValue.preview;

        // The preview takes the place of the builder (palette, canvas, inspector).
        this.element.classList.toggle('akb-wrap--previewing', this.visible);

        if (this.visible) {
            this.applyDevice();
            if (this.dirty) {
                this.refresh();
            }
        }
    }

    selectDevice(event) {
        this.deviceValue = event.currentTarget.dataset.device;
    }

    deviceValueChanged() {
        if (this.hasStageTarget) {
            this.applyDevice();
        }
    }

    /** Sizes the iframe like the chosen device and scales it down to fit the available width. */
    applyDevice() {
        const width = this.devicesValue[this.deviceValue] ?? 1280;
        const available = this.stageTarget.clientWidth;
        const stageHeight = this.stageTarget.clientHeight;
        const scale = available > 0 ? Math.min(1, available / width) : 1;

        this.scalerTarget.style.width = `${width * scale}px`;
        this.viewportTarget.style.width = `${width}px`;
        this.viewportTarget.style.height = `${stageHeight / scale}px`;
        this.viewportTarget.style.transform = `scale(${scale})`;

        this.deviceTargets.forEach((button) => {
            const active = button.dataset.device === this.deviceValue;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        this.widthTarget.textContent = `${width}px${scale < 1 ? ` · ${Math.round(scale * 100)}%` : ''}`;
    }

    // -------------------------------------------------------------- refresh

    /** Called by the builder `change` event and by edits of the page fields. */
    schedule() {
        this.dirty = true;
        if (!this.visible) {
            return;
        }

        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.refresh(), REFRESH_DEBOUNCE);
    }

    async refresh() {
        const blocks = this.readBlocks();
        if (blocks === null) {
            this.setStatus(this.labelsValue.invalid, true);

            return;
        }

        this.abort?.abort();
        this.abort = new AbortController();
        const requestId = ++this.requestId;
        this.setStatus(this.labelsValue.loading, false);

        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                signal: this.abort.signal,
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfValue, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ token: this.token, locale: this.localeValue, page: this.readPageFields(), blocks }),
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            if (requestId !== this.requestId) {
                return;
            }

            this.token = data.token;
            this.dirty = false;
            this.showWarnings(data.warnings ?? []);
            this.openTarget.href = data.url;

            // The URL is stable for a token: reload it so the server renders the new content.
            await this.loadFrame(data.url);
            this.setStatus('', false);
        } catch (error) {
            if (error.name !== 'AbortError') {
                this.setStatus(this.labelsValue.error, true);
            }
        }
    }

    /** Double buffering: the new page is loaded hidden, then swapped in at the same scroll position. */
    loadFrame(url) {
        return new Promise((resolve) => {
            const previous = this.viewportTarget.querySelector('iframe');
            const frame = document.createElement('iframe');
            frame.className = 'akp__frame';
            frame.title = this.labelsValue.title;
            frame.style.visibility = 'hidden';

            frame.addEventListener('load', () => {
                const scrollY = previous?.contentWindow?.scrollY ?? 0;
                frame.contentWindow?.scrollTo(0, scrollY);
                frame.style.visibility = '';
                previous?.remove();
                resolve();
            }, { once: true });

            frame.src = url;
            this.viewportTarget.append(frame);
        });
    }

    // ----------------------------------------------------------------- data

    readBlocks() {
        const input = this.element.querySelector('textarea[data-akawaka-cms-builder-target="input"]');
        if (!input) {
            return [];
        }

        try {
            const blocks = JSON.parse(input.value || '[]');

            return Array.isArray(blocks) ? blocks : null;
        } catch {
            return null;
        }
    }

    fields() {
        return ['title', 'slug', 'metaTitle', 'metaDescription', 'ogImage']
            .map((name) => document.querySelector(`[name="${this.fieldPrefixValue}[${name}]"]`))
            .filter(Boolean);
    }

    readPageFields() {
        return Object.fromEntries(
            ['title', 'slug', 'metaTitle', 'metaDescription', 'ogImage'].map((name) => [
                name,
                document.querySelector(`[name="${this.fieldPrefixValue}[${name}]"]`)?.value ?? '',
            ]),
        );
    }

    showWarnings(warnings) {
        this.warningsTarget.hidden = warnings.length === 0;
        this.warningsTarget.textContent = warnings.length === 0 ? '' : `${warnings.length} ${this.labelsValue.warnings}`;
        this.warningsTarget.title = warnings.join('\n');
    }

    setStatus(text, isError) {
        this.statusTarget.textContent = text;
        this.statusTarget.classList.toggle('text-danger', isError);
    }
}
