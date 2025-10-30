<div id="sidebar-menu">
    <ul class="metismenu list-unstyled" id="side-menu">
        <li class="menu-title" data-key="t-menu">Menu</li>

        <li>
            <a href="{{ route('dashboard') }}">
                <i data-feather="home"></i>
                <span data-key="t-dashboard">Dashboard</span>
            </a>
        </li>

        @if (user_has_role(['admin', 'head_telemarketing']))
            <li>
                <a href="{{ route('users.index') }}">
                    <i data-feather="users"></i>
                    <span data-key="t-users">User</span>
                </a>
            </li>
        @endif

        @if (user_has_role('admin'))
            <li>
                <a href="{{ route('outlet-call-logs.index') }}">
                    <i class="fas fa-history"></i>
                    <span data-key="t-outlet-call-logs">Log Outlet Call</span>
                </a>
            </li>
        @endif

        @if (user_has_role(['head_telemarketing', 'admin']))
            <li>
                <a href="{{ route('approve-outlet-call.index') }}">
                    <i class="fas fa-user"></i>
                    <span data-key="t-outlet-calls">Approve Outlet Call</span>
                </a>
            </li>

            <li>
                <a href="{{ route('outlet-call.index') }}">
                    <i class="fas fa-phone-alt"></i>
                    <span data-key="t-outlet-calls">Outlet Call</span>
                </a>
            </li>
        @endif

        @if (user_has_role(['admin', 'leader', 'head_telemarketing', 'it_pusat']))
            <li>
                <a href="{{ route('non-ordering-outlets.index') }}">
                    <i class="fas fa-times-circle"></i>
                    <span data-key="t-non-ordering-outlets">Non Ordering Outlets</span>
                </a>
            </li>
        @endif

        {{-- @if (user_has_role(['admin', 'leader', 'head_telemarketing']))
            <li>
                <a href="{{ route('outlet-call-dashboard') }}">
                    <i class="fas fa-chart-bar"></i>
                    <span data-key="t-outlet-call-dashboard">Dashboard Outlet Call</span>
                </a>
            </li>
        @endif --}}

        @if (user_has_role(['admin', 'leader', 'head_telemarketing', 'telemarketing', 'it_pusat']))
            <li>
                <a href="{{ route('outlet-list.index') }}">
                    <i class="fas fa-list"></i>
                    <span data-key="t-outlet-list">Jadwal Call</span>
                </a>
            </li>
        @endif

        @if (user_has_role(['admin', 'leader', 'head_telemarketing', 'it_pusat']))
            <li>
                <a href="{{ route('bypass-outlet.index') }}">
                    <i class="fas fa-route"></i>
                    <span data-key="t-bypass-outlet">Bypass Outlet</span>
                </a>
            </li>

            <li>
                <a href="{{ route('approval-bypass-outlet.index') }}">
                    <i class="fas fa-check-circle"></i>
                    <span data-key="t-approval-bypass-outlet">Approval Bypass Outlet</span>
                </a>
            </li>
        @endif

        @if (user_has_role(['leader', 'it_pusat']))
            <li>
                <a href="{{ route('outlet-call.index') }}">
                    <i class="fas fa-phone-alt"></i>
                    <span data-key="t-outlet-calls">Outlet Call</span>
                </a>
            </li>
        @endif



        @if (!user_has_role(['kepala_non_operasional', 'head_telemarketing', 'leader']))
            <li>
                <a href="{{ route('transactions.index') }}">
                    <i class="fas fa-ticket-alt"></i>
                    <span data-key="t-transactions">Service Require</span>
                </a>
            </li>

            <li>
                <a href="{{ route('edit-transactions.index') }}">
                    <i class="fas fa-user-edit"></i>
                    <span data-key="t-transactions">Edit Pesanan</span>
                </a>
            </li>

            <li>
                <a href="{{ route('dso-order.index') }}">
                    <i class="fas fa-fire-alt"></i>
                    <span data-key="t-transactions">Order DSO</span>
                </a>
            </li>
        @endif

        @if (user_has_role(['kepala_non_operasional', 'admin']))
            <li>
                <a href="{{ route('approve-transactions.index') }}">
                    <i class="fas fa-check"></i>
                    <span data-key="t-transactions">Approve Pesanan</span>
                </a>
            </li>
        @endif

        @if (user_has_role(['telemarketing', 'admin']))
            <li>
                <a href="{{ route('inquiry.index') }}">
                    <i class="fas fa-question-circle"></i>
                    <span data-key="t-inquiry">Inquiry</span>
                </a>
            </li>

            <li>
                <a href="{{ route('complaints.index') }}">
                    <i class="fas fa-comment-dots"></i>
                    <span data-key="t-complaints">Complaint</span>
                </a>
            </li>
        @endif

    </ul>
</div>
