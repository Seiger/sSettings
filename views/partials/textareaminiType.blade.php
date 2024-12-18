<div class="row form-row form-element-input">
    <label class="control-label col-5 col-md-3 col-lg-2">
        @if(trim($field['label']))
            <span>@lang($field['label'])</span>
        @else
            <span>@lang('sSettings::global.no_title')</span>
        @endif
        <small class="form-text text-muted">@{!!evo()->getConfig('sset_{{$field['name']}}', '')!!}</small>
    </label>
    <div class="col-7 col-md-9 col-lg-10">
        <textarea class="form-control" id="{{$field['name']}}" name="sset_{{$field['name']}}" cols="30" rows="3" onchange="documentDirty=true;">{{evo()->getConfig('sset_'.$field['name'], '')}}</textarea>
        <small class="form-text text-muted">@lang($field['description'])</small>
    </div>
</div>
<div class="split my-1"></div>
