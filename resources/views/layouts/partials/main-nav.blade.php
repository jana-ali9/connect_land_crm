<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="{{ route('any', 'index') }}" class="logo-dark">
            <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
            <img src="/images/logo-dark.png" class="logo-lg" alt="logo dark">
        </a>

        <a href="{{ route('any', 'index') }}" class="logo-light">
            <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
            <img src="/images/logo-light.png" class="logo-lg" alt="logo light">
        </a>
    </div>

    <!-- Menu Toggle Button (sm-hover) -->
    <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
        <iconify-icon icon="solar:hamburger-menu-broken" class="button-sm-hover-icon"></iconify-icon>
    </button>

    <div class="scrollbar" data-simplebar>

        <ul class="navbar-nav" id="navbar-nav">

            <li class="menu-title">Menu</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('any') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:home-2-broken"></iconify-icon>
                    </span>
                    <span class="nav-text"> Dashboard </span>
                    <span class="badge bg-success badge-pill text-end">9+</span>
                </a>
            </li>

            @php($Name = 'users')
            @if (auth()->user()->hasPermission("read $Name"))
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarAdmin" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarAdmin">
                        <span class="nav-icon">
                            <iconify-icon icon="mingcute:user-3-line"></iconify-icon>
                        </span>
                        <span class="nav-text"> Admins </span>
                    </a>
                    <div class="collapse" id="sidebarAdmin">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('admins.index') }}">show admins</a>
                            </li>
                            @if (auth()->user()->hasPermission("create $Name"))
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="{{ route('admins.create') }}">create admins</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif


            @php($Name = 'roles')
            @if (auth()->user()->hasPermission("read $Name"))
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#{{ 'sidebar' . $Name }}"data-bs-toggle="collapse"
                        role="button" aria-expanded="false" aria-controls="{{ 'sidebar' . $Name }}">
                        <span class="nav-icon">
                            <iconify-icon icon="mingcute:bug-line"></iconify-icon>
                        </span>
                        <span class="nav-text">{{ $Name }}</span>
                    </a>
                    <div class="collapse" id="{{ 'sidebar' . $Name }}">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route("$Name.index") }}">all {{ $Name }}</a>
                            </li>

                            @if (auth()->user()->hasPermission("create $Name"))
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="{{ route("$Name.create") }}">create
                                        {{ $Name }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif

            @php($Name = 'clients')
            @if (auth()->user()->hasPermission("read $Name"))
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#{{ 'sidebar' . $Name }}" data-bs-toggle="collapse"
                        role="button" aria-expanded="false" aria-controls="{{ 'sidebar' . $Name }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:user-linear"></iconify-icon>
                        </span>
                        <span class="nav-text">{{ $Name }}</span>
                    </a>
                    <div class="collapse" id="{{ 'sidebar' . $Name }}">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route("$Name.index") }}">all {{ $Name }}</a>
                            </li>

                            @if (auth()->user()->hasPermission("create $Name"))
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="{{ route("$Name.create") }}">create
                                        {{ $Name }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
            @php($Name = 'buildings')
            @if (auth()->user()->hasPermission("read $Name"))
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#{{ 'sidebar' . $Name }}" data-bs-toggle="collapse"
                        role="button" aria-expanded="false" aria-controls="{{ 'sidebar' . $Name }}">
                        <span class="nav-icon">
                            <iconify-icon icon="fluent:building-24-regular"></iconify-icon>
                        </span>
                        <span class="nav-text">{{ $Name }}</span>
                    </a>
                    <div class="collapse" id="{{ 'sidebar' . $Name }}">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route("$Name.index") }}">all
                                    {{ $Name }}</a>
                            </li>

                            @if (auth()->user()->hasPermission("create $Name"))
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="{{ route("$Name.create") }}">create
                                        {{ $Name }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
            @php($Name = 'lands')
            @if (auth()->user()->hasPermission("read $Name"))
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#{{ 'sidebar' . $Name }}" data-bs-toggle="collapse"
                        role="button" aria-expanded="false" aria-controls="{{ 'sidebar' . $Name }}">
                        <span class="nav-icon">
                            <iconify-icon icon="mdi:map" width="24"></iconify-icon>
                        </span>
                        <span class="nav-text">{{ ucfirst($Name) }}</span>
                    </a>
                    <div class="collapse" id="{{ 'sidebar' . $Name }}">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route("$Name.index") }}">
                                    All {{ ucfirst($Name) }}
                                </a>
                            </li>

                            @if (auth()->user()->hasPermission("create $Name"))
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="{{ route("$Name.create") }}">
                                        Create {{ ucfirst($Name) }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif

            @php($Name = 'units')
            @if (auth()->user()->hasPermission("read $Name"))
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#{{ 'sidebar' . $Name }}" data-bs-toggle="collapse"
                        role="button" aria-expanded="false" aria-controls="{{ 'sidebar' . $Name }}">
                        <span class="nav-icon">
                            <iconify-icon icon="cil:room"></iconify-icon>
                        </span>
                        <span class="nav-text">{{ $Name }}</span>
                    </a>
                    <div class="collapse" id="{{ 'sidebar' . $Name }}">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route("$Name.index") }}">all
                                    {{ $Name }}</a>
                            </li>

                            @if (auth()->user()->hasPermission("create $Name"))
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="{{ route("$Name.create") }}">create
                                        {{ $Name }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
            @php($Name = 'services')
            @if (auth()->user()->hasPermission("read $Name"))
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#{{ 'sidebar' . $Name }}" data-bs-toggle="collapse"
                        role="button" aria-expanded="false" aria-controls="{{ 'sidebar' . $Name }}">
                        <span class="nav-icon">
                            <iconify-icon icon="icons8:services"></iconify-icon>
                        </span>
                        <span class="nav-text">Services & Feature</span>
                    </a>
                    <div class="collapse" id="{{ 'sidebar' . $Name }}">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route("$Name.index") }}">all services & feature</a>
                            </li>

                            @if (auth()->user()->hasPermission("create $Name"))
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="{{ route("$Name.create") }}">create services &
                                        feature</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
            @php($Name = 'contracts')
            @if (auth()->user()->hasPermission("read $Name"))
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#{{ 'sidebar' . $Name }}" data-bs-toggle="collapse"
                        role="button" aria-expanded="false" aria-controls="{{ 'sidebar' . $Name }}">
                        <span class="nav-icon">
                            <iconify-icon icon="hugeicons:contracts"></iconify-icon>
                        </span>
                        <span class="nav-text">{{ $Name }}</span>
                    </a>
                    <div class="collapse" id="{{ 'sidebar' . $Name }}">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route("$Name.index") }}">all
                                    {{ $Name }}</a>
                            </li>

                            @if (auth()->user()->hasPermission("create $Name"))
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="{{ route("$Name.create") }}">create renting
                                        {{ $Name }}</a>
                                </li>

                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="{{ route("$Name.createbuilding") }}">create selling
                                        {{ $Name }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
            @php($Name = 'invoices')
            @if (auth()->user()->hasPermission("read $Name"))
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#{{ 'sidebar' . $Name }}" data-bs-toggle="collapse"
                        role="button" aria-expanded="false" aria-controls="{{ 'sidebar' . $Name }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:bill-list-broken"></iconify-icon>
                        </span>
                        <span class="nav-text">{{ $Name }}</span>
                    </a>
                    <div class="collapse" id="{{ 'sidebar' . $Name }}">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route("$Name.index") }}">all
                                    {{ $Name }}</a>
                            </li>

                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href=" {{ route("$Name.history") }}">Payment history</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            @php($Name = 'expenseOffers')
            @if (auth()->user()->hasPermission("read $Name"))
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#{{ 'sidebar' . $Name }}" data-bs-toggle="collapse"
                        role="button" aria-expanded="false" aria-controls="{{ 'sidebar' . $Name }}">
                        <span class="nav-icon">
                            <iconify-icon icon="ic:outline-local-offer"></iconify-icon>
                        </span>
                        <span class="nav-text">Expense Offers</span>
                    </a>
                    <div class="collapse" id="{{ 'sidebar' . $Name }}">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route("$Name.index") }}">all
                                    Expense Offers</a>
                            </li>
                            @if (auth()->user()->hasPermission("create $Name"))
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link" href="{{ route("$Name.create") }}">Create Expense</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
        </ul>
    </div>
</div>
