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
        <input type="text" id="{{$field['name']}}" class="form-control" name="sset_{{$field['name']}}" value="{{evo()->getConfig('sset_'.$field['name'], '')}}" onchange="documentDirty=true;" style="width: 90%">
        @php
            $site_url = evo()->getConfig('sset_'.$field['name'], null) ? evo()->getConfig('site_url', '/') : '';
        @endphp
        <input class="form-control" type="button" value="@lang("global.insert")" style="width: 10%" onclick="BrowseServer('{{$field['name']}}')">
        <div class="col-12" style="padding-left: 0px;">
            <div id="image_for_{{$field['name']}}" class="image_for_field"
                 data-image="{{$site_url}}{{evo()->getConfig('sset_'.$field['name'], '')}}"
                 onclick="BrowseServer('{{$field['name']}}')"
                 style="background-image: url('{{$site_url}}{{evo()->getConfig('sset_'.$field['name'], '')}}');"></div>
            <script>document.getElementById('{{$field['name']}}').addEventListener('change', evoRenderImageCheck, false);</script>
        </div>
        <small class="form-text text-muted">@lang($field['description'])</small>
    </div>
</div>
