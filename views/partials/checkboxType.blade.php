<div class="row form-row form-row-image">
    <label class="control-label col-5 col-md-3 col-lg-2">
        @if(trim($field['label']))
            <span>@lang($field['label'])</span>
        @else
            <span>@lang('sSettings::global.no_title')</span>
        @endif
        <small class="form-text text-muted">[(sset_{{$field['name']}})]</small>
    </label>
    <div class="col-7 col-md-9 col-lg-10">
        <input type="checkbox" id="publishedcheck_{{$field['name']}}" class="form-checkbox form-control"
               maxlength="255" onchange="documentDirty=true;"
               onclick="changestate(document.getElementById('{{$field['name']}}'));" @if(evo()->getConfig('sset_'.$field['name'], 0)) checked @endif>
        <input type="hidden" id="{{$field['name']}}" class="form-control" name="sset_{{$field['name']}}" maxlength="255"
               value="{{evo()->getConfig('sset_'.$field['name'], 0)}}"
               onchange="documentDirty=true;">
        <small class="form-text text-muted">@lang($field['description'])</small>
    </div>
</div>