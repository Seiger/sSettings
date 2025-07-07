<div class="row form-row form-row-image">
    <label class="control-label col-5 col-md-3 col-lg-2">
        @if(trim($field['label']))
            <span>@lang($field['label'])</span>
        @else
            <span>@lang('sSettings::global.no_title')</span>
        @endif
        <small class="form-text text-muted">@{{evo()->getConfig('sset_{!!$field['name']!!}', '')}}</small>
    </label>
    <div class="col-7 col-md-9 col-lg-10">
        <div class="col-12" style="padding-left: 0px;">
            <input class="form-control" style="width: 90%!important;"
                   type="text" id="{{$field['name']}}"
                   name="sset_{{$field['name']}}"
                   value="{{evo()->getConfig('sset_'.$field['name'], '')}}"
                   onchange="documentDirty=true;" maxlength="255">
            <input type="button" value="{{ __('global.insert') }}" style="width: 10%" onclick="BrowseFileServer('{{$field['name']}}')">
        </div>
        <small class="form-text text-muted">@lang($field['description'])</small>
    </div>
</div>
