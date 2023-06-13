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
@push('scripts.bot')
    <div id="actions">
        <div class="btn-group">
            <a id="Button1" class="btn btn-success" href="javascript:void(0);" onclick="saveForm('#ssettings');">
                <i class="fa fa-floppy-o"></i>
                <span>@lang('global.save')</span>
            </a>
            @if(evo()->hasPermission('settings'))
                <a id="Button4" class="btn btn-warning" href="{{sSettings::route('sSettings.configure')}}">
                    <i class="fa fa-hammer"></i><span>@lang('global.edit_settings')</span>
                </a>
            @endif
        </div>
    </div>
    <script>
        function saveForm(selector) {
            $(selector).submit();
        }
    </script>
    <style>
        #copyright{position:fixed;bottom:0;right:0;background-color:#0057b8;padding:3px 7px;border-radius:5px;}
        #copyright img{width:9em;}
    </style>
    <div id="copyright"><a href="https://seigerit.com/" target="_blank"><img src="/assets/site/seirgerit-yellow.svg"/></a></div>
@endpush
