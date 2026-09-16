<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ $issue?->exists ? route('issueboard.show', $issue) : route('issueboard.index') }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-[#d97706]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
                {{ __('issueboard::issueboard.back_to_board') }}
            </a>
            <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                {{ $issue?->exists ? __('issueboard::issueboard.edit_issue') : __('issueboard::issueboard.new_issue') }}
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                {{ __('issueboard::issueboard.form_intro') }}
            </p>
        </div>

        @if (! $issue?->exists)
            <div class="inline-flex items-center gap-2 self-start rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-200">
                <span class="h-2.5 w-2.5 rounded-full bg-rose-600"></span>
                {{ __('issueboard::issueboard.starts_as_new') }}
            </div>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#fff4e5] text-[#d97706]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 3.94c.09-.54.56-.94 1.12-.94h1.08c.56 0 1.03.4 1.12.94l.2 1.2c.06.35.28.65.59.82.07.04.14.08.21.12.3.18.67.23 1 .1l1.13-.42a1.12 1.12 0 0 1 1.38.5l.54.94c.28.49.17 1.1-.26 1.46l-.92.78c-.27.23-.41.57-.4.92v.24c-.01.35.13.69.4.92l.92.78c.43.36.54.97.26 1.46l-.54.94c-.28.49-.87.7-1.38.5l-1.13-.42c-.33-.13-.7-.08-1 .1l-.21.12c-.31.17-.53.47-.59.82l-.2 1.2c-.09.54-.56.94-1.12.94h-1.08c-.56 0-1.03-.4-1.12-.94l-.2-1.2a1.15 1.15 0 0 0-.59-.82l-.21-.12c-.3-.18-.67-.23-1-.1l-1.13.42c-.51.2-1.1-.01-1.38-.5l-.54-.94a1.12 1.12 0 0 1 .26-1.46l.92-.78c.27-.23.41-.57.4-.92v-.24c.01-.35-.13-.69-.4-.92l-.92-.78A1.12 1.12 0 0 1 5.3 7.2l.54-.94c.28-.49.87-.7 1.38-.5l1.13.42c.33.13.7.08 1-.1l.21-.12c.31-.17.53-.47.59-.82l.19-1.2Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ __('issueboard::issueboard.sections.issue') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('issueboard::issueboard.sections.issue_help') }}</p>
                    </div>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <label for="title" class="block text-sm font-semibold text-slate-700">
                            {{ __('issueboard::issueboard.fields.title') }}
                            <span class="text-rose-500" aria-hidden="true">*</span>
                        </label>
                        <input id="title" type="text" wire:model="title" autofocus
                               placeholder="{{ __('issueboard::issueboard.placeholders.title') }}"
                               class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#ff9200] focus:bg-white focus:ring-[#ff9200]">
                        @error('title') <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="project" class="block text-sm font-semibold text-slate-700">
                                {{ __('issueboard::issueboard.fields.project') }}
                            </label>
                            @if (auth()->user()->canManageTeam())
                                <a href="{{ \Illuminate\Support\Facades\Route::has('projects.index') ? route('projects.index') : url('/projects') }}" target="_blank" class="text-xs font-semibold text-orange-600 hover:text-orange-700">
                                    + {{ __('app.projects.new_project') }}
                                </a>
                            @endif
                        </div>
                        <select id="project" wire:model="project_id"
                                class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#ff9200] focus:bg-white focus:ring-[#ff9200]">
                            <option value="">{{ __('issueboard::issueboard.all_projects') }}</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-slate-400">{{ __('issueboard::issueboard.project_help') }}</p>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-slate-700">
                            {{ __('issueboard::issueboard.fields.description') }}
                        </label>
                        <x-rich-text-editor
                            id="description"
                            wire:model="description"
                            :placeholder="__('issueboard::issueboard.placeholders.description')"
                            min-height="min-h-48"
                        />
                        @error('description') <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="solution" class="block text-sm font-semibold text-slate-700">
                            {{ __('issueboard::issueboard.fields.suggested_solution') }}
                            <span class="ml-1 text-xs font-normal text-slate-400">{{ __('issueboard::issueboard.optional') }}</span>
                        </label>
                        <x-rich-text-editor
                            id="solution"
                            wire:model="suggested_solution"
                            :placeholder="__('issueboard::issueboard.placeholders.solution')"
                            min-height="min-h-32"
                        />
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-[#0094af]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l9.9-9.9a3 3 0 0 1 4.243 4.243l-9.9 9.9a1.5 1.5 0 0 1-2.122-2.122l8.839-8.838"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ __('issueboard::issueboard.sections.context') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('issueboard::issueboard.sections.context_help') }}</p>
                    </div>
                </div>

                <div class="mt-6 space-y-6">
                    <div>
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-sm font-semibold text-slate-700">{{ __('issueboard::issueboard.fields.links') }}</p>
                            <button type="button" wire:click="addLink"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-[#007d94] hover:text-[#005f70]">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                                {{ __('issueboard::issueboard.add_link') }}
                            </button>
                        </div>

                        <div class="mt-3 space-y-3">
                            @foreach ($links as $index => $link)
                                <div wire:key="link-{{ $index }}" class="grid gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-[minmax(0,180px)_minmax(0,1fr)_auto]">
                                    <label>
                                        <span class="sr-only">{{ __('issueboard::issueboard.fields.link_label') }}</span>
                                        <input type="text" wire:model="links.{{ $index }}.label"
                                               placeholder="{{ __('issueboard::issueboard.fields.link_label') }}"
                                               class="w-full rounded-lg border-slate-300 bg-white text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                                    </label>
                                    <label>
                                        <span class="sr-only">{{ __('issueboard::issueboard.fields.links') }}</span>
                                        <input type="url" wire:model="links.{{ $index }}.url" placeholder="https://"
                                               class="w-full rounded-lg border-slate-300 bg-white text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                                    </label>
                                    <button type="button" wire:click="removeLink({{ $index }})"
                                            class="flex h-10 w-10 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-700"
                                            aria-label="{{ __('issueboard::issueboard.delete') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    @error("links.{$index}.url") <p class="text-xs font-medium text-rose-600 sm:col-span-3">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="files" class="block cursor-pointer rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-7 text-center transition hover:border-[#0094af] hover:bg-cyan-50/40">
                            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-white text-[#0094af] shadow-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v1.125A2.625 2.625 0 0 0 5.625 20.25h12.75A2.625 2.625 0 0 0 21 17.625V16.5M16.5 8.25 12 3.75m0 0-4.5 4.5M12 3.75V15"/>
                                </svg>
                            </span>
                            <span class="mt-3 block text-sm font-bold text-slate-700">{{ __('issueboard::issueboard.upload_title') }}</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">
                                {{ __('issueboard::issueboard.upload_hint', ['size' => round(config('issueboard.max_upload_kb') / 1024)]) }}
                            </span>
                            <input id="files" type="file" wire:model="files" multiple class="sr-only">
                        </label>
                        @error('files.*') <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror

                        <div wire:loading wire:target="files" class="mt-3 flex items-center gap-2 text-xs font-medium text-[#007d94]">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-[#0094af]"></span>
                            {{ __('issueboard::issueboard.uploading') }}
                        </div>

                        @if ($files)
                            <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                                @foreach ($files as $index => $file)
                                    <li wire:key="file-{{ $index }}" class="flex min-w-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600">
                                        <svg class="h-4 w-4 shrink-0 text-[#0094af]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l9.9-9.9"/>
                                        </svg>
                                        <span class="min-w-0 flex-1 truncate">{{ $file->getClientOriginalName() }}</span>
                                        <button type="button" wire:click="removeFile({{ $index }})"
                                                class="text-slate-400 hover:text-rose-700" aria-label="{{ __('issueboard::issueboard.delete') }}">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($issue?->exists && $issue->attachments->isNotEmpty())
                            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                @foreach ($issue->attachments as $attachment)
                                    <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white" wire:key="att-{{ $attachment->id }}">
                                        @if ($attachment->is_image)
                                            <img src="{{ $attachment->url }}" alt="{{ $attachment->original_name }}"
                                                 class="h-24 w-full object-cover">
                                        @else
                                            <div class="flex h-24 items-center justify-center bg-slate-50 px-2 text-center text-[11px] text-slate-600">
                                                {{ $attachment->original_name }}
                                            </div>
                                        @endif
                                        <button type="button" wire:click="removeAttachment({{ $attachment->id }})"
                                                class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-white/95 text-slate-500 shadow opacity-100 hover:text-rose-700 sm:opacity-0 sm:group-hover:opacity-100"
                                                aria-label="{{ __('issueboard::issueboard.delete') }}">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-5 lg:sticky lg:top-24">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">{{ __('issueboard::issueboard.sections.ownership') }}</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('issueboard::issueboard.sections.ownership_help') }}</p>
                    </div>
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="assignee" class="block text-xs font-bold uppercase tracking-wide text-slate-500">
                            {{ __('issueboard::issueboard.fields.assignee') }}
                        </label>
                        <select id="assignee" wire:model="assigned_to"
                                class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#ff9200] focus:bg-white focus:ring-[#ff9200]">
                            <option value="">{{ __('issueboard::issueboard.unassigned') }}</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="priority" class="block text-xs font-bold uppercase tracking-wide text-slate-500">
                                {{ __('issueboard::issueboard.priority_label') }}
                            </label>
                            <select id="priority" wire:model="priority"
                                    class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#ff9200] focus:bg-white focus:ring-[#ff9200]">
                                <option value="1">{{ __('issueboard::issueboard.priority.1') }}</option>
                                <option value="2">{{ __('issueboard::issueboard.priority.2') }}</option>
                                <option value="3">{{ __('issueboard::issueboard.priority.3') }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="due" class="block text-xs font-bold uppercase tracking-wide text-slate-500">
                                {{ __('issueboard::issueboard.due_date') }}
                            </label>
                            <input id="due" type="date" wire:model="due_date"
                                   class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#ff9200] focus:bg-white focus:ring-[#ff9200]">
                        </div>
                    </div>
                </div>
            </section>

            <fieldset class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <legend class="sr-only">{{ __('issueboard::issueboard.contact') }}</legend>
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.142-4.03 7.5-9 7.5a10.9 10.9 0 0 1-3.59-.59L3 20.25l1.53-4.08A6.62 6.62 0 0 1 3 12c0-4.142 4.03-7.5 9-7.5s9 3.358 9 7.5Z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">{{ __('issueboard::issueboard.contact') }}</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('issueboard::issueboard.contact_help') }}</p>
                    </div>
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="cname" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('issueboard::issueboard.fields.contact_name') }}</label>
                        <input id="cname" type="text" wire:model="contact_name"
                               class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#ff9200] focus:bg-white focus:ring-[#ff9200]">
                    </div>
                    <div>
                        <label for="cmail" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('issueboard::issueboard.fields.contact_email') }}</label>
                        <input id="cmail" type="email" wire:model="contact_email"
                               class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#ff9200] focus:bg-white focus:ring-[#ff9200]">
                        @error('contact_email') <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="cphone" class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('issueboard::issueboard.fields.contact_phone') }}</label>
                        <input id="cphone" type="text" wire:model="contact_phone"
                               class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#ff9200] focus:bg-white focus:ring-[#ff9200]">
                    </div>
                </div>
            </fieldset>

            <div class="rounded-2xl bg-slate-900 p-4 shadow-sm">
                <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#ff9200] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#e68200] disabled:cursor-wait disabled:opacity-60">
                    <svg class="h-4 w-4" wire:loading.remove wire:target="save" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75 10.5 18l9-13.5"/>
                    </svg>
                    <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    {{ __('issueboard::issueboard.save') }}
                </button>
                <a href="{{ route('issueboard.index') }}"
                   class="mt-2 inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-medium text-slate-300 hover:text-white">
                    {{ __('issueboard::issueboard.cancel') }}
                </a>
            </div>
        </aside>
    </div>
</div>
