<!-- Main Sidebar Container -->
<aside class="main-sidebar bg-blue elevation-4">
  <!-- Brand Logo -->
  <a href="index3.html" class="brand-link">
    <img src="{{asset('yankes.png')}}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-bold">{{config('app.name')}}</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->

    <!-- Sidebar Menu -->
    <nav class="mt-2 text-capitalize">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
      <li class="nav-header">{{ __("menu") }}</li>
       <li class="nav-item">
          <a href="{{route('dashboard')}}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
            {{ __("dashboard") }}
            </p>
          </a>
        </li>

        <li class="nav-item {{ request()->routeIs('barang.*') ? 'menu-open' : '' }}">
          <a href="javascript:void(0)" class="nav-link text-white {{ request()->routeIs('barang.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-box"></i>
            <p>
            {{ __("master of goods") }}
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{route('barang.jenis')}}" class="nav-link text-white {{ request()->routeIs('barang.jenis') ? 'active' : '' }}">
              <i class="fas fa-angle-right"></i>
                <p>{{ __("category") }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('barang.satuan')}}" class="nav-link text-white {{ request()->routeIs('barang.satuan') ? 'active' : '' }}">
              <i class="fas fa-angle-right"></i>
                <p>{{ __("unit") }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('barang.merk')}}" class="nav-link text-white {{ request()->routeIs('barang.merk') ? 'active' : '' }}">
              <i class="fas fa-angle-right"></i>
                <p>{{ __("brand") }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('barang')}}" class="nav-link text-white {{ request()->routeIs('barang') && !request()->routeIs('barang.*') ? 'active' : '' }}">
              <i class="fas fa-angle-right"></i>
                <p>{{ __("goods") }}</p>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a href="{{route('customer')}}" class="nav-link text-white {{ request()->routeIs('customer') ? 'active' : '' }}">
            <i class="nav-icon far fa-user"></i>
            <p>
            {{ __("customer") }}
            </p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{route('supplier')}}" class="nav-link text-white {{ request()->routeIs('supplier') ? 'active' : '' }}">
            <i class="nav-icon fas fa-shipping-fast"></i>
            <p>
              {{ __("supplier") }}
            </p>
          </a>
        </li>
        <li class="nav-item {{ request()->routeIs('transaksi.*') ? 'menu-open' : '' }}">
          <a href="javascript:void(0)" class="nav-link text-white {{ request()->routeIs('transaksi.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-exchange-alt"></i>
            <p>
            {{ __("transaction") }}
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{route('transaksi.masuk')}}" class="nav-link text-white {{ request()->routeIs('transaksi.masuk') ? 'active' : '' }}">
              <i class="fas fa-angle-right"></i>
                <p>{{ __("incoming transaction") }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('transaksi.keluar')}}" class="nav-link text-white {{ request()->routeIs('transaksi.keluar') ? 'active' : '' }}">
              <i class="fas fa-angle-right"></i>
                <p>{{ __("outbound transaction") }}</p>
              </a>
            </li>
          </ul>
        </li>
        
        <!-- Menu Cuti Baru -->
        <li class="nav-item {{ request()->routeIs('cuti.*') ? 'menu-open' : '' }}">
          <a href="javascript:void(0)" class="nav-link text-white {{ request()->routeIs('cuti.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-envelope"></i>
            <p>
            {{ __("Cuti") }}
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <!-- Menu Pengajuan Cuti -->
            <li class="nav-item">
                <a href="{{ route('leave-application') }}" class="nav-link text-white {{ request()->routeIs('leave-application') ? 'active' : '' }}">
                    <i class="fas fa-angle-right"></i>
                    <p>{{ __("Pengajuan Cuti") }}</p>
                </a>
            </li>
        
            <!-- Menu Validasi Cuti -->
            <li class="nav-item">
                <a href="{{ route('leave-validation') }}" class="nav-link text-white {{ request()->routeIs('leave-validation') ? 'active' : '' }}">
                    <i class="fas fa-angle-right"></i>
                    <p>{{ __("Validasi Pengajuan Cuti") }}</p>
                </a>
            </li>
        </ul>
        </li>
        
        <li class="nav-item {{ request()->routeIs('laporan.*') ? 'menu-open' : '' }}">
          <a href="javascript:void(0)" class="nav-link text-white {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-print"></i>
            <p>
            {{ __("report") }}
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{route('laporan.masuk')}}" class="nav-link text-white {{ request()->routeIs('laporan.masuk') ? 'active' : '' }}">
              <i class="fas fa-angle-right"></i>
                <p>{{ __("incoming goods report") }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('laporan.keluar')}}" class="nav-link text-white {{ request()->routeIs('laporan.keluar') ? 'active' : '' }}">
              <i class="fas fa-angle-right"></i>
                <p>{{ __("outgoing goods report") }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('laporan.stok')}}" class="nav-link text-white {{ request()->routeIs('laporan.stok') ? 'active' : '' }}">
              <i class="fas fa-angle-right"></i>
                <p>{{ __("stock report") }}</p>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-header">{{ __("others") }}</li>
        <li class="nav-item {{ request()->routeIs('settings.*') ? 'menu-open' : '' }}">
          <a href="javascript:void(0)" class="nav-link text-white {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-cog"></i>
            <p>
            {{ __("setting") }}
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
          @if(Auth::user()->role->name != 'employee')
            <li class="nav-item">
              <a href="{{route('settings.employee')}}" class="nav-link text-white {{ request()->routeIs('settings.employee') ? 'active' : '' }}">
              <i class="fas fa-angle-right"></i>
                <p>{{ __("employee") }}</p>
              </a>
            </li>
          @endif
            <!-- <li class="nav-item">
              <a href="" class="nav-link text-white">
              <i class="fas fa-angle-right"></i>
                <p>web</p>
              </a>
            </li> -->
            <li class="nav-item">
              <a href="{{route('settings.profile')}}" class="nav-link text-white {{ request()->routeIs('settings.profile') ? 'active' : '' }}">
              <i class="fas fa-angle-right"></i>
                <p>{{ __("profile") }}</p>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item">
            <a href="{{route('login.delete')}}" class="nav-link text-white">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>
              {{ __("messages.logout") }}
              </p>
            </a>
        </li>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>

<!-- CSS tambahan untuk style menu aktif (tambahkan ke file CSS Anda) -->
<style>
  .nav-sidebar .nav-item .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2) !important;
    color: #ffffff !important;
    font-weight: bold;
  }
  
  .nav-sidebar .menu-open > .nav-link {
    background-color: rgba(255, 255, 255, 0.1) !important;
  }
  
  .nav-treeview > .nav-item > .nav-link.active {
    background-color: rgba(255, 255, 255, 0.3) !important;
    color: #ffffff !important;
    font-weight: bold;
  }
  
  .nav-sidebar .menu-open {
    background-color: rgba(0, 0, 0, 0.1);
  }
</style>