<form
    class="evo-ui-form-surface evo-ui-form-surface--density-compact evo-ui-form-surface--layout-settings"
    x-on:submit.prevent="submitSave()"
    x-data="{
        localDirty: @js($dirty),
        savedClean: @js($saved),
        saving: false,
        savedFeedback: false,
        savedFeedbackTimer: null,
        cleanSnapshot: '',
        suppressDirtyUntil: 0,
        submitSave() {
            if (this.saving || !this.localDirty || this.savedClean) {
                return;
            }

            this.saving = true;
            const syncEditors = window.EvoUI?.syncRichEditors
                ? window.EvoUI.syncRichEditors(this.$el, this.$wire)
                : Promise.resolve();

            Promise.resolve(syncEditors)
                .then(() => this.$wire.save())
                .catch(() => {
                    this.saving = false;
                });
        },
        trackedFields() {
            return Array.from(this.$el.querySelectorAll('input, textarea, select'))
                .filter((field) => !field.disabled && !field.closest('.evo-ui-modal'));
        },
        fieldSnapshot() {
            return JSON.stringify(this.trackedFields()
                .map((field) => [
                    field.type,
                    field.dataset.dirtyKey || field.name || field.id || '',
                    field.tagName === 'SELECT' && field.multiple
                        ? Array.from(field.selectedOptions).map((option) => option.value).join('||')
                        : (field.type === 'checkbox' || field.type === 'radio' ? field.checked : field.value)
                ])
                .sort((left, right) => JSON.stringify(left).localeCompare(JSON.stringify(right))));
        },
        forceClean() {
            this.localDirty = false;
            this.$el.setAttribute('data-evo-form-dirty', 'false');
        },
        markDirty() {
            if (Date.now() < this.suppressDirtyUntil) {
                this.forceClean();
                return;
            }

            this.saving = false;
            this.savedFeedback = false;
            if (this.savedFeedbackTimer) {
                window.clearTimeout(this.savedFeedbackTimer);
            }
            this.savedClean = false;
            this.$el.setAttribute('data-evo-form-saved', 'false');

            this.$nextTick(() => {
                this.localDirty = this.fieldSnapshot() !== this.cleanSnapshot;
            });
        },
        markSaved() {
            this.saving = false;
            this.suppressDirtyUntil = Date.now() + 800;
            this.forceClean();
            this.savedClean = true;
            this.$el.setAttribute('data-evo-form-saved', 'true');
            this.savedFeedback = true;
            if (this.savedFeedbackTimer) {
                window.clearTimeout(this.savedFeedbackTimer);
            }
            this.savedFeedbackTimer = window.setTimeout(() => {
                this.savedFeedback = false;
            }, 1600);
            this.$nextTick(() => window.setTimeout(() => {
                this.cleanSnapshot = this.fieldSnapshot();
                this.forceClean();
                this.savedClean = true;
                this.$el.setAttribute('data-evo-form-saved', 'true');
            }, 0));
            window.setTimeout(() => {
                this.cleanSnapshot = this.fieldSnapshot();
                this.forceClean();
                this.savedClean = true;
                this.$el.setAttribute('data-evo-form-saved', 'true');
                this.suppressDirtyUntil = 0;
            }, 800);
        },
        init() {
            this.$nextTick(() => {
                this.cleanSnapshot = this.fieldSnapshot();
                this.$el.setAttribute('data-evo-form-dirty', this.localDirty ? 'true' : 'false');
            });
        }
    }"
    x-on:input="markDirty()"
    x-on:change="markDirty()"
    x-on:evo-ui:form.saved.window="markSaved()"
    data-evo-form
    data-evo-form-dirty="{{ $dirty ? 'true' : 'false' }}"
    data-evo-form-saved="{{ $saved ? 'true' : 'false' }}"
>
    <div class="evo-ui-form-heading">
        <div></div>
        <div class="evo-ui-form-toolbar" aria-label="@lang('evo::global.form_actions')">
            <button
                class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled"
                type="submit"
                x-bind:disabled="(typeof saving !== 'undefined' && saving) || (typeof savedFeedback !== 'undefined' && savedFeedback) || !(typeof localDirty !== 'undefined' && localDirty) || (typeof savedClean !== 'undefined' && savedClean)"
                x-bind:class="{ 'is-disabled': (typeof saving !== 'undefined' && saving) || (typeof savedFeedback !== 'undefined' && savedFeedback) || !(typeof localDirty !== 'undefined' && localDirty) || (typeof savedClean !== 'undefined' && savedClean), 'is-saved': (typeof savedFeedback !== 'undefined' && savedFeedback) }"
                x-bind:title="(typeof savedFeedback !== 'undefined' && savedFeedback) ? @js(__('evo::global.form_saved')) : @js(__('evo::global.action_save'))"
                x-bind:aria-label="(typeof savedFeedback !== 'undefined' && savedFeedback) ? @js(__('evo::global.form_saved')) : @js(__('evo::global.action_save'))"
                @disabled(!$dirty)
                wire:loading.attr="disabled"
                wire:loading.class="is-disabled"
                wire:target="save"
            >
                <x-evo::icon name="check" class="evo-ui-btn__icon" x-show="!(typeof savedFeedback !== 'undefined' && savedFeedback)" />
                <x-evo::icon name="circle-check" class="evo-ui-btn__icon" x-show="typeof savedFeedback !== 'undefined' && savedFeedback" x-cloak />
                <span class="evo-ui-btn__label">@lang('evo::global.action_save')</span>
            </button>
        </div>
    </div>

    @php
        $activeFields = (array) data_get($tabs, $activeTab . '.fields', []);
    @endphp

    <div class="evo-ui-settings-values">
        @forelse($activeFields as $field)
            @php
                $type = $catalog->normalizeType((string) ($field['type'] ?? 'text'));
                $fieldKey = (string) ($field['name'] ?? '');
                $name = 'sset_' . $fieldKey;
                $label = trim((string) __($field['label'] ?: 'sSettings::global.no_title'));
                $description = trim((string) __($field['description'] ?? ''));
                $inputId = 'ssettings-' . $name;
                $usage = "{{ evo()->getConfig('" . $name . "', '') }}";
                $options = $catalog->parseOptions($field['options'] ?? '');
            @endphp

            @if($type === 'divider')
                <div class="evo-ui-settings-divider">
                    <span>{{ $label }}</span>
                </div>
            @else
                <div class="evo-ui-settings-row {{ in_array($type, ['textarea', 'textareamini', 'richtext'], true) ? 'evo-ui-settings-row--textarea' : '' }}" data-evo-settings-row>
                    <div class="evo-ui-settings-row__meta">
                        <label class="evo-ui-settings-row__label" for="{{ $inputId }}">{{ $label }}</label>
                        <code class="evo-ui-settings-row__usage">{{ $usage }}</code>
                    </div>

                    <div class="evo-ui-settings-row__control">
                        @if($type === 'checkbox')
                            <label class="evo-ui-checkbox">
                                <input type="checkbox" wire:model="data.{{ $name }}">
                                <span>{{ $description ?: $label }}</span>
                            </label>
                        @elseif($type === 'checkboxgroup')
                            <div class="evo-ui-option-stack" id="{{ $inputId }}">
                                @forelse($options as $option)
                                    <label class="evo-ui-option-choice">
                                        <input type="checkbox" value="{{ $option['value'] }}" wire:model="data.{{ $name }}">
                                        <span>{{ __($option['label']) }}</span>
                                    </label>
                                @empty
                                    <span class="evo-ui-option-empty">@lang('sSettings::global.no_options')</span>
                                @endforelse
                            </div>
                        @elseif($type === 'radio')
                            <div class="evo-ui-option-stack" id="{{ $inputId }}">
                                @forelse($options as $option)
                                    <label class="evo-ui-option-choice">
                                        <input type="radio" name="{{ $inputId }}" value="{{ $option['value'] }}" wire:model="data.{{ $name }}">
                                        <span>{{ __($option['label']) }}</span>
                                    </label>
                                @empty
                                    <span class="evo-ui-option-empty">@lang('sSettings::global.no_options')</span>
                                @endforelse
                            </div>
                        @elseif($type === 'dropdown' || $type === 'listbox')
                            <select
                                id="{{ $inputId }}"
                                class="evo-ui-input {{ $type === 'listbox' ? 'evo-ui-select--listbox' : '' }}"
                                wire:model="data.{{ $name }}"
                                @if($type === 'listbox') size="{{ min(max(count($options), 5), 10) }}" @endif
                            >
                                <option value=""></option>
                                @foreach($options as $option)
                                    <option value="{{ $option['value'] }}">{{ __($option['label']) }}</option>
                                @endforeach
                            </select>
                        @elseif($type === 'listboxmultiple')
                            <select
                                id="{{ $inputId }}"
                                class="evo-ui-input evo-ui-select--multiple"
                                wire:model="data.{{ $name }}"
                                multiple
                                size="{{ min(max(count($options), 5), 10) }}"
                            >
                                @foreach($options as $option)
                                    <option value="{{ $option['value'] }}">{{ __($option['label']) }}</option>
                                @endforeach
                            </select>
                        @elseif($type === 'richtext')
                            @php
                                $richEditorHtml = class_exists(\EvoUI\Support\RichTextEditor::class)
                                    ? \EvoUI\Support\RichTextEditor::html($inputId, '260px')
                                    : '';
                            @endphp
                            <div
                                class="evo-ui-editor-field"
                                wire:ignore
                                wire:key="ssettings-richtext-{{ $name }}"
                                x-init="$nextTick(() => window.EvoUI?.initRichEditorField?.($el))"
                            >
                                <textarea
                                    id="{{ $inputId }}"
                                    class="evo-ui-input evo-ui-textarea evo-ui-textarea--editor"
                                    rows="7"
                                    data-ssettings-richtext
                                    data-evo-rich-editor
                                    data-evo-rich-editor-model="data.{{ $name }}"
                                >{{ $data[$name] ?? '' }}</textarea>
                                {!! $richEditorHtml !!}
                            </div>
                        @elseif($type === 'textarea' || $type === 'textareamini')
                            <textarea
                                id="{{ $inputId }}"
                                class="evo-ui-input evo-ui-textarea"
                                rows="{{ $type === 'textareamini' ? 2 : 7 }}"
                                wire:model="data.{{ $name }}"
                            ></textarea>
                        @elseif($type === 'image' || $type === 'file')
                            <span class="evo-ui-media-field">
                                <input
                                    id="{{ $inputId }}"
                                    class="evo-ui-input"
                                    type="text"
                                    wire:model="data.{{ $name }}"
                                >
                                <button
                                    type="button"
                                    class="evo-ui-btn evo-ui-btn--icon evo-ui-media-field__button"
                                    title="@lang('sSettings::global.select_file')"
                                    aria-label="@lang('sSettings::global.select_file')"
                                    onclick="window.EvoUI?.browseMediaField(@js($inputId), @js($type === 'image' ? 'images' : 'files'))"
                                >
                                    <x-evo::icon :name="$type === 'image' ? 'image' : 'file'" />
                                </button>
                                @if($type === 'image' && !empty($data[$name]))
                                    <span class="evo-ui-image-preview" style="background-image: url('{{ str_starts_with((string) $data[$name], 'http') ? $data[$name] : EVO_SITE_URL . ltrim((string) $data[$name], '/') }}')"></span>
                                @endif
                            </span>
                        @else
                            <input
                                id="{{ $inputId }}"
                                class="evo-ui-input"
                                type="{{ in_array($type, ['url', 'email', 'number', 'date'], true) ? $type : 'text' }}"
                                wire:model="data.{{ $name }}"
                            >
                        @endif

                        @if($description && $type !== 'checkbox')
                            <p class="evo-ui-settings-row__description">{{ $description }}</p>
                        @endif
                    </div>
                </div>
            @endif
        @empty
            <div class="evo-ui-empty">@lang('sSettings::global.no_fields')</div>
        @endforelse
    </div>

</form>
