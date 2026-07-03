/**
 * BeaconCMS Hybrid Editor
 * Breakdance-inspired editor with Code Block + Visual Preview + Fields mode
 */

class BeaconEditor {
    constructor(textareaId, options = {}) {
        this.textarea = document.getElementById(textareaId);
        if (!this.textarea) return;

        this.options = {
            height: options.height || '500px',
            mode: options.mode || 'visual', // 'code', 'visual', 'split'
            ...options
        };

        this.init();
    }

    init() {
        // Wrap textarea in editor container
        this.container = document.createElement('div');
        this.container.className = 'beacon-editor';
        this.textarea.parentNode.insertBefore(this.container, this.textarea);

        // Build editor UI
        this.container.innerHTML = `
            <div class="be-toolbar">
                <div class="be-toolbar-left">
                    <div class="be-mode-switcher">
                        <button type="button" class="be-mode-btn active" data-mode="visual" title="Visual Editor">
                            <i class="fa-solid fa-eye"></i> Visual
                        </button>
                        <button type="button" class="be-mode-btn" data-mode="code" title="Code Editor">
                            <i class="fa-solid fa-code"></i> Code
                        </button>
                        <button type="button" class="be-mode-btn" data-mode="split" title="Split View">
                            <i class="fa-solid fa-columns"></i> Split
                        </button>
                    </div>
                </div>
                <div class="be-toolbar-center">
                    <div class="be-format-tools" id="be-format-tools-${this.textarea.id}">
                        <button type="button" class="be-tool-btn" data-cmd="bold" title="Bold (Ctrl+B)"><i class="fa-solid fa-bold"></i></button>
                        <button type="button" class="be-tool-btn" data-cmd="italic" title="Italic (Ctrl+I)"><i class="fa-solid fa-italic"></i></button>
                        <button type="button" class="be-tool-btn" data-cmd="underline" title="Underline"><i class="fa-solid fa-underline"></i></button>
                        <span class="be-tool-sep"></span>
                        <button type="button" class="be-tool-btn" data-cmd="heading" title="Heading"><i class="fa-solid fa-heading"></i></button>
                        <button type="button" class="be-tool-btn" data-cmd="insertUnorderedList" title="Bullet List"><i class="fa-solid fa-list-ul"></i></button>
                        <button type="button" class="be-tool-btn" data-cmd="insertOrderedList" title="Numbered List"><i class="fa-solid fa-list-ol"></i></button>
                        <span class="be-tool-sep"></span>
                        <button type="button" class="be-tool-btn" data-cmd="createLink" title="Insert Link"><i class="fa-solid fa-link"></i></button>
                        <button type="button" class="be-tool-btn" data-cmd="insertImage" title="Insert Image"><i class="fa-solid fa-image"></i></button>
                        <button type="button" class="be-tool-btn" data-cmd="insertTable" title="Insert Table"><i class="fa-solid fa-table"></i></button>
                        <span class="be-tool-sep"></span>
                        <button type="button" class="be-tool-btn" data-cmd="codeBlock" title="Code Block"><i class="fa-solid fa-terminal"></i></button>
                        <button type="button" class="be-tool-btn" data-cmd="htmlBlock" title="HTML Block"><i class="fa-brands fa-html5"></i></button>
                    </div>
                </div>
                <div class="be-toolbar-right">
                    <button type="button" class="be-tool-btn" id="be-fullscreen-${this.textarea.id}" title="Fullscreen">
                        <i class="fa-solid fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="be-body" style="height: ${this.options.height}">
                <div class="be-visual-pane be-pane active" id="be-visual-${this.textarea.id}">
                    <div class="be-content-editable" contenteditable="true" id="be-editable-${this.textarea.id}"></div>
                </div>
                <div class="be-code-pane be-pane" id="be-code-${this.textarea.id}">
                    <div class="be-code-header">
                        <span><i class="fa-solid fa-code"></i> HTML</span>
                        <button type="button" class="be-copy-btn" title="Copy HTML">
                            <i class="fa-solid fa-copy"></i> Copy
                        </button>
                    </div>
                    <textarea class="be-code-editor" id="be-code-editor-${this.textarea.id}" spellcheck="false"></textarea>
                </div>
                <div class="be-preview-pane be-pane" id="be-preview-${this.textarea.id}">
                    <div class="be-preview-label"><i class="fa-solid fa-eye"></i> Live Preview</div>
                    <div class="be-preview-content" id="be-preview-content-${this.textarea.id}"></div>
                </div>
            </div>
            <div class="be-statusbar">
                <span class="be-status-mode" id="be-status-${this.textarea.id}">Visual Mode</span>
                <span class="be-status-chars" id="be-chars-${this.textarea.id}">0 characters</span>
            </div>
        `;

        // Move textarea inside container (hidden)
        this.textarea.style.display = 'none';
        this.container.appendChild(this.textarea);

        // Cache elements
        this.editable = document.getElementById(`be-editable-${this.textarea.id}`);
        this.codeEditor = document.getElementById(`be-code-editor-${this.textarea.id}`);
        this.previewContent = document.getElementById(`be-preview-content-${this.textarea.id}`);
        this.visualPane = document.getElementById(`be-visual-${this.textarea.id}`);
        this.codePane = document.getElementById(`be-code-${this.textarea.id}`);
        this.previewPane = document.getElementById(`be-preview-${this.textarea.id}`);
        this.statusMode = document.getElementById(`be-status-${this.textarea.id}`);
        this.statusChars = document.getElementById(`be-chars-${this.textarea.id}`);

        // Set initial content
        this.editable.innerHTML = this.textarea.value || '<p>Start writing here...</p>';
        this.codeEditor.value = this.textarea.value || '';

        // Bind events
        this.bindEvents();
        this.setMode(this.options.mode);
        this.updateCharCount();
    }

    bindEvents() {
        // Mode switcher
        this.container.querySelectorAll('.be-mode-btn').forEach(btn => {
            btn.addEventListener('click', () => this.setMode(btn.dataset.mode));
        });

        // Format tools
        this.container.querySelectorAll('.be-tool-btn[data-cmd]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.execCommand(btn.dataset.cmd);
            });
        });

        // Sync visual → code → textarea
        this.editable.addEventListener('input', () => {
            this.syncFromVisual();
            this.updateCharCount();
        });

        // Sync code → visual → textarea
        this.codeEditor.addEventListener('input', () => {
            this.syncFromCode();
            this.updateCharCount();
        });

        // Keyboard shortcuts in visual mode
        this.editable.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key.toLowerCase()) {
                    case 'b': e.preventDefault(); this.execCommand('bold'); break;
                    case 'i': e.preventDefault(); this.execCommand('italic'); break;
                    case 'k': e.preventDefault(); this.execCommand('createLink'); break;
                }
            }
            // Tab for code blocks
            if (e.key === 'Tab' && this.currentMode === 'code') {
                e.preventDefault();
                const start = this.codeEditor.selectionStart;
                const end = this.codeEditor.selectionEnd;
                this.codeEditor.value = this.codeEditor.value.substring(0, start) + '    ' + this.codeEditor.value.substring(end);
                this.codeEditor.selectionStart = this.codeEditor.selectionEnd = start + 4;
                this.syncFromCode();
            }
        });

        // Fullscreen
        const fsBtn = document.getElementById(`be-fullscreen-${this.textarea.id}`);
        if (fsBtn) {
            fsBtn.addEventListener('click', () => this.toggleFullscreen());
        }

        // Copy button
        this.container.querySelector('.be-copy-btn')?.addEventListener('click', () => {
            navigator.clipboard.writeText(this.codeEditor.value).then(() => {
                const btn = this.container.querySelector('.be-copy-btn');
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
                setTimeout(() => btn.innerHTML = orig, 2000);
            });
        });

        // Paste handling — clean paste in visual mode
        this.editable.addEventListener('paste', (e) => {
            // Allow HTML paste but clean it
            const html = e.clipboardData.getData('text/html');
            const text = e.clipboardData.getData('text/plain');
            
            if (html) {
                e.preventDefault();
                // Clean HTML - remove styles, scripts, etc
                const cleaned = this.cleanPastedHTML(html);
                document.execCommand('insertHTML', false, cleaned);
                this.syncFromVisual();
            }
        });
    }

    setMode(mode) {
        this.currentMode = mode;

        // Update buttons
        this.container.querySelectorAll('.be-mode-btn').forEach(b => b.classList.remove('active'));
        this.container.querySelector(`[data-mode="${mode}"]`)?.classList.add('active');

        // Show/hide panes
        this.visualPane.classList.remove('active');
        this.codePane.classList.remove('active');
        this.previewPane.classList.remove('active');

        // Show/hide format tools
        const formatTools = this.container.querySelector('.be-format-tools');

        switch(mode) {
            case 'visual':
                this.visualPane.classList.add('active');
                formatTools.style.display = 'flex';
                this.statusMode.textContent = 'Visual Mode';
                this.syncFromCode(); // Update visual from code
                break;
            case 'code':
                this.codePane.classList.add('active');
                formatTools.style.display = 'none';
                this.statusMode.textContent = 'Code Mode (HTML)';
                this.syncFromVisual(); // Update code from visual
                break;
            case 'split':
                this.codePane.classList.add('active');
                this.previewPane.classList.add('active');
                formatTools.style.display = 'none';
                this.statusMode.textContent = 'Split Mode';
                this.syncFromVisual();
                this.updatePreview();
                break;
        }
    }

    execCommand(cmd) {
        this.editable.focus();

        switch(cmd) {
            case 'heading':
                const tag = prompt('Heading level (1-6):', '2');
                if (tag && tag >= 1 && tag <= 6) {
                    document.execCommand('formatBlock', false, `h${tag}`);
                }
                break;
            case 'createLink':
                const url = prompt('Enter URL:', 'https://');
                if (url) document.execCommand('createLink', false, url);
                break;
            case 'insertImage':
                const src = prompt('Enter image URL:', 'https://');
                if (src) document.execCommand('insertImage', false, src);
                break;
            case 'insertTable':
                const rows = prompt('Number of rows:', '3');
                const cols = prompt('Number of columns:', '3');
                if (rows && cols) {
                    let table = '<table class="content-table"><thead><tr>';
                    for (let c = 0; c < cols; c++) table += '<th>Header</th>';
                    table += '</tr></thead><tbody>';
                    for (let r = 0; r < rows - 1; r++) {
                        table += '<tr>';
                        for (let c = 0; c < cols; c++) table += '<td>Cell</td>';
                        table += '</tr>';
                    }
                    table += '</tbody></table>';
                    document.execCommand('insertHTML', false, table);
                }
                break;
            case 'codeBlock':
                const code = prompt('Paste your code:');
                if (code) {
                    const escaped = code.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    document.execCommand('insertHTML', false, 
                        `<pre class="code-block"><code>${escaped}</code></pre><p></p>`);
                }
                break;
            case 'htmlBlock':
                const html = prompt('Paste HTML code (will be rendered):');
                if (html) {
                    document.execCommand('insertHTML', false, 
                        `<div class="html-block">${html}</div><p></p>`);
                }
                break;
            default:
                document.execCommand(cmd, false, null);
        }

        this.syncFromVisual();
    }

    syncFromVisual() {
        const html = this.editable.innerHTML;
        this.codeEditor.value = this.formatHTML(html);
        this.textarea.value = html;
        if (this.currentMode === 'split') this.updatePreview();
    }

    syncFromCode() {
        const html = this.codeEditor.value;
        this.editable.innerHTML = html;
        this.textarea.value = html;
        if (this.currentMode === 'split') this.updatePreview();
    }

    updatePreview() {
        this.previewContent.innerHTML = this.codeEditor.value;
    }

    updateCharCount() {
        const text = this.editable.textContent || '';
        this.statusChars.textContent = `${text.length} characters`;
    }

    toggleFullscreen() {
        this.container.classList.toggle('be-fullscreen');
        const icon = this.container.querySelector(`#be-fullscreen-${this.textarea.id} i`);
        if (this.container.classList.contains('be-fullscreen')) {
            icon.className = 'fa-solid fa-compress';
            document.body.style.overflow = 'hidden';
        } else {
            icon.className = 'fa-solid fa-expand';
            document.body.style.overflow = '';
        }
    }

    formatHTML(html) {
        // Simple HTML formatter for code view readability
        let formatted = html;
        formatted = formatted.replace(/></g, '>\n<');
        formatted = formatted.replace(/\n{3,}/g, '\n\n');
        return formatted.trim();
    }

    cleanPastedHTML(html) {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        // Remove scripts
        temp.querySelectorAll('script, style, link, meta').forEach(el => el.remove());
        // Remove all style attributes
        temp.querySelectorAll('[style]').forEach(el => el.removeAttribute('style'));
        // Remove class attributes except our own
        temp.querySelectorAll('[class]').forEach(el => {
            if (!el.className.startsWith('content-') && !el.className.startsWith('code-')) {
                el.removeAttribute('class');
            }
        });
        return temp.innerHTML;
    }

    // Get the HTML content
    getContent() {
        return this.textarea.value;
    }

    // Set content programmatically
    setContent(html) {
        this.textarea.value = html;
        this.editable.innerHTML = html;
        this.codeEditor.value = html;
    }
}

// Auto-initialize editors on page load
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('textarea.wysiwyg').forEach(textarea => {
        new BeaconEditor(textarea.id, {
            height: textarea.dataset.height || '400px',
            mode: textarea.dataset.mode || 'visual'
        });
    });
});
