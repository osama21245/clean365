<li class="nav-item">
    <a class="nav-link {{ $webPage == 'map-api' ? 'active' : '' }}"
       href="{{ route('admin.configuration.third-party', 'map-api') }}">
        {{translate('Map Api')}}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ $webPage == 'storage_connection' ? 'active' :'' }}"
       href="{{ route('admin.configuration.third-party', 'storage_connection') }}">
        {{translate('Storage Connection')}}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ $webPage=='app_settings'?'active':'' }}"
       href="{{ route('admin.configuration.third-party', 'app_settings') }}">
        {{translate('App Settings')}}
    </a>
</li>
