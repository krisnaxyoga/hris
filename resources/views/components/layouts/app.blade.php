@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="corporate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · HRIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200">
    <div class="drawer lg:drawer-open">
        <input id="main-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex flex-col">
            {{-- Topbar --}}
            <div class="navbar bg-base-100 border-b border-base-300 sticky top-0 z-30">
                <div class="flex-none lg:hidden">
                    <label for="main-drawer" class="btn btn-square btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </label>
                </div>
                <div class="flex-1">
                    <span class="text-lg font-semibold px-2">{{ $title }}</span>
                </div>
                <div class="flex-none gap-2">
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost gap-2">
                            <div class="avatar avatar-placeholder">
                                <div class="bg-primary text-primary-content w-8 rounded-full">
                                    <span class="text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                                </div>
                            </div>
                            <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                        </div>
                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-50 w-56 p-2 shadow border border-base-300">
                            <li class="menu-title">{{ auth()->user()->email }}</li>
                            <li class="menu-title text-xs">{{ auth()->user()->getRoleNames()->implode(', ') }}</li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="text-error w-full text-left">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Page content --}}
            <main class="p-4 sm:p-6 flex-1">
                <x-ui.flash />
                {{ $slot }}
            </main>
        </div>

        {{-- Sidebar --}}
        <div class="drawer-side z-40">
            <label for="main-drawer" class="drawer-overlay"></label>
            <aside class="bg-base-100 border-r border-base-300 w-64 min-h-full flex flex-col">
                <div class="p-4 border-b border-base-300">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-primary">HRIS<span class="text-base-content/60">.</span></a>
                    <p class="text-xs text-base-content/60 mt-1 truncate">{{ auth()->user()->company?->name ?? 'No company' }}</p>
                </div>
                <ul class="menu px-3 py-4 gap-1 flex-1">
                    <li>
                        <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>
                            Dashboard
                        </a>
                    </li>
                    <li class="menu-title">Organization</li>
                    @can('viewAny', App\Models\Company::class)
                        <li><a href="{{ route('companies.index') }}" @class(['active' => request()->routeIs('companies.*')])>Companies</a></li>
                    @endcan
                    @can('viewAny', App\Models\Department::class)
                        <li><a href="{{ route('departments.index') }}" @class(['active' => request()->routeIs('departments.*')])>Departments</a></li>
                    @endcan
                    @can('viewAny', App\Models\Position::class)
                        <li><a href="{{ route('positions.index') }}" @class(['active' => request()->routeIs('positions.*')])>Positions</a></li>
                    @endcan
                    <li class="menu-title">People</li>
                    @can('viewAny', App\Models\EmployeeProfile::class)
                        <li><a href="{{ route('employees.index') }}" @class(['active' => request()->routeIs('employees.*')])>Employees</a></li>
                    @endcan
                    @can('viewAny', App\Models\User::class)
                        <li><a href="{{ route('users.index') }}" @class(['active' => request()->routeIs('users.*')])>Users</a></li>
                    @endcan
                    <li class="menu-title">Time & Attendance</li>
                    <li><a href="{{ route('attendance.me') }}" @class(['active' => request()->routeIs('attendance.me')])>My Attendance</a></li>
                    @can('viewAny', App\Models\Attendance::class)
                        <li><a href="{{ route('attendance.index') }}" @class(['active' => request()->routeIs('attendance.index')])>Attendance Log</a></li>
                    @endcan
                    <li class="menu-title">Leave</li>
                    <li><a href="{{ route('leave.me') }}" @class(['active' => request()->routeIs('leave.me')])>My Leave</a></li>
                    <li><a href="{{ route('leave.approvals') }}" @class(['active' => request()->routeIs('leave.approvals')])>Leave Approvals</a></li>
                    <li class="menu-title">Work Arrangements</li>
                    <li><a href="{{ route('work-arrangements.me') }}" @class(['active' => request()->routeIs('work-arrangements.me')])>WFH / Business Trip</a></li>
                    <li><a href="{{ route('work-arrangements.approvals') }}" @class(['active' => request()->routeIs('work-arrangements.approvals')])>WFH Approvals</a></li>
                    <li><a href="{{ route('timesheets.index') }}" @class(['active' => request()->routeIs('timesheets.*')])>Timesheets</a></li>
                </ul>
                <div class="p-4 text-xs text-base-content/40 border-t border-base-300">HRIS · Phase 1</div>
            </aside>
        </div>
    </div>
</body>
</html>
