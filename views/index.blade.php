@extends('manager::template.page')
@section('content')
    <h1><i class="@lang('sSettings::global.icon')" data-tooltip="@lang('sSettings::global.description')"></i>@lang('sSettings::global.title')</h1>
    <form name="ssettings" id="ssettings" class="content" method="post" action="{{sSettings::route('sSettings.update-settings')}}" onsubmit="documentDirty=false;">
        <div class="sectionBody">
            <div class="tab-pane" id="resourcesPane">
                <script>tpResources = new WebFXTabPane(document.getElementById('resourcesPane'), false);</script>
                @foreach($tabs as $tabId => $tab)
                    <div class="tab-page {{$tabId}}" id="{{$tabId}}">
                        <h2 class="tab">
                            @if(trim($tab['label']))
                                <span>@lang($tab['label'])</span>
                            @else
                                <span>@lang('sSettings::global.no_title')</span>
                            @endif
                        </h2>
                        <script>tpResources.addTabPage(document.getElementById('{{$tabId}}'));</script>
                        <div class="container container-body">
                            @if(isset($tab['fields']) && is_array($tab['fields']) && count($tab['fields']))
                                @foreach($tab['fields'] as $field)
                                    @include('sSettings::partials.'.$field['type'].'Type')
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </form>
@endsection
@push('scripts.top')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    @include('sSettings::partials.style')
    <script>
        function evoRenderImageCheck(a) {
            var b = document.getElementById('image_for_' + a.target.id),
                c = new Image;
            a.target.value ? (c.src = "<?php echo evo()->getConfig('site_url')?>" + a.target.value, c.onerror = function () {
                b.style.backgroundImage = '', b.setAttribute('data-image', '');
            }, c.onload = function () {
                b.style.backgroundImage = 'url(\'' + this.src + '\')', b.setAttribute('data-image', this.src);
            }) : (b.style.backgroundImage = '', b.setAttribute('data-image', ''));
        }
    </script>
@endpush
@push('scripts.bot')
    <div id="actions">
        <div class="btn-group">
            <a id="Button1" class="btn btn-success" href="javascript:void(0);" onclick="saveForm('#ssettings');">
                <i class="fa fa-floppy-o"></i>
                <span>@lang('global.save')</span>
            </a>
            @if(evo()->hasPermission('settings', 'mgr'))
                <a id="Button4" class="btn btn-warning" href="{{sSettings::route('sSettings.configure')}}">
                    <i class="fa fa-hammer"></i><span>@lang('global.edit_settings')</span>
                </a>
            @endif
        </div>
    </div>
    <script src="media/script/bootstrap/js/bootstrap.min.js"></script>
    <script> function saveForm(selector){$(selector).submit()}</script>
    <script>
        function changestate(el) {
            documentDirty = true;
            if(parseInt(el.value) === 1) {
                el.value = 0;
            } else {
                el.value = 1;
            }
        }
    </script>
    <div id="copyright"><a href="https://seigerit.com/" target="_blank"><img src="{{evo()->getConfig('site_url', '/')}}assets/site/seigerit-blue.svg"/></a></div>
@endpush
