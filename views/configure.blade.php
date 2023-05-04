@extends('manager::template.page')
@section('content')
    <h1><i class="@lang('sSettings::global.icon')" data-tooltip="@lang('sSettings::global.description')"></i>@lang('sSettings::global.title')</h1>
    <form name="ssettings" id="ssettings" class="content" method="post" action="{{route('sSettings.update-configure')}}" onsubmit="documentDirty=false;">
        <div class="sectionBody">
            <div class="tab-pane" id="resourcesPane">
                <script>tpResources = new WebFXTabPane(document.getElementById('resourcesPane'), false);</script>
                <div class="tab-page configure" id="configure">
                    <h2 class="tab"><span>@lang('global.edit_settings')</span></h2>
                    <p class="text-danger text-monospace text-center">@lang('sSettings::global.message_for_dev')</p>
                    <script>tpResources.addTabPage(document.getElementById('configure'));</script>
                    <div class="container container-body builder">
                        <div class="row">
                            <div class="col">
                                <div id="builder" class="b-content">
                                    @foreach($tabs as $tabId => $tab)
                                        <div class="b-tab">
                                            <div class="b-tab-title sectionHeader">
                                                <div class="row align-items-center">
                                                    <div class="col-auto"><i class="fa fa-arrows b-move"></i></div>
                                                    <div class="col-auto b-tab-title-input">
                                                        <input type="text" name="tabs[{{$tabId}}][label]" value="@lang($tab['label'])" class="form-control form-control-sm pr-2 b-title" placeholder="@lang('sSettings::global.no_title')">
                                                    </div>
                                                    <div class="col-auto" onclick="onAddTab($(this))"><i data-tooltip="@lang('sSettings::global.add_new_tab')" class="fa fa-plus-circle text-primary b-btn-add"></i></div>
                                                    <div class="col-auto" onclick="onDeleteTab($(this))"><i data-tooltip="@lang('sSettings::global.del_this_tab')" class="fa fa-minus-circle text-danger b-btn-del"></i></div>
                                                    <div class="col-auto" onclick="onAddField($(this))"><i data-tooltip="@lang('sSettings::global.add_new_field')" class="fa fa-plus-circle text-success b-btn-add"></i></div>
                                                </div>
                                            </div>
                                            <div class="row col row-col-wrap col-12 b-draggable b-tab-fields">
                                                @if(isset($tab['fields']) && is_array($tab['fields']) && count($tab['fields']))
                                                    @foreach($tab['fields'] as $field)
                                                        <div class="col-12 b-field b-item">
                                                            <div class="row align-items-center">
                                                                <div class="col-auto"><i class="fa fa-bars b-move"></i></div>
                                                                <div class="col">@lang($field['label'])&emsp;<strong>[(sset_{{$field['name']}})]</strong></div>
                                                                <div class="col-auto"><span class="badge badge-warning">{{$field['type']}}</span></div>
                                                                <div class="col-auto" onclick="onSettings($(this))"><i class="fa fa-cog b-btn-settings"></i></div>
                                                                <div class="col-auto" onclick="onDeleteField($(this))"><i class="fa fa-minus-circle text-danger b-btn-del"></i></div>
                                                            </div>
                                                            <div class="row col b-field-settings b-settings nu-context-menu">
                                                                <div class="row b-settings-header">
                                                                    <div class="col text-center"><b class="text-primary">[(sset_{{$field['name']}})]</b></div>
                                                                    <label class="col-auto b-btn-close btn-danger" onclick="closeSettings($(this))">×</label>
                                                                </div>
                                                                <div class="b-settings-body">
                                                                    <div class="b-settings-content">
                                                                        @if(!trim($field['name']))
                                                                            <div class="row b-setting">
                                                                                <div class="col-4">@lang('sSettings::global.field_key')</div>
                                                                                <div class="col-8 b-btn-group">
                                                                                    <input type="text" name="tabs[{{$tabId}}][fields][{{$loop->index}}][name]" required class="form-control form-control-sm">
                                                                                </div>
                                                                            </div>
                                                                        @else
                                                                            <input type="hidden" name="tabs[{{$tabId}}][fields][{{$loop->index}}][name]" value="{{$field['name']}}">
                                                                        @endif
                                                                        <div class="row b-setting">
                                                                            <div class="col-4">@lang('sSettings::global.field_label')</div>
                                                                            <div class="col-8 b-btn-group">
                                                                                <input type="text" name="tabs[{{$tabId}}][fields][{{$loop->index}}][label]" value="@lang($field['label'])" class="form-control form-control-sm">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row b-setting">
                                                                            <div class="col-4">@lang('sSettings::global.field_description')</div>
                                                                            <div class="col-8 b-btn-group">
                                                                                <input type="text" name="tabs[{{$tabId}}][fields][{{$loop->index}}][description]" value="@lang($field['description'])" class="form-control form-control-sm">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row b-setting">
                                                                            <div class="col-4">@lang('sSettings::global.field_type')</div>
                                                                            <div class="col-8 b-btn-group">
                                                                                <select name="tabs[{{$tabId}}][fields][{{$loop->index}}][type]" class="form-control form-control-sm">
                                                                                    <option value="text">Text</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            <i class="b-resize b-resize-r"></i>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div id="copyright">
        @lang('sSettings::global.copyright') <strong><a href="https://seigerit.com/" target="_blank">Seiger IT</a></strong>
    </div>
@endsection
@push('scripts.bot')
    <div id="actions">
        <div class="btn-group">
            <a id="Button1" class="btn btn-success" href="javascript:void(0);" onclick="saveForm('#ssettings');">
                <i class="fa fa-floppy-o"></i>
                <span>@lang('global.save')</span>
            </a>
            <a id="Button3" class="btn btn-secondary" href="{{route('sSettings.index')}}">
                <i class="fa fa-times-circle"></i>
                <span>@lang('global.cancel')</span>
            </a>
        </div>
    </div>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js"></script>
    <script>
        sortableTabs();
        sortableFields();
        function sortableTabs(){$('#builder').sortable({animation:150,ghostClass:'blue-background-class'});}
        function sortableFields(){let sortableOptions2={group:{name:"sortable-list-2",pull:true,put:true},animation:150,forceFallback:true,ghostClass:'blue-background-class'};let containers=null;containers=document.querySelectorAll(".b-draggable");for(let i = 0;i<containers.length;i++){new Sortable(containers[i], sortableOptions2);}}
        function onSettings(target){let parent = target.closest('.b-item');let settingsBlock = parent.children().last().hasClass('b-settings') && parent.children().last();if(parent.hasClass('b-open')){parent.removeClass('b-open');}else{if(settingsBlock){setTimeout(function(){parent.addClass('b-open');},20);}}}
        function closeSettings(target){document.querySelectorAll('.b-open').forEach(function(el){if(!target.closest('.b-settings') || target.hasClass('b-btn-close')){el.classList.remove('b-open');}})}
        function onAddTab(target){let parent=target.closest('.b-tab');let newTab=$('#newTab').html();parent.after(newTab);let tabId=$('[name="tabs[newTab][label]"]').first();let containers=(document.querySelectorAll(".b-tab").length)-1;tabId.attr('name','tabs[newTab'+containers+'][label]').attr('value','newTab'+containers)}
        function onDeleteTab(target){let containers=document.querySelectorAll(".b-tab").length;parent=target.closest('.b-tab');if(containers < 3){alertify.error("@lang('sSettings::global.error_tab_delete')")}else{if(parent.find('.b-item').length){alertify.error("@lang('sSettings::global.error_tab_delete_is_files')")}else{parent.remove()}}}
        function onAddField(target){let parent=target.closest('.b-tab');let tabId=parent.find('.b-tab-title-input input[name]').first().attr('name').replace("tabs[","").replace("][label]","");let countFields=parent.find('.b-field').length;let newField=$('#newField').html();newField=newField.replace('tabs[tabId][fields][99999]','tabs['+tabId+'][fields]['+countFields+']');parent.find('.b-tab-fields').append(newField);sortableFields()}
        function onDeleteField(target){let parent=target.closest('.b-field');alertify.confirm("@lang('sSettings::global.are_you_sure')","@lang('sSettings::global.deleted_irretrievably')",function(){alertify.error("@lang('sSettings::global.deleted')");parent.remove()},function(){alertify.success("@lang('sSettings::global.canceled')")}).set('labels',{ok:"@lang('global.delete')",cancel:"@lang('global.cancel')"}).set({transition:'zoom'})}
        function saveForm(selector){$(selector).find('.b-tab').each(function(index){let parent=$(selector).find('.b-tab').eq(index);let tabId=parent.find('.b-tab-title-input input[name]').first().attr('name').replace("tabs[","").replace("][label]","");parent.find('.b-field').each(function(position){parent.find('.b-field').eq(position).find('input').filter(function(){return this.name.match(/]\[name]/)}).attr('name', 'tabs['+tabId+'][fields]['+position+'][name]');parent.find('.b-field').eq(position).find('input').filter(function(){return this.name.match(/]\[label]/)}).attr('name', 'tabs['+tabId+'][fields]['+position+'][label]');parent.find('.b-field').eq(position).find('input').filter(function(){return this.name.match(/]\[description]/)}).attr('name', 'tabs['+tabId+'][fields]['+position+'][description]');parent.find('.b-field').eq(position).find('select').filter(function(){return this.name.match(/]\[type]/)}).attr('name', 'tabs['+tabId+'][fields]['+position+'][type]')})});$(selector).submit()}
    </script>
    <style>
        #copyright{position:fixed;bottom:0;right:0;background-color:#0057b8;color:#ffd700;padding:5px}
        #copyright a{color:#ffd700}
        .alertify .ajs-footer .ajs-buttons .ajs-button.ajs-ok {color:#fff;background-color:#d9534f;border-color:#d9534f;}
        .builder .row{display:flex;flex-wrap:wrap;margin-left:-.25rem;margin-right:-.25rem;cursor:default}
        .builder .row::after{display:none}
        .builder .row.col{align-content:flex-start;margin:0 0 .25rem;padding:0 .25rem 0 0}
        .builder .col-4, .builder .col-8, .builder .col-12, .builder .col, .builder .col-auto{position:relative;width:100%;min-height:0;padding-left:.25rem;padding-right:.25rem}
        .builder .col{flex-basis:0;flex-grow:1;max-width:100%}
        .builder .col-auto{-ms-flex:0 0 auto;flex:0 0 auto;width:auto;max-width:none}
        .builder .col-4{flex:0 0 33.3333%;max-width:33.3333%}
        .builder .col-8{flex:0 0 66.6667%;max-width:66.6667%}
        .builder .col-12{-ms-flex:0 0 100%;flex:0 0 100%;max-width:100%}
        .builder .align-items-center{align-items:center}
        .builder label{margin:0;user-select:none}
        .builder input[type="text"]{cursor:auto}
        .builder .row-col{position:relative;padding:.25rem .25rem 0 .25rem !important;margin-right:-1px !important;min-height:2.4rem;height:100%;border:1px solid}
        .builder .b-resize{position:absolute;top:0;right:-1px;bottom:0;width:.35rem;cursor:col-resize;transition:background-color .25s}
        .builder .fa{font-size:.75rem}
        .builder .b-hidden{display:none}
        .builder .b-settings{position:absolute;overflow:hidden;z-index:6;opacity:0;visibility:hidden;left:.5rem;top:0;padding:0 !important;width:20rem !important;max-width:20rem !important;cursor:auto;transform:translateY(.5rem);transition:opacity .25s, visibility .25s, transform .25s}
        .builder .row-col-wrap > .b-settings, .builder .b-field > .b-settings{left:auto;right:3rem}
        .builder .b-open > .b-settings{transform:translateX(0);opacity:1;visibility:visible}
        .builder .b-settings .row{align-items:center;padding:.4rem 1rem;border-bottom:1px solid}
        .builder .b-settings .b-settings-header{padding:0;margin:0}
        .builder .b-settings .b-settings-header > div{padding:0 1rem}
        .builder .b-settings .b-settings-header label{padding:0 .5rem;height:1.5rem;font-size:2rem;line-height:.45rem;text-align:center;font-family:serif}
        .builder .b-settings .b-settings-header .b-btn-close{line-height:1.45rem}
        .builder .b-settings .b-settings-body{font-size:0;white-space:nowrap}
        .builder .b-settings .b-input-more:checked ~ .b-settings-body .b-settings-content{transform:translateX(-100%);}
        .builder .b-settings .b-settings-content{overflow:hidden;display:inline-block;width:20rem;font-size:.75rem;vertical-align:top;transition:transform .5s}
        .builder .b-settings .b-settings-content .row:last-child{margin-bottom:.5rem;border:none}
        .builder .b-settings .b-btn-group{display:flex;flex-wrap:wrap;flex-direction:row}
        .builder .b-settings .b-btn-group label{flex-basis:0;flex-grow:1;max-width:100%;display:block;border-left:1px solid}
        .builder .b-settings .b-btn-group label:first-child{border:none}
        .builder .b-settings input[type="radio"] + i, .builder .b-settings input[type="checkbox"] + i{display:block;width:100%;padding:.1rem .25rem;font-style: normal;font-size:.875rem;line-height:1.3rem;text-align:center;cursor:pointer}
        .builder .b-settings [data-name="position"][value="r"] + i{transform:rotate(180deg)}
        .builder .b-tab{display:flex;flex-wrap:wrap;flex-direction:row;position:relative;top:1px;padding:.5rem .5rem .25rem .5rem;margin:2.5rem 0 3rem;min-height:3.5rem;border:1px solid}
        .builder .b-tab-title{position:absolute;left:0;bottom:100%;border-bottom:none}
        .builder .b-tab-title-input{position:relative;width:10.5rem;}
        .builder .b-items .b-field{overflow:hidden;padding:.25rem .5rem;margin:-1px 0 0;border:none;border-top:1px solid;white-space:nowrap;text-overflow:ellipsis}
        .builder .b-items .b-field .b-btn-settings, .builder .b-items .b-field .b-btn-del, .builder .b-items .b-field, .builder .b-items .b-category .b-btn-del{display:none}
        .b-field-title{margin-top:-3px;padding-bottom:2px!important;}
        .builder .b-field{position:relative;padding:.25rem .5rem;margin-bottom:.25rem;height:1.8rem;border:1px solid}
        .builder .b-field .b-btn-settings, .builder .b-field .b-btn-del{opacity:.5;transition:.5s opacity}
        .builder .b-field:hover .b-btn-settings, .builder .b-field:hover .b-btn-del{opacity:1}
        .builder .b-field:hover, .builder .b-category:hover{background-color:rgba(0, 0, 0, 0.04)}
        .builder .b-field-hidden::after{position:absolute;content:"";left:0;top:0;right:0;bottom:0;background-color:rgba(239, 239, 239, 0.65)}
        .builder .b-field-hidden *{-webkit-user-select:none !important;user-select:none !important;}
        .builder .b-settings input[type="radio"] + i, .builder .b-settings input[type="checkbox"] + i, .builder .b-settings .b-btn-more{background-color:#e0e0e0}
        .builder .b-settings input[type="radio"]:checked + i, .builder .b-settings input[type="checkbox"]:checked + i{background-color:#1976d2;color:#fff}
        .builder .b-resize{background-color:#0057b8}
        .builder .b-items .b-field, .builder .b-tab, .builder .b-field, .builder .row-col, .builder .b-resize, .builder .b-settings .row{border-color:#e0e0e0}
        .builder .row-col-wrap:hover .row-col, .builder .row-col-wrap:hover .b-resize, .builder .b-btn-wrap, .builder .b-resize, .builder .b-settings .col-12:first-child{border-color:#ccc}
        .builder .row-col-wrap:hover .b-resize, .builder .b-settings .b-btn-group label{border-color:#ccc}
        .builder .b-item.active, .builder .b-item.active .b-tab-title, .builder .row-col-wrap.active{background-color:#fff4dc;color:#222}*/
        .darkness .builder .b-items .b-field, .darkness .builder .b-tab, .darkness .builder .b-field, .darkness .builder .row-col, .darkness .builder .b-resize, .darkness .builder .b-settings .row, .darkness .builder .b-settings .b-btn-group label{border-color: #414449}
        .darkness .builder .b-resize{background-color:#ffd700}
        .darkness .builder .row-col-wrap:hover .b-resize{background-color:#65686d}
        .darkness .builder .row-col-wrap:hover .row-col, .darkness .builder .row-col-wrap:hover .b-resize, .darkness .builder .b-btn-wrap, .darkness .builder .b-settings > .row:first-child{border-color:#65686d}
        .darkness .builder .b-btn-wrap, .darkness .builder .b-settings input[type="radio"] + i, .darkness .builder .b-settings input[type="checkbox"] + i, .darkness .builder .b-settings .b-btn-more{background-color:#282c34}
        .darkness .builder .b-settings input[type="radio"]:checked + i, .darkness .builder .b-settings input[type="checkbox"]:checked + i, .builder .b-settings .b-input-more:checked + .row .b-btn-more{background-color:#1976d2;color:#fff}
        .builder .row-col-wrap:hover .b-resize:hover, .builder .b-resize:hover, .builder .b-resize:active{background-color:#1976d2}
        .darkness .builder .b-item.active, .darkness .builder .b-item.active .b-tab-title{background-color:#ffd700;color:#222}
        .blue-background-class {background-color: #C8EBFB;}
    </style>
    <div id="newTab" style="display:none">
        <div class="b-tab">
            <div class="b-tab-title sectionHeader">
                <div class="row align-items-center">
                    <div class="col-auto"><i class="fa fa-arrows b-move"></i></div>
                    <div class="col-auto b-tab-title-input">
                        <input type="text" name="tabs[newTab][label]" value="newTab" class="form-control form-control-sm pr-2 b-title">
                    </div>
                    <div class="col-auto" onclick="onAddTab($(this))"><i data-tooltip="@lang('sSettings::global.add_new_tab')" class="fa fa-plus-circle text-primary b-btn-add"></i></div>
                    <div class="col-auto" onclick="onDeleteTab($(this))"><i data-tooltip="@lang('sSettings::global.del_this_tab')" class="fa fa-minus-circle text-danger b-btn-del"></i></div>
                    <div class="col-auto" onclick="onAddField($(this))"><i data-tooltip="@lang('sSettings::global.add_new_field')" class="fa fa-plus-circle text-success b-btn-add"></i></div>
                </div>
            </div>
            <div class="row col row-col-wrap col-12 b-draggable b-tab-fields"></div>
            <i class="b-resize b-resize-r"></i>
        </div>
    </div>
    <div id="newField" style="display:none">
        <div class="col-12 b-field b-item">
            <div class="row align-items-center">
                <div class="col-auto"><i class="fa fa-bars b-move"></i></div>
                <div class="col"></div>
                <div class="col-auto"></div>
                <div class="col-auto" onclick="onSettings($(this))"><i class="fa fa-cog b-btn-settings"></i></div>
                <div class="col-auto" onclick="onDeleteField($(this))"><i class="fa fa-minus-circle text-danger b-btn-del"></i></div>
            </div>
            <div class="row col b-field-settings b-settings nu-context-menu">
                <div class="row b-settings-header">
                    <div class="col text-center"><b class="text-primary"></b></div>
                    <label class="col-auto b-btn-close btn-danger" onclick="closeSettings($(this))">×</label>
                </div>
                <div class="b-settings-body">
                    <div class="b-settings-content">
                        <div class="row b-setting">
                            <div class="col-4">@lang('sSettings::global.field_key')</div>
                            <div class="col-8 b-btn-group">
                                <input type="text" name="tabs[tabId][fields][99999][name]" required class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="row b-setting">
                            <div class="col-4">@lang('sSettings::global.field_label')</div>
                            <div class="col-8 b-btn-group">
                                <input type="text" name="tabs[tabId][fields][99999][label]" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="row b-setting">
                            <div class="col-4">@lang('sSettings::global.field_description')</div>
                            <div class="col-8 b-btn-group">
                                <input type="text" name="tabs[tabId][fields][99999][description]" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="row b-setting">
                            <div class="col-4">@lang('sSettings::global.field_type')</div>
                            <div class="col-8 b-btn-group">
                                <select name="tabs[tabId][fields][99999][type]" class="form-control form-control-sm">
                                    <option value="text">Text</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush