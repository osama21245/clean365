<div class="mb-20 nav-tabs-responsive position-relative">
    <ul class="nav nav--tabs scrollbar-w flex-nowrap white-nowrap overflow-x-auto flex-wrap-nowrap nav--tabs__style2">
        <li class="nav-item">
            <a href="{{ route('admin.configuration.third-party', ['webPage' => 'payment_config', 'type' => 'digital_payment']) }}" class="nav-link {{ request()->has('type') && request()->type == 'digital_payment' ? 'active' : '' }}">
                {{translate('Digital Payment')}}
            </a>
        </li>
    </ul>
</div>
