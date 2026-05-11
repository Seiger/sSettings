<form
    class="evo-ui-form ssettings-configure ssettings-configure--compact"
    x-on:submit.prevent="submitSave()"
    x-data="{
        localDirty: @js($dirty),
        savedClean: @js($saved),
        saving: false,
        showSavedToast: false,
        savedToastTimer: null,
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
            this.savedClean = false;
            this.$el.setAttribute('data-evo-form-saved', 'false');

            this.$nextTick(() => this.syncDirty());
        },
        markStructureDirty() {
            if (Date.now() < this.suppressDirtyUntil) {
                return;
            }

            this.saving = false;
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
            this.showSavedToast = true;
            if (this.savedToastTimer) {
                window.clearTimeout(this.savedToastTimer);
            }
            this.savedToastTimer = window.setTimeout(() => {
                this.showSavedToast = false;
            }, 2400);

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
    x-bind:data-evo-form-dirty="localDirty ? 'true' : 'false'"
    x-bind:data-evo-form-saved="savedClean ? 'true' : 'false'"
    data-evo-form
    data-evo-form-dirty="{{ $dirty ? 'true' : 'false' }}"
    data-evo-form-saved="{{ $saved ? 'true' : 'false' }}"
    data-ssettings-compact-configure
>
    <div class="ssettings-configure-toolbar" aria-label="@lang('evo::global.form_actions')" data-ssettings-configure-toolbar>
        <button
            type="button"
            class="evo-ui-btn evo-ui-btn--icon evo-ui-btn--success ssettings-toolbar-add"
            title="@lang('sSettings::global.add_new_tab')"
            aria-label="@lang('sSettings::global.add_new_tab')"
            wire:click="addTab"
            data-ssettings-add-tab-button
        >
            <x-evo::icon name="plus" />
        </button>

        <div class="evo-ui-form-toolbar ssettings-toolbar ssettings-configure-toolbar__right">
            <button
                class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled"
                type="submit"
                x-bind:disabled="saving || !localDirty || savedClean || @js(!$writable)"
                x-bind:class="{ 'is-disabled': saving || !localDirty || savedClean || @js(!$writable) }"
                @disabled(!$dirty || !$writable)
                wire:loading.attr="disabled"
                wire:loading.class="is-disabled"
                wire:target="save,sortFieldByUid,sortTabByUid,moveFieldStep,moveTab,addField,addFieldAfter,addTab,addTabAfter,removeField,removeTab,commitFieldEdit,updateFieldByUid,updateTabLabelByUid,clearOpenField"
            >
                <x-evo::icon name="check" class="evo-ui-btn__icon" />
                <span class="evo-ui-btn__label">@lang('evo::global.action_save')</span>
            </button>
        </div>
    </div>

    <div class="evo-ui-save-toast evo-ui-save-toast--success" role="status" aria-live="polite" x-cloak x-show="showSavedToast" x-transition.opacity.duration.150ms>
        <span class="evo-ui-save-toast__content">
            <x-evo::icon name="circle-check" />
            <span>@lang('evo::global.form_saved')</span>
        </span>
    </div>

    @if(!$writable)
        <div class="evo-ui-alert evo-ui-alert--danger">@lang('sSettings::global.not_writable')</div>
    @endif

    @if($error)
        <div class="evo-ui-alert evo-ui-alert--danger">{{ $error }}</div>
    @endif

    <div
        class="ssettings-compact-configure evo-ui-dnd"
        data-evo-dnd
        data-evo-dnd-group-method="sortTabByUid"
        data-evo-dnd-item-method="sortFieldByUid"
        x-init="$nextTick(() => window.EvoUI?.initDnd?.($el))"
        x-on:evo-ui:form.dirty="markStructureDirty()"
    >
        @foreach($tabs as $tabIndex => $tab)
            <section
                class="ssettings-compact-tab evo-ui-dnd-group-row"
                wire:key="ssettings-tab-{{ $tab['_uid'] ?? $tabIndex }}"
                data-evo-dnd-group
                data-evo-dnd-uid="{{ $tab['_uid'] ?? '' }}"
                data-evo-dnd-group-uid="{{ $tab['_uid'] ?? '' }}"
                data-index="{{ $tabIndex }}"
                draggable="true"
            >
                <header class="ssettings-compact-tab__header">
                    <x-evo::reorder-rail
                        class="ssettings-reorder-rail--tab"
                        :move-up="'moveTab(' . $tabIndex . ', -1)'"
                        :move-down="'moveTab(' . $tabIndex . ', 1)'"
                        :up-disabled="$tabIndex === 0"
                        :down-disabled="$tabIndex >= count($tabs) - 1"
                        :label="__('sSettings::global.drag_to_reorder')"
                    />

                    <label class="ssettings-mini-field ssettings-mini-field--label">
                        <span>@lang('sSettings::global.tab_label')</span>
                        <input
                            class="evo-ui-input"
                            type="text"
                            value="{{ $tab['label'] ?? '' }}"
                            data-ssettings-dirty-input
                            data-dirty-key="tab-label-{{ $tab['key'] ?? $tabIndex }}-{{ $tabIndex }}"
                            x-on:change="$wire.updateTabLabelByUid(@js($tab['_uid'] ?? ''), $event.target.value)"
                        >
                    </label>

                    <div class="evo-ui-row-actions ssettings-compact-actions">
                        <button type="button" class="evo-ui-row-action evo-ui-row-action--info" title="@lang('sSettings::global.add_tab_after')" aria-label="@lang('sSettings::global.add_tab_after')" wire:click="addTabAfter({{ $tabIndex }})">
                            <x-evo::icon name="copy-plus" />
                        </button>
                        <button type="button" class="evo-ui-row-action evo-ui-row-action--danger" title="@lang('global.remove')" aria-label="@lang('global.remove')" wire:click="removeTab({{ $tabIndex }})" @disabled(count($tabs) < 2)>
                            <x-evo::icon name="trash" />
                        </button>
                    </div>
                </header>

                <div
                    class="ssettings-compact-fields evo-ui-dnd-list"
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
                            class="ssettings-compact-field evo-ui-dnd-row"
                            wire:key="ssettings-field-{{ $field['_uid'] ?? ($tabIndex . '-' . $fieldIndex) }}"
                            x-data="{
                                open: @js($openFieldUid === ($field['_uid'] ?? '')),
                                name: @js($fieldName),
                                label: @js($fieldLabel),
                                description: @js($fieldDescription),
                                type: @js($fieldType),
                                optionTypes: @js($optionTypes),
                                options: @js($fieldOptions),
                                optionHandleIndex: null,
                                optionDragIndex: null,
                                optionPlaceholderIndex: null,
                                optionDropIndex: null,
                                optionPlaceholderHeight: 38,
                                init() {
                                    this.options = this.options.map((option) => ({
                                        _uid: option._uid || this.optionUid(),
                                        value: option.value || '',
                                        label: option.label || '',
                                    }));
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
                                prepareOptionDrag(index) {
                                    this.optionHandleIndex = index;
                                },
                                cancelOptionHandle(event) {
                                    if (!event.target?.closest?.('[data-ssettings-option-drag-handle]')) {
                                        this.optionHandleIndex = null;
                                    }
                                },
                                resetOptionDrag() {
                                    this.optionHandleIndex = null;
                                    this.optionDragIndex = null;
                                    this.optionPlaceholderIndex = null;
                                    this.optionDropIndex = null;
                                    this.optionPlaceholderHeight = 38;
                                    window.__sSettingsOptionDrag = null;
                                },
                                optionInsertionIndex(index, event) {
                                    const row = event.currentTarget?.matches?.('.ssettings-option-editor-row')
                                        ? event.currentTarget
                                        : event.target?.closest?.('.ssettings-option-editor-row');
                                    const box = row?.getBoundingClientRect();

                                    if (!box) {
                                        return index;
                                    }

                                    return event.clientY > box.top + box.height / 2 ? index + 1 : index;
                                },
                                hoverOption(index, event) {
                                    if (this.optionDragIndex === null) {
                                        return;
                                    }

                                    event.preventDefault();
                                    this.optionDropIndex = this.optionInsertionIndex(index, event);
                                },
                                hoverOptionList(event) {
                                    if (this.optionDragIndex === null) {
                                        return;
                                    }

                                    event.preventDefault();

                                    const list = event.currentTarget?.matches?.('[data-ssettings-option-list]')
                                        ? event.currentTarget
                                        : event.target?.closest?.('[data-ssettings-option-list]');

                                    if (!list) {
                                        return;
                                    }

                                    const rows = Array.from(list.querySelectorAll('.ssettings-option-editor-row'))
                                        .filter((row) => Number(row.getAttribute('data-option-index')) !== this.optionDragIndex);
                                    const before = rows.findIndex((row) => {
                                        const box = row.getBoundingClientRect();

                                        return event.clientY < box.top + box.height / 2;
                                    });

                                    this.optionDropIndex = before === -1
                                        ? this.options.length
                                        : Number(rows[before].getAttribute('data-option-index'));
                                },
                                dropOptionList(event) {
                                    if (this.optionDragIndex === null) {
                                        return;
                                    }

                                    event.preventDefault();
                                    const to = Number.isInteger(this.optionDropIndex) ? this.optionDropIndex : this.options.length;
                                    this.moveDraggedOption(to);
                                },
                                moveDraggedOption(to) {
                                    const from = Number(this.optionDragIndex);

                                    if (!Number.isInteger(from) || from < 0 || from >= this.options.length) {
                                        this.resetOptionDrag();
                                        return;
                                    }

                                    if (from === to || from + 1 === to) {
                                        this.resetOptionDrag();
                                        return;
                                    }

                                    const [option] = this.options.splice(from, 1);

                                    if (from < to) {
                                        to -= 1;
                                    }

                                    this.options.splice(Math.max(0, Math.min(to, this.options.length)), 0, option);
                                    this.resetOptionDrag();
                                },
                                dragOptionStart(index, event) {
                                    if (this.optionHandleIndex !== index) {
                                        event.preventDefault();
                                        return;
                                    }

                                    if (event.dataTransfer) {
                                        const row = event.target.closest('.ssettings-option-editor-row');
                                        const box = row?.getBoundingClientRect();

                                        event.dataTransfer.effectAllowed = 'move';
                                        event.dataTransfer.setData('text/plain', String(index));

                                        if (row && box) {
                                            this.optionPlaceholderHeight = Math.ceil(box.height);
                                            event.dataTransfer.setDragImage(
                                                row,
                                                Math.max(0, Math.min(event.clientX - box.left, box.width)),
                                                Math.max(0, Math.min(event.clientY - box.top, box.height))
                                            );
                                        }
                                    }

                                    this.optionDragIndex = index;
                                    window.__sSettingsOptionDrag = {
                                        component: this,
                                        from: index,
                                    };

                                    window.requestAnimationFrame(() => {
                                        if (this.optionDragIndex === index) {
                                            this.optionPlaceholderIndex = index;
                                            this.optionDropIndex = index + 1;
                                        }
                                    });
                                },
                                dropOption(index, event) {
                                    event.preventDefault();

                                    const rawIndex = this.optionDragIndex ?? Number(event.dataTransfer.getData('text/plain'));
                                    const from = Number(rawIndex);

                                    if (!Number.isInteger(from) || from < 0 || from >= this.options.length) {
                                        this.resetOptionDrag();
                                        return;
                                    }

                                    const to = Number.isInteger(this.optionDropIndex)
                                        ? this.optionDropIndex
                                        : this.optionInsertionIndex(index, event);

                                    this.moveDraggedOption(to);
                                },
                                dragOptionEnd() {
                                    this.resetOptionDrag();
                                }
                            }"
                            data-evo-dnd-item
                            data-evo-dnd-uid="{{ $field['_uid'] ?? '' }}"
                            data-evo-dnd-item-uid="{{ $field['_uid'] ?? '' }}"
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

                            <button type="button" class="ssettings-field-summary" x-on:click="open = true">
                                <span class="ssettings-field-summary__title" data-placeholder="@lang('sSettings::global.field_label')">{{ $fieldLabel }}</span>
                                <code class="ssettings-field-summary__key ssettings-system-key">{{ $fieldUsage }}</code>
                                @if($fieldDescription)
                                    <span class="ssettings-field-summary__description">{{ $fieldDescription }}</span>
                                @endif
                            </button>

                            <span class="ssettings-type-badge ssettings-type-badge--compact" title="{{ $types[$fieldType] ?? ucfirst($fieldType) }}">{{ $types[$fieldType] ?? ucfirst($fieldType) }}</span>

                            <div class="evo-ui-row-actions ssettings-compact-actions">
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
                                class="evo-ui-modal-backdrop ssettings-field-modal-backdrop"
                                role="presentation"
                                x-cloak
                                x-show="open"
                                x-on:click.self="open = false; $wire.clearOpenField()"
                                x-on:keydown.escape.window="if (open) { open = false; $wire.clearOpenField() }"
                            >
                                <section class="evo-ui-modal evo-ui-modal--sm ssettings-field-modal" role="dialog" aria-modal="true" x-on:click.stop>
                                    <header class="evo-ui-modal__header ssettings-field-modal__header">
                                        <div class="evo-ui-modal__title">
                                            <code class="ssettings-system-key">{{ $fieldUsage }}</code>
                                        </div>
                                        <button type="button" class="evo-ui-modal__close" title="@lang('evo::global.action_cancel')" aria-label="@lang('evo::global.action_cancel')" x-on:click="open = false; $wire.clearOpenField()">
                                            <x-evo::icon name="x" />
                                        </button>
                                    </header>
                                    <div class="ssettings-field-modal__body" data-ssettings-field-settings-modal>
                                        <label class="ssettings-modal-field">
                                            <span>@lang('sSettings::global.field_key')</span>
                                            <input class="evo-ui-input" type="text" x-model="name">
                                        </label>
                                        <label class="ssettings-modal-field">
                                            <span>@lang('sSettings::global.field_label')</span>
                                            <input class="evo-ui-input" type="text" x-model="label">
                                        </label>
                                        <label class="ssettings-modal-field">
                                            <span>@lang('sSettings::global.field_description')</span>
                                            <input class="evo-ui-input" type="text" x-model="description">
                                        </label>
                                        <label class="ssettings-modal-field">
                                            <span>@lang('sSettings::global.field_type')</span>
                                            <select class="evo-ui-input" x-model="type" x-on:change="ensureOptionRow()">
                                                @foreach($types as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <div class="ssettings-options-editor" x-show="usesOptions()" x-cloak>
                                            <div class="ssettings-options-editor__heading">
                                                <span>@lang('sSettings::global.field_options')</span>
                                            </div>
                                            <div
                                                class="ssettings-options-editor__rows"
                                                data-ssettings-option-list
                                                x-bind:style="`--ssettings-option-placeholder-height: ${optionPlaceholderHeight}px`"
                                                x-on:dragenter.prevent.stop
                                                x-on:dragover.prevent.stop="hoverOptionList($event)"
                                                x-on:drop.prevent.stop="dropOptionList($event)"
                                            >
                                                <template x-if="optionDragIndex !== null && optionDropIndex === 0">
                                                    <div class="ssettings-option-dnd-placeholder" aria-hidden="true"></div>
                                                </template>
                                                <template x-for="(option, optionIndex) in options" :key="option._uid || optionIndex">
                                                    <div class="ssettings-option-editor-item">
                                                        <div
                                                            class="ssettings-option-editor-row"
                                                            data-ssettings-option-row
                                                            x-bind:data-option-index="optionIndex"
                                                            draggable="true"
                                                            x-bind:class="{
                                                                'is-dragging': optionPlaceholderIndex === optionIndex,
                                                                'is-drag-hidden': optionPlaceholderIndex === optionIndex
                                                            }"
                                                            x-on:pointerdown.capture="cancelOptionHandle($event)"
                                                            x-on:dragstart.stop="dragOptionStart(optionIndex, $event)"
                                                            x-on:dragend.stop="dragOptionEnd()"
                                                            x-on:dragenter.prevent.stop
                                                            x-on:dragover.prevent.stop="hoverOption(optionIndex, $event); $event.dataTransfer.dropEffect = 'move'"
                                                            x-on:drop.prevent.stop="dropOption(optionIndex, $event)"
                                                        >
                                                            <div class="ssettings-option-reorder-rail">
                                                                <button
                                                                    type="button"
                                                                    title="@lang('sSettings::global.move_up')"
                                                                    aria-label="@lang('sSettings::global.move_up')"
                                                                    x-on:click="moveOption(optionIndex, -1)"
                                                                    x-bind:disabled="optionIndex === 0"
                                                                >
                                                                    <x-evo::icon name="arrow-up" />
                                                                </button>
                                                                <span
                                                                    role="button"
                                                                    tabindex="0"
                                                                    class="ssettings-option-drag-handle"
                                                                    title="@lang('sSettings::global.drag_to_reorder')"
                                                                    aria-label="@lang('sSettings::global.drag_to_reorder')"
                                                                    data-ssettings-option-drag-handle
                                                                    x-on:pointerdown.stop="prepareOptionDrag(optionIndex)"
                                                                    x-on:mousedown.stop="prepareOptionDrag(optionIndex)"
                                                                    x-on:selectstart.prevent
                                                                >
                                                                    <x-evo::icon name="grip-vertical" />
                                                                </span>
                                                                <button
                                                                    type="button"
                                                                    title="@lang('sSettings::global.move_down')"
                                                                    aria-label="@lang('sSettings::global.move_down')"
                                                                    x-on:click="moveOption(optionIndex, 1)"
                                                                    x-bind:disabled="optionIndex >= options.length - 1"
                                                                >
                                                                    <x-evo::icon name="arrow-down" />
                                                                </button>
                                                            </div>
                                                            <input class="evo-ui-input" type="text" x-model="option.value" placeholder="@lang('sSettings::global.option_value')" draggable="false">
                                                            <input class="evo-ui-input" type="text" x-model="option.label" placeholder="@lang('sSettings::global.option_label')" draggable="false">
                                                            <div class="evo-ui-row-actions ssettings-option-actions">
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
                                                        <template x-if="optionDragIndex !== null && optionDropIndex === optionIndex + 1">
                                                            <div class="ssettings-option-dnd-placeholder" aria-hidden="true"></div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <footer class="evo-ui-modal__footer">
                                        <button
                                            type="button"
                                            class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled"
                                            x-on:click="$wire.updateFieldByUid(@js($field['_uid'] ?? ''), name, label, description, type, options); open = false"
                                        >
                                            <x-evo::icon name="check" />
                                            <span>@lang('evo::global.action_save')</span>
                                        </button>
                                    </footer>
                                </section>
                            </div>
                        </div>

                    @empty
                        <div
                            class="ssettings-compact-empty"
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
