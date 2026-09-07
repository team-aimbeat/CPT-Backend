<aside class="sidebar sidebar-default navs-rounded-all">
    <div class="sidebar-header d-flex align-items-center justify-content-center">
        <a href="{{route('dashboard')}}" class="navbar-brand">
            <div class="logo-main">
                <img class="logo-normal img-fluid site_logo_preview" src="{{ getSingleMedia(appSettingData('get'),'site_logo',null) }}" height="90" alt="site_logo">
                <img class="logo-normal dark-normal site_dark_logo_preview img-fluid" src="{{ getSingleMedia(appSettingData('get'),'site_dark_logo',null) }}" height="90" alt="site_dark_logo">
                <img class="logo-mini img-fluid site_mini_logo_preview" src="{{ getSingleMedia(appSettingData('get'), 'site_mini_logo',null) }}" height="90" alt="mini_logo">
                <img class="logo-mini dark-mini site_dark_mini_logo_preview img-fluid" src="{{ getSingleMedia(appSettingData('get'), 'site_dark_mini_logo',null) }}" height="90" alt="dark_mini_logo">
            </div>
        </a>
        <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
            <i class="icon">
                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </i>
        </div>
    </div>
    <div class="sidebar-body pt-0 data-scrollbar pb-5">
        <div class="sidebar-list" id="sidebar">
        <ul class="navbar-nav iq-main-menu">
            <li class="nav-item">
                <a class="{{ request()->is('admin/companies*') ? 'nav-link active' : 'nav-link' }}" href="{{ route('companies.index') }}">
                    <i class="icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.75 21.25H20.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M5.75 21.25V5.75C5.75 4.64543 6.64543 3.75 7.75 3.75H16.25C17.3546 3.75 18.25 4.64543 18.25 5.75V21.25" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M9 8H10.5M13.5 8H15M9 11.5H10.5M13.5 11.5H15M9 15H10.5M13.5 15H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </i>
                    <span class="item-name">Companies</span>
                </a>
            </li>
        </ul>
        @include('partials.dashboard.vertical-nav') 
        </div>
    </div>
    <div class="sidebar-footer"></div>
</aside>
