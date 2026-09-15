<div
    wire:ignore
    x-data="issueboardRichTextEditor(@entangle($attributes->wire('model')), @js(__('app.editor.link_prompt')))"
    x-init="init()"
    @if ($focusOnComment) x-on:focus-comment-box.window="$nextTick(() => focus())" @endif
    class="mt-2"
>
    <div class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm transition focus-within:border-[#ff9200] focus-within:ring-2 focus-within:ring-[#ff9200]/20">
        <div x-on:mousedown.prevent class="flex flex-wrap items-center gap-1 border-b border-slate-200 bg-slate-50 px-2 py-2">
            <button type="button" x-on:click="format('bold')" class="issueboard-editor-button" aria-label="{{ __('app.editor.bold') }}">
                <span class="font-black">B</span>
            </button>
            <button type="button" x-on:click="format('italic')" class="issueboard-editor-button" aria-label="{{ __('app.editor.italic') }}">
                <span class="font-serif italic">I</span>
            </button>
            <button type="button" x-on:click="format('underline')" class="issueboard-editor-button" aria-label="{{ __('app.editor.underline') }}">
                <span class="underline">U</span>
            </button>

            <span class="mx-1 h-5 w-px bg-slate-200" aria-hidden="true"></span>

            <button type="button" x-on:click="format('insertUnorderedList')" class="issueboard-editor-button" aria-label="{{ __('app.editor.bullet_list') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01"/>
                </svg>
            </button>
            <button type="button" x-on:click="format('insertOrderedList')" class="issueboard-editor-button" aria-label="{{ __('app.editor.numbered_list') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6h10M10 12h10M10 18h10M4 6h1M4 12h1.5L4 14m0 4h2l-2 2h2"/>
                </svg>
            </button>
            <button type="button" x-on:click="format('formatBlock', 'blockquote')" class="issueboard-editor-button" aria-label="{{ __('app.editor.quote') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h3v7.5h-6v-6a4.5 4.5 0 0 1 4.5-4.5M16.5 8.25h3v7.5h-6v-6a4.5 4.5 0 0 1 4.5-4.5"/>
                </svg>
            </button>

            <span class="mx-1 h-5 w-px bg-slate-200" aria-hidden="true"></span>

            <button type="button" x-on:click="createLink()" class="issueboard-editor-button" aria-label="{{ __('app.editor.add_link') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 6.364 6.364l-1.768 1.768a4.5 4.5 0 0 1-6.364 0M10.81 15.312a4.5 4.5 0 0 1-6.364-6.364L6.214 7.18a4.5 4.5 0 0 1 6.364 0"/>
                </svg>
            </button>
            <button type="button" x-on:click="clearFormatting()" class="issueboard-editor-button" aria-label="{{ __('app.editor.clear_formatting') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75 14.25 12m0 0 2.25 2.25M14.25 12l2.25-2.25M14.25 12 12 14.25M7.5 4.5h9M9.75 4.5 6 19.5"/>
                </svg>
            </button>
        </div>

        <div
            id="{{ $id }}"
            x-ref="editor"
            x-on:input="syncFromEditor()"
            x-on:blur="syncFromEditor()"
            x-on:paste.prevent="pastePlainText($event)"
            contenteditable="true"
            role="textbox"
            aria-multiline="true"
            data-placeholder="{{ $placeholder }}"
            class="issueboard-editor-canvas {{ $minHeight }}"
        ></div>
    </div>
</div>
