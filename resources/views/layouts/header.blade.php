<div class="navbar-header">
    <div class="d-flex">
        <!-- LOGO -->
        <div class="navbar-brand-box">
            <a href="#" class="logo logo-dark">
                <span class="logo-lg">
                    <span class="logo-txt">Telemarketing</span>
                </span>
            </a>
        </div>

        <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
            <i class="fa fa-fw fa-bars"></i>
        </button>
    </div>

    <div class="d-flex">

        <div class="dropdown d-inline-block">
            <button type="button" class="btn header-item noti-icon position-relative" id="page-header-notifications-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i data-feather="bell" class="icon-lg"></i>
                <span class="badge bg-danger rounded-pill" id="cart-amount"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                 aria-labelledby="page-header-notifications-dropdown">
                <div class="p-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-0"> Cart </h6>
                        </div>
                    </div>
                </div>
                <div data-simplebar="init" style="max-height: 230px;">
                    <div class="simplebar-wrapper" style="margin: 0px;">
                      <div class="simplebar-height-auto-observer-wrapper">
                        <div class="simplebar-height-auto-observer"></div>
                      </div>
                      <div class="simplebar-mask">
                        <div class="simplebar-offset" style="right: -16.8px; bottom: 0px;">
                          <div class="simplebar-content-wrapper" style="height: auto; overflow: hidden scroll;">
                            <div class="simplebar-content" style="padding: 0px;">
                                <div id="cart"></div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="simplebar-placeholder" style="width: auto; height: 410px;"></div>
                    </div>
                    <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                      <div class="simplebar-scrollbar" style="transform: translate3d(0px, 0px, 0px); display: none;"></div>
                    </div>
                    <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
                      <div class="simplebar-scrollbar" style="transform: translate3d(0px, 100px, 0px); display: block; height: 129px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dropdown d-inline-block">
            <button type="button" class="btn header-item bg-soft-light border-start border-end" id="page-header-user-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img class="rounded-circle header-profile-user" src="{{ asset('assets/images/users/avatar-1.jpg') }}"
                     alt="Header Avatar">
                <span class="d-none d-xl-inline-block ms-1 fw-medium">{{ Auth::user()->name }}.</span>
                <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <!-- item-->
                <a class="dropdown-item" href="{{ route('change-password') }}"><i class="mdi mdi-lock font-size-16 align-middle me-1"></i> Ganti Password</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" id="logout-btn"><i class="mdi mdi-logout font-size-16 align-middle me-1"></i> Keluar</a>
            </div>
        </div>

    </div>
</div>