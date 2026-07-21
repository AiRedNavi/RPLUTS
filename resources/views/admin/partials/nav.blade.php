{{-- resources/views/admin/partials/nav.blade.php --}}
<div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('admin.users.index') }}" class="tw-nav-link @if(request()->routeIs('admin.users.*')) fw-semibold @endif">Users</a>
    <a href="{{ route('admin.ports.index') }}" class="tw-nav-link @if(request()->routeIs('admin.ports.*')) fw-semibold @endif">Ports</a>
    <a href="{{ route('admin.articles.index') }}" class="tw-nav-link @if(request()->routeIs('admin.articles.*')) fw-semibold @endif">Articles</a>
    <a href="{{ url('/') }}" class="tw-nav-link">← Dashboard Publik</a>
</div>