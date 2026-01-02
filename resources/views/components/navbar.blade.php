<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('dashboard') }}" class="nav-link">Home</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li>
            <a class="nav-link">
                <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'User' }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('privacy') }}" class="nav-link" target="_blank">
                <span class="d-none d-md-inline">Privacy Statement</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('refund') }}" class="nav-link" target="_blank">
                <span class="d-none d-md-inline">Refund Policy</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('terms') }}" class="nav-link" target="_blank">
                <span class="d-none d-md-inline">Terms Of Service</span>
            </a>
        </li>
        <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="nav-link btn btn-link" style="border: none; background: none; padding: 0.5rem 1rem;">
                    <span class="d-none d-md-inline">Logout</span>
                </button>
            </form>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
    </ul>
</nav>
<!-- /.navbar -->

