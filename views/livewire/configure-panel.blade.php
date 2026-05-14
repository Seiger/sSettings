<form
    class="evo-ui-form-surface evo-ui-form-surface--density-compact evo-ui-form-surface--layout-settings"
    x-on:submit.prevent="submitSave()"
    x-data="{
        localDirty: @js($dirty),
        savedClean: @js($saved),
        saving: false,
        savedFeedback: false,
        savedFeedbackTimer: null,
        saveCleanupTimer: null,
        structureDirty: false,
        cleanSnapshot: '',
        suppressDirtyUntil: 0,
        submitSave() {
            if (this.saving || !this.localDirty || this.savedClean || @js(!$writable)) {
                return;
            }

            this.saving = true;
            Promise.resolve(this.$wire.save())
                .then((saved) => {
                    if (saved === false) {
                        this.saving = false;
                        this.savedClean = false;
                        this.$el.setAttribute('data-evo-form-saved', 'false');
                        return;
                    }

                    this.completeSave();
                })
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
                    field.type === 'checkbox' || field.type === 'radio' ? field.checked : field.value
                ]));
        },
        syncDirty() {
            this.localDirty = this.structureDirty || this.fieldSnapshot() !== this.cleanSnapshot;
            this.$el.setAttribute('data-evo-form-dirty', this.localDirty ? 'true' : 'false');

            if (!this.localDirty) {
                this.savedClean = true;
                this.$el.setAttribute('data-evo-form-saved', 'true');
            }
        },
        forceClean() {
            this.structureDirty = false;
            this.localDirty = false;
            this.$el.setAttribute('data-evo-form-dirty', 'false');
        },
        markDirty(event = null) {
            if (event?.target?.closest?.('.evo-ui-modal')) {
                return;
            }

            if (Date.now() < this.suppressDirtyUntil) {
                this.forceClean();
                this.savedClean = true;
                this.$el.setAttribute('data-evo-form-saved', 'true');
                return;
            }

            this.saving = false;
            this.savedFeedback = false;
            if (this.savedFeedbackTimer) {
                window.clearTimeout(this.savedFeedbackTimer);
            }
            this.savedClean = false;
            this.$el.setAttribute('data-evo-form-saved', 'false');

            this.$nextTick(() => this.syncDirty());
        },
        markStructureDirty() {
            if (Date.now() < this.suppressDirtyUntil) {
                return;
            }

            this.saving = false;
            this.savedFeedback = false;
            if (this.savedFeedbackTimer) {
                window.clearTimeout(this.savedFeedbackTimer);
            }
            this.savedClean = false;
            this.$el.setAttribute('data-evo-form-saved', 'false');

            this.structureDirty = true;
            this.localDirty = true;
            this.$el.setAttribute('data-evo-form-dirty', 'true');
        },
        completeSave() {
            this.saving = false;
            this.suppressDirtyUntil = Date.now() + 800;
            this.cleanSnapshot = this.fieldSnapshot();
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

            if (this.saveCleanupTimer) {
                window.clearTimeout(this.saveCleanupTimer);
            }
            this.saveCleanupTimer = window.setTimeout(() => {
                this.cleanSnapshot = this.fieldSnapshot();
                this.forceClean();
                this.savedClean = true;
                this.$el.setAttribute('data-evo-form-saved', 'true');
                this.suppressDirtyUntil = 0;
                this.saveCleanupTimer = null;
            }, 800);
        },
        markSaved() {
            this.completeSave();
        },
        init() {
            this.$nextTick(() => {
                this.cleanSnapshot = this.fieldSnapshot();
                this.$el.setAttribute('data-evo-form-dirty', this.localDirty ? 'true' : 'false');
            });
        }
    }"
    x-on:input="markDirty($event)"
    x-on:change="markDirty($event)"
    x-on:ssettings-dirty.window="markStructureDirty()"
    x-on:evo-ui:form.saved.window="markSaved()"
    data-evo-form
    data-evo-inline-create="ssettings-configure"
    data-evo-form-dirty="{{ $dirty ? 'true' : 'false' }}"
    data-evo-form-saved="{{ $saved ? 'true' : 'false' }}"
>
    <div class="evo-ui-builder-toolbar" aria-label="@lang('evo::global.form_actions')">
        <button
            type="button"
            class="evo-ui-btn evo-ui-btn--icon evo-ui-btn--success"
            title="@lang('sSettings::global.add_new_tab')"
            aria-label="@lang('sSettings::global.add_new_tab')"
            wire:click="addTab"
            data-ssettings-add-tab-button
        >
            <x-evo::icon name="plus" />
        </button>

        <div class="evo-ui-form-toolbar evo-ui-builder-toolbar__right">
            <button
                class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled"
                type="submit"
                x-bind:disabled="(typeof saving !== 'undefined' && saving) || (typeof savedFeedback !== 'undefined' && savedFeedback) || !(typeof localDirty !== 'undefined' && localDirty) || (typeof savedClean !== 'undefined' && savedClean) || @js(!$writable)"
                x-bind:class="{ 'is-disabled': (typeof saving !== 'undefined' && saving) || (typeof savedFeedback !== 'undefined' && savedFeedback) || !(typeof localDirty !== 'undefined' && localDirty) || (typeof savedClean !== 'undefined' && savedClean) || @js(!$writable), 'is-saved': (typeof savedFeedback !== 'undefined' && savedFeedback) }"
                x-bind:title="(typeof savedFeedback !== 'undefined' && savedFeedback) ? @js(__('evo::global.form_saved')) : @js(__('evo::global.action_save'))"
                x-bind:aria-label="(typeof savedFeedback !== 'undefined' && savedFeedback) ? @js(__('evo::global.form_saved')) : @js(__('evo::global.action_save'))"
                @disabled(!$dirty || !$writable)
                wire:loading.attr="disabled"
                wire:loading.class="is-disabled"
                wire:target="save,sortFieldByUid,sortTabByUid,moveFieldStep,moveTab,addField,addFieldAfter,addTab,addTabAfter,removeField,removeTab,commitFieldEdit,updateFieldByUid,updateTabLabelByUid,clearOpenField"
            >
                <x-evo::icon name="check" class="evo-ui-btn__icon" x-show="!(typeof savedFeedback !== 'undefined' && savedFeedback)" />
                <x-evo::icon name="circle-check" class="evo-ui-btn__icon" x-show="typeof savedFeedback !== 'undefined' && savedFeedback" x-cloak />
                <span class="evo-ui-btn__label">@lang('evo::global.action_save')</span>
            </button>
        </div>
    </div>

    @if(!$writable)
        <div class="evo-ui-alert evo-ui-alert--danger">@lang('sSettings::global.not_writable')</div>
    @endif

    @if($error)
        <div class="evo-ui-alert evo-ui-alert--danger">{{ $error }}</div>
    @endif

    <div
        class="evo-ui-builder evo-ui-dnd evo-ui-inline-create"
        data-evo-dnd
        data-evo-dnd-group-method="sortTabByUid"
        data-evo-dnd-item-method="sortFieldByUid"
        data-evo-dnd-option-row-selector="[data-ssettings-parent-option-row-disabled]"
        x-init="$nextTick(() => window.EvoUI?.initDnd?.($el))"
        x-on:evo-ui:form-dirty="markStructureDirty()"
    >
        @foreach($tabs as $tabIndex => $tab)
            <section
                class="evo-ui-dnd-group-row evo-ui-dnd-group-row--nested"
                wire:key="ssettings-tab-{{ $tab['_uid'] ?? $tabIndex }}"
                data-evo-dnd-group
                data-evo-dnd-uid="{{ $tab['_uid'] ?? '' }}"
                data-evo-dnd-group-uid="{{ $tab['_uid'] ?? '' }}"
                data-evo-inline-created="{{ $tab['_uid'] ?? '' }}"
                data-evo-inline-create-id="{{ $tab['_uid'] ?? '' }}"
                data-index="{{ $tabIndex }}"
                draggable="true"
            >
                <header class="evo-ui-dnd-group-header">
                    <x-evo::reorder-rail
                        :move-up="'moveTab(' . $tabIndex . ', -1)'"
                        :move-down="'moveTab(' . $tabIndex . ', 1)'"
                        :up-disabled="$tabIndex === 0"
                        :down-disabled="$tabIndex >= count($tabs) - 1"
                        :label="__('sSettings::global.drag_to_reorder')"
                    />

                    <label class="evo-ui-dnd-inline-field evo-ui-dnd-inline-field--label">
                        <span>@lang('sSettings::global.tab_label')</span>
                        <input
                            class="evo-ui-input"
                            type="text"
                            value="{{ $tab['label'] ?? '' }}"
                            data-ssettings-dirty-input
                            data-evo-inline-focus
                            data-dirty-key="tab-label-{{ $tab['key'] ?? $tabIndex }}-{{ $tabIndex }}"
                            x-on:change="$wire.updateTabLabelByUid(@js($tab['_uid'] ?? ''), $event.target.value)"
                        >
                    </label>

                    <div class="evo-ui-row-actions evo-ui-row-actions--compact">
                        <button type="button" class="evo-ui-row-action evo-ui-row-action--success" title="@lang('sSettings::global.add_tab_after')" aria-label="@lang('sSettings::global.add_tab_after')" wire:click="addTabAfter({{ $tabIndex }})">
                            <x-evo::icon name="plus" />
                        </button>
                        <button type="button" class="evo-ui-row-action evo-ui-row-action--danger" title="@lang('global.remove')" aria-label="@lang('global.remove')" wire:click="removeTab({{ $tabIndex }})" @disabled(count($tabs) < 2)>
                            <x-evo::icon name="trash" />
                        </button>
                    </div>
                </header>

                <div
                    class="evo-ui-dnd-list"
                    data-evo-dnd-list
                    data-evo-dnd-group-uid="{{ $tab['_uid'] ?? '' }}"
                >
                    @forelse((array) ($tab['fields'] ?? []) as $fieldIndex => $field)
                        @php
                            $fieldName = (string) ($field['name'] ?? '');
                            $fieldLabel = trim((string) __($field['label'] ?? ''));
                            $fieldDescription = trim((string) __($field['description'] ?? ''));
                            $fieldType = (string) ($field['type'] ?? 'text');
                            $fieldUsage = '[(sset_' . $fieldName . ')]';
                            $fieldOptions = $catalog->parseOptions($field['options'] ?? '');
                        @endphp

                        <div
                            class="evo-ui-dnd-row evo-ui-dnd-row--with-badge"
                            wire:key="ssettings-field-{{ $field['_uid'] ?? ($tabIndex . '-' . $fieldIndex) }}"
                            x-data="{
                                open: @js($openFieldUid === ($field['_uid'] ?? '')),
                                name: @js($fieldName),
                                label: @js($fieldLabel),
                                description: @js($fieldDescription),
                                type: @js($fieldType),
                                optionTypes: @js($optionTypes),
                                options: @js($fieldOptions),
                                draftSnapshot: '',
                                init() {
                                    this.options = this.options.map((option) => ({
                                        _uid: option._uid || this.optionUid(),
                                        value: option.value || '',
                                        label: option.label || '',
                                    }));

                                    this.refreshDraftSnapshot();
                                },
                                optionUid() {
                                    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                                        return window.crypto.randomUUID();
                                    }

                                    return `option-${Date.now()}-${Math.random().toString(36).slice(2)}`;
                                },
                                createOption() {
                                    return { _uid: this.optionUid(), value: '', label: '' };
                                },
                                usesOptions() {
                                    return this.optionTypes.includes(this.type);
                                },
                                optionPayload() {
                                    return this.usesOptions()
                                        ? this.options.map((option) => ({
                                            _uid: option._uid || '',
                                            value: option.value || '',
                                            label: option.label || '',
                                        }))
                                        : [];
                                },
                                draftPayload() {
                                    return JSON.stringify({
                                        name: this.name || '',
                                        label: this.label || '',
                                        description: this.description || '',
                                        type: this.type || '',
                                        options: this.optionPayload(),
                                    });
                                },
                                refreshDraftSnapshot() {
                                    this.draftSnapshot = this.draftPayload();
                                },
                                isDraftDirty() {
                                    return this.draftPayload() !== this.draftSnapshot;
                                },
                                ensureOptionRow() {
                                    if (this.usesOptions() && this.options.length === 0) {
                                        this.options.push(this.createOption());
                                    }
                                },
                                addOption(index = null) {
                                    const position = Number.isInteger(index) ? index + 1 : this.options.length;
                                    this.options.splice(position, 0, this.createOption());
                                },
                                removeOption(index) {
                                    this.options.splice(index, 1);
                                    this.ensureOptionRow();
                                },
                                moveOption(index, direction) {
                                    const to = index + direction;

                                    if (to < 0 || to >= this.options.length) {
                                        return;
                                    }

                                    const [option] = this.options.splice(index, 1);
                                    this.options.splice(to, 0, option);
                                },
                                moveOptionByUid(uid, position) {
                                    const from = this.options.findIndex((option) => option._uid === uid);
                                    const requested = Number(position);
                                    const to = Number.isFinite(requested)
                                        ? Math.max(0, Math.min(requested, this.options.length - 1))
                                        : from;

                                    if (!Number.isInteger(from) || from < 0 || from >= this.options.length) {
                                        return;
                                    }

                                    if (from === to) {
                                        return;
                                    }

                                    const [option] = this.options.splice(from, 1);
                                    this.options.splice(Math.max(0, Math.min(to, this.options.length)), 0, option);
                                },
                                applyFieldEdit() {
                                    if (!this.isDraftDirty()) {
                                        return;
                                    }

                                    Promise.resolve(this.$wire.updateFieldByUid(@js($field['_uid'] ?? ''), this.name, this.label, this.description, this.type, this.options))
                                        .then(() => {
                                            this.refreshDraftSnapshot();
                                            this.open = false;
                                        });
                                }
                            }"
                            data-evo-dnd-item
                            data-evo-dnd-uid="{{ $field['_uid'] ?? '' }}"
                            data-evo-dnd-item-uid="{{ $field['_uid'] ?? '' }}"
                            data-evo-inline-created="{{ $field['_uid'] ?? '' }}"
                            data-evo-inline-create-id="{{ $field['_uid'] ?? '' }}"
                            data-index="{{ $fieldIndex }}"
                            draggable="true"
                            x-bind:draggable="open ? 'false' : 'true'"
                        >
                            <x-evo::reorder-rail
                                :move-up="'moveFieldStep(' . $tabIndex . ', ' . $fieldIndex . ', -1)'"
                                :move-down="'moveFieldStep(' . $tabIndex . ', ' . $fieldIndex . ', 1)'"
                                :up-disabled="$tabIndex === 0 && $fieldIndex === 0"
                                :down-disabled="$tabIndex >= count($tabs) - 1 && $fieldIndex >= count((array) ($tab['fields'] ?? [])) - 1"
                                :label="__('sSettings::global.drag_to_reorder')"
                            />

                            <button type="button" class="evo-ui-dnd-summary" x-on:click="open = true">
                                <span class="evo-ui-dnd-summary__main">
                                    <span class="evo-ui-dnd-title" data-placeholder="@lang('sSettings::global.field_label')">{{ $fieldLabel }}</span>
                                    @if($fieldDescription)
                                        <span class="evo-ui-dnd-subtitle">{{ $fieldDescription }}</span>
                                    @endif
                                </span>
                                <code class="evo-ui-dnd-key">{{ $fieldUsage }}</code>
                            </button>

                            <span class="evo-ui-dnd-badge" title="{{ $types[$fieldType] ?? ucfirst($fieldType) }}">{{ $types[$fieldType] ?? ucfirst($fieldType) }}</span>

                            <div class="evo-ui-row-actions evo-ui-row-actions--compact">
                                <button type="button" class="evo-ui-row-action evo-ui-row-action--success" title="@lang('sSettings::global.add_field_after')" aria-label="@lang('sSettings::global.add_field_after')" wire:click="addFieldAfter({{ $tabIndex }}, {{ $fieldIndex }})">
                                    <x-evo::icon name="plus" />
                                </button>
                                <button type="button" class="evo-ui-row-action evo-ui-row-action--primary" title="@lang('sSettings::global.edit_field_settings')" aria-label="@lang('sSettings::global.edit_field_settings')" x-on:click="open = true">
                                    <x-evo::icon name="edit" />
                                </button>
                                <button type="button" class="evo-ui-row-action evo-ui-row-action--danger" title="@lang('global.remove')" aria-label="@lang('global.remove')" wire:click="removeField({{ $tabIndex }}, {{ $fieldIndex }})">
                                    <x-evo::icon name="trash" />
                                </button>
                            </div>

                            <div
                                class="evo-ui-modal-backdrop"
                                role="presentation"
                                x-cloak
                                x-show="open"
                                x-on:click.self="open = false; $wire.clearOpenField()"
                                x-on:keydown.escape.window="if (open) { open = false; $wire.clearOpenField() }"
                            >
                                <section class="evo-ui-modal evo-ui-modal--lg" role="dialog" aria-modal="true" x-on:click.stop>
                                    <header class="evo-ui-modal__header">
                                        <div class="evo-ui-modal__title">
                                            <code class="evo-ui-dnd-key">{{ $fieldUsage }}</code>
                                        </div>
                                        <button type="button" class="evo-ui-modal__close" title="@lang('evo::global.action_cancel')" aria-label="@lang('evo::global.action_cancel')" x-on:click="open = false; $wire.clearOpenField()">
                                            <x-evo::icon name="x" />
                                        </button>
                                    </header>
                                    <div class="evo-ui-modal__body evo-ui-modal__body--compact" data-ssettings-field-settings-modal>
                                        <label class="evo-ui-field evo-ui-field--full">
                                            <span class="evo-ui-field__label">@lang('sSettings::global.field_key')</span>
                                            <input class="evo-ui-input" type="text" x-model="name">
                                        </label>
                                        <label class="evo-ui-field evo-ui-field--full">
                                            <span class="evo-ui-field__label">@lang('sSettings::global.field_label')</span>
                                            <input class="evo-ui-input" type="text" x-model="label" data-evo-inline-focus>
                                        </label>
                                        <label class="evo-ui-field evo-ui-field--full">
                                            <span class="evo-ui-field__label">@lang('sSettings::global.field_description')</span>
                                            <input class="evo-ui-input" type="text" x-model="description">
                                        </label>
                                        <label class="evo-ui-field evo-ui-field--full">
                                            <span class="evo-ui-field__label">@lang('sSettings::global.field_type')</span>
                                            <select class="evo-ui-input" x-model="type" x-on:change="ensureOptionRow()">
                                                @foreach($types as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <div class="evo-ui-field evo-ui-field--full" x-show="usesOptions()" x-cloak>
                                            <span class="evo-ui-field__label">@lang('sSettings::global.field_options')</span>
                                            <div
                                                class="evo-ui-dnd evo-ui-dnd-option-list"
                                                data-evo-dnd
                                                data-evo-dnd-option-list
                                                x-init="$nextTick(() => window.EvoUI?.initDnd?.($el))"
                                                x-on:evo-ui:dnd-option-changed="moveOptionByUid($event.detail.uid, $event.detail.position)"
                                            >
                                                <template x-for="(option, optionIndex) in options" :key="option._uid || optionIndex">
                                                    <div
                                                        class="evo-ui-dnd-option-row"
                                                        data-evo-dnd-option-row
                                                        x-bind:data-evo-dnd-uid="option._uid"
                                                        draggable="false"
                                                    >
                                                        <div class="evo-ui-reorder-rail">
                                                            <button
                                                                type="button"
                                                                class="evo-ui-reorder-rail__button"
                                                                title="@lang('sSettings::global.move_up')"
                                                                aria-label="@lang('sSettings::global.move_up')"
                                                                x-on:click="moveOption(optionIndex, -1)"
                                                                x-bind:disabled="optionIndex === 0"
                                                            >
                                                                <x-evo::icon name="chevron-up" />
                                                            </button>
                                                            <span
                                                                role="button"
                                                                tabindex="0"
                                                                class="evo-ui-drag-handle"
                                                                title="@lang('sSettings::global.drag_to_reorder')"
                                                                aria-label="@lang('sSettings::global.drag_to_reorder')"
                                                                draggable="false"
                                                                data-evo-drag-handle
                                                                data-evo-dnd-handle
                                                            >
                                                                <x-evo::icon name="grip-vertical" />
                                                            </span>
                                                            <button
                                                                type="button"
                                                                class="evo-ui-reorder-rail__button"
                                                                title="@lang('sSettings::global.move_down')"
                                                                aria-label="@lang('sSettings::global.move_down')"
                                                                x-on:click="moveOption(optionIndex, 1)"
                                                                x-bind:disabled="optionIndex >= options.length - 1"
                                                            >
                                                                <x-evo::icon name="chevron-down" />
                                                            </button>
                                                        </div>
                                                        <div class="evo-ui-dnd-option-row__fields">
                                                            <input class="evo-ui-input" type="text" x-model="option.value" placeholder="@lang('sSettings::global.option_value')" draggable="false" data-evo-dnd-option-value>
                                                            <input class="evo-ui-input" type="text" x-model="option.label" placeholder="@lang('sSettings::global.option_label')" draggable="false" data-evo-dnd-option-label>
                                                        </div>
                                                        <div class="evo-ui-dnd-actions evo-ui-row-actions evo-ui-row-actions--compact">
                                                            <button
                                                                type="button"
                                                                class="evo-ui-row-action evo-ui-row-action--success"
                                                                title="@lang('sSettings::global.add_option')"
                                                                aria-label="@lang('sSettings::global.add_option')"
                                                                x-on:click="addOption(optionIndex)"
                                                            >
                                                                <x-evo::icon name="plus" />
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="evo-ui-row-action evo-ui-row-action--danger"
                                                                title="@lang('sSettings::global.remove_option')"
                                                                aria-label="@lang('sSettings::global.remove_option')"
                                                                x-on:click="removeOption(optionIndex)"
                                                            >
                                                                <x-evo::icon name="trash" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <footer class="evo-ui-modal__footer">
                                        <button
                                            type="button"
                                            class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled"
                                            x-bind:disabled="!isDraftDirty()"
                                            x-bind:class="{ 'is-disabled': !isDraftDirty() }"
                                            x-on:click="applyFieldEdit()"
                                        >
                                            <x-evo::icon name="check" />
                                            <span>@lang('evo::global.action_apply')</span>
                                        </button>
                                    </footer>
                                </section>
                            </div>
                        </div>

                    @empty
                        <div
                            class="evo-ui-dnd-empty"
                        >
                            <span>@lang('sSettings::global.no_fields')</span>
                            <button type="button" class="evo-ui-btn evo-ui-btn--icon evo-ui-btn--success" title="@lang('sSettings::global.add_new_field')" aria-label="@lang('sSettings::global.add_new_field')" wire:click="addField({{ $tabIndex }})">
                                <x-evo::icon name="plus" />
                            </button>
                        </div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</form>
