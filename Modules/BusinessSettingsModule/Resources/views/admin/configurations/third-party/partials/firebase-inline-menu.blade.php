{{-- Authentication tab removed; Configuration only --}}
<div class="mb-20 nav-tabs-responsive position-relative d-none">
    <ul class="nav nav--tabs scrollbar-w flex-nowrap white-nowrap overflow-x-auto flex-wrap-nowrap nav--tabs__style2">
        <li class="nav-item">
            <a href="{{ route('admin.configuration.third-party', 'firebase-configuration') }}" class="nav-link {{ $webPage == 'firebase-configuration' ? 'active' : '' }}">
                {{translate('Configuration')}}
            </a>
        </li>
        {{--
        <li class="nav-item">
            <a href="{{  route('admin.configuration.third-party', 'firebase-authentication') }}" class="nav-link  {{ $webPage == 'firebase-authentication' ? 'active' : '' }}">
                {{translate('Authentication')}}
            </a>
        </li>
        --}}
    </ul>
</div>
