<header class="topbar">
     <div class="container-fluid">
          <div class="navbar-header">
               <div class="d-flex align-items-center gap-2">
                    <!-- Menu Toggle Button -->
                    <div class="topbar-item">
                         <button type="button" class="button-toggle-menu topbar-button">
                              <iconify-icon icon="solar:hamburger-menu-broken" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <!-- App Search-->
                   {{--  <form class="app-search d-none d-md-block me-auto">
                         <div class="position-relative">
                              <input type="search" class="form-control" placeholder="Search..." autocomplete="off" value="">
                              <iconify-icon icon="solar:magnifer-broken" class="search-widget-icon"></iconify-icon>
                         </div>
                    </form> --}}
               </div>

               <div class="d-flex align-items-center gap-1">
                    <!-- Theme Color (Light/Dark) -->
                    <div class="topbar-item">
                         <button type="button" class="topbar-button" id="light-dark-mode">
                              <iconify-icon icon="solar:moon-broken" class="fs-24 align-middle light-mode"></iconify-icon>
                              <iconify-icon icon="solar:sun-broken" class="fs-24 align-middle dark-mode"></iconify-icon>
                         </button>
                    </div>
                   {{--  <div class="topbar-item">
                         <button type="button" class="topbar-button" id="toggle-language">
                             <iconify-icon icon="flag:us" class="fs-24 align-middle translation en-flag" 
                                 style="display: {{ app()->getLocale() == 'en' ? 'none' : 'inline' }};"></iconify-icon>
                             <iconify-icon icon="flag:sa" class="fs-24 align-middle translation ar-flag" 
                                 style="display: {{ app()->getLocale() == 'ar' ? 'none' : 'inline' }};"></iconify-icon>
                         </button>
                     </div> --}}
                    <!-- Category -->
                    <div class="dropdown topbar-item d-none d-lg-flex">
                         <button type="button" class="topbar-button" data-toggle="fullscreen">
                              <iconify-icon icon="solar:full-screen-broken" class="fs-24 align-middle fullscreen"></iconify-icon>
                              <iconify-icon icon="solar:quit-full-screen-broken" class="fs-24 align-middle quit-fullscreen"></iconify-icon>
                         </button>
                    </div>

                    <!-- Theme Setting -->
                    {{-- <div class="topbar-item d-none d-md-flex">
                         <button type="button" class="topbar-button" id="theme-settings-btn" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas">
                              <iconify-icon icon="solar:settings-broken" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div> --}}

                    <!-- User -->
                    <div class="dropdown topbar-item">
                         <a type="button" class="topbar-button" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="d-flex align-items-center">
                                   <img class="rounded-circle" width="32" src="/images/users/avatar-1.jpg" alt="avatar-3">
                              </span>
                         </a>
                         <div class="dropdown-menu dropdown-menu-end">
                              <!-- item-->
                              <h6 class="dropdown-header">Welcome {{ Auth::user()->name }}!</h6>

                              <div class="dropdown-divider my-1"></div>


                              <form method="GET" action="{{ route('logout') }}" x-data>
                                   @csrf
                                   <button class="dropdown-item text-danger" type="submit">
                                       <iconify-icon icon="solar:logout-3-outline"
                                           class="align-middle me-2 fs-18"></iconify-icon>
                                       <span class="align-middle">Logout</span>
                                   </button>
       
                               </form>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</header>
<script>
     
document.addEventListener("DOMContentLoaded", function () {
    const langButton = document.getElementById("toggle-language");

    if (langButton) {
        langButton.addEventListener("click", function () {
            let currentLang = document.documentElement.lang === "ar" ? "en" : "ar";
            window.location.href = `/change-language/${currentLang}`;
        });
    }
});
</script>