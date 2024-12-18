<div class="row form-row form-element-input">
    <label class="control-label col-5 col-md-3 col-lg-2">
        @if(trim($field['label']))
            <span>@lang($field['label'])</span>
        @else
            <span>@lang('sSettings::global.no_title')</span>
        @endif
        <small class="form-text text-muted">@{{evo()->getConfig('sset_{!!$field['name']!!}', '')}}</small>
    </label>
    <div class="col-7 col-md-9 col-lg-10">
        <input class="form-control" type="text" id="{{$field['name']}}" name="sset_{{$field['name']}}" value="{{evo()->getConfig('sset_'.$field['name'], '')}}" onchange="documentDirty=true;" maxlength="255">
        <small class="form-text text-muted">@lang($field['description'])</small>
    </div>
</div>
<div class="split my-1"></div>