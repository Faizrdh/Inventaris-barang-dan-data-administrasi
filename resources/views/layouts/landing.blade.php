<!-- landing.blade.php -->
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="menejemen inventaris barang dan pengajuan cuti">
  <meta name="csrf-token" content="{{csrf_token()}}">
  <title>{{config('app.name')}} | Sistem Inventaris & Pengajuan Cuti</title>
  
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{asset('theme/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('theme/dist/css/adminlte.min.css')}}">
  <!-- jQuery -->
  <script src="{{asset('theme/plugins/jquery/jquery.min.js')}}"></script>
  <!-- sweetalert -->
  <script src="{{asset('theme/alert/js/sweetalert2.js')}}"></script>
  <link rel="stylesheet" href="{{ asset("localizations/flags.css") }}">
  <style>
    :root {
      --primary-gradient-start: #0051ff;
      --primary-gradient-end: #00a5ff;
      --text-light: #ffffff;
    }
    
    body {
      font-family: 'Source Sans Pro', sans-serif;
      background-color: #f4f6f9;
      color: #333;
    }
    
    .header-section {
      background-image: linear-gradient(to right, var(--primary-gradient-start), var(--primary-gradient-end));
      padding: 1rem 0;
      color: var(--text-light);
    }
    
    .hero-section {
      background-image: linear-gradient(135deg, var(--primary-gradient-start), var(--primary-gradient-end));
      color: var(--text-light);
      padding: 4rem 0;
      margin-bottom: 2rem;
    }
    
    .hero-title {
      font-weight: bold;
      font-size: 2.5rem;
      margin-bottom: 1rem;
    }
    
    .hero-subtitle {
      font-size: 1.2rem;
      max-width: 700px;
      margin: 0 auto;
    }
    
    .features-section {
      padding: 3rem 0;
      text-align: center;
    }
    
    .feature-title {
      font-size: 2rem;
      margin-bottom: 3rem;
      font-weight: bold;
    }
    
    .feature-card {
      padding: 2rem;
      margin-bottom: 2rem;
      background-color: #fff;
      border-radius: 5px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease;
      height: 100%;
    }
    
    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .feature-icon {
      font-size: 3rem;
      color: var(--primary-gradient-end);
      margin-bottom: 1rem;
    }
    
    .feature-card h4 {
      font-weight: bold;
      margin-bottom: 1rem;
    }
    
    .status-section {
      padding: 3rem 0;
      background-color: #fff;
    }
    
    .status-title {
      font-size: 2rem;
      margin-bottom: 2rem;
      text-align: center;
      font-weight: bold;
    }
    
    .status-table th {
      background-color: #f4f6f9;
    }
    
    .status-approved {
      background-color: #c3e6cb;
      color: #155724;
      padding: 5px 10px;
      border-radius: 5px;
      font-weight: bold;
    }
    
    .status-pending {
      background-color: #ffeeba;
      color: #856404;
      padding: 5px 10px;
      border-radius: 5px;
      font-weight: bold;
    }
    
    .status-rejected {
      background-color: #f8d7da;
      color: #721c24;
      padding: 5px 10px;
      border-radius: 5px;
      font-weight: bold;
    }
    
    .footer-section {
      background-image: linear-gradient(to right, var(--primary-gradient-start), var(--primary-gradient-end));
      color: var(--text-light);
      padding: 2rem 0;
    }
    
    .footer-links a {
      color: var(--text-light);
      margin: 0 10px;
    }
    
    .login-btn {
      background-color: var(--text-light);
      color: var(--primary-gradient-start);
      border-radius: 5px;
      padding: 8px 24px;
      font-weight: bold;
      transition: all 0.3s ease;
    }
    
    .login-btn:hover {
      background-color: rgba(255, 255, 255, 0.9);
      color: var(--primary-gradient-start);
      transform: translateY(-2px);
    }
    
    .logo-img {
      height: 40px;
      margin-right: 10px;
    }
    
    .lang-icon {
      background-image: url('{{ asset("localizations/flags.png") }}');
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .hero-title {
        font-size: 2rem;
      }
      
      .hero-subtitle {
        font-size: 1rem;
      }
      
      .feature-title, .status-title {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img src="{{asset('loading.gif')}}" alt="loading" height="60" width="60">
  </div>

  <!-- Header -->
  <header class="header-section">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center">
       <div class="d-flex align-items-center">
    <img src="{{ asset('yankes.png') }}" alt="Logo" class="logo-img" style="width: 80px; height: auto;">
      <div class="ms-3 text-white">
          <h1 class="mb-0 font-weight-bold" style="font-size: 28px;">SIMADA</h1>
          <p class="mb-0" style="font-size: 18px;">Sistem Informasi Manajemen Administrasi & Inventaris</p>
        </div>
</div>

        <div>
          <a href="{{ route('login') }}" class="btn login-btn">Login</a>
          <div class="btn-group ml-2">
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero-section text-center">
    <div class="container">
      <h1 class="hero-title">Sistem Informasi Manajemen Administrasi & Inventaris</h1>
      <p class="hero-subtitle">Kelola inventaris, dan data administrasi dengan lebih mudah dan terstruktur dalam satu platform</p>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features-section">
    <div class="container">
      <h2 class="feature-title">Fitur Utama</h2>
      <div class="row">
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-boxes"></i>
            </div>
            <h4>Manajemen Inventaris</h4>
            <p>Kelola seluruh aset dan barang dengan sistem tracking yang terorganisir dan mudah di pantau</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="far fa-calendar-alt"></i>
            </div>
            <h4>Pengajuan Cuti</h4>
            <p>Ajukan dan kelola cuti dengan mudah, pantau status persetujuan secara real-time</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-chart-bar"></i>
            </div>
            <h4>Pelaporan</h4>
            <p>Dapatkan insight untuk pengambilan keputusan dari data inventaris, pengajuan cuti dan laporan yang komprehensif</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Status Section -->
  <section class="status-section">
    <div class="container">
      <h2 class="status-title">Status pengajuan cuti</h2>
      <div class="table-responsive">
        <table class="table table-bordered status-table">
          <thead>
            <tr>
              <th width="5%">NO</th>
              <th width="20%">Nama</th>
              <th width="10%">NIP</th>
              <th width="15%">Tipe Izin</th>
              <th width="15%">Tanggal mulai</th>
              <th width="15%">Tanggal selesai</th>
              <th width="15%">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($leaveApplications as $key => $application)
            <tr>
              <td>{{ $key + 1 }}</td>
              <td>{{ $application->name }}</td> 
              <td>{{ $application->employee_id }}</td>
              <td>{{ $application->leave_type }}</td>
              <td>{{ $application->start_date ? $application->start_date->format('Y-m-d') : '-' }}</td> {{-- PERBAIKAN: Handle null date --}}
              <td>{{ $application->end_date ? $application->end_date->format('Y-m-d') : '-' }}</td> {{-- PERBAIKAN: Handle null date --}}
              <td class="text-center">
                @if($application->status == 'approved')
                <span class="status-approved">Disetujui</span>
                @elseif($application->status == 'pending')
                <span class="status-pending">Pending</span>
                @else
                <span class="status-rejected">Ditolak</span>
                @endif
              </td>
            </tr>
            @empty
            {{-- PERBAIKAN: Gunakan @empty untuk fallback data --}}
            <tr>
              <td>1</td>
              <td>Siti Nur</td>
              <td>1231241</td>
              <td>Cuti</td>
              <td>2025-02-06</td>
              <td>2025-02-08</td>
              <td class="text-center"><span class="status-approved">Disetujui</span></td>
            </tr>
            <tr>
              <td>2</td>
              <td>Maulida R</td>
              <td>1231313</td>
              <td>Cuti Melahirkan</td>
              <td>2025-02-03</td>
              <td>2025-02-04</td>
              <td class="text-center"><span class="status-pending">Pending</span></td>
            </tr>
            <tr>
              <td>3</td>
              <td>Lionel Messi</td>
              <td>1312131</td>
              <td>Izin</td>
              <td>2025-01-01</td>
              <td>2025-01-05</td>
              <td class="text-center"><span class="status-pending">Pending</span></td>
            </tr>
            <tr>
              <td>4</td>
              <td>Arhan P</td>
              <td>12314112</td>
              <td>Cuti Tahunan Sakit</td>
              <td>2024-12-02</td>
              <td>2024-12-07</td>
              <td class="text-center"><span class="status-approved">Disetujui</span></td>
            </tr>
            <tr>
              <td>5</td>
              <td>Jay Idzes</td>
              <td>13212314</td>
              <td>Sakit</td>
              <td>2024-08-12</td>
              <td>2024-08-14</td>
              <td class="text-center"><span class="status-rejected">Ditolak</span></td>
            </tr>
            <tr>
              <td>6</td>
              <td>Bellingham</td>
              <td>123123412</td>
              <td>Cuti</td>
              <td>2024-08-10</td>
              <td>2024-08-12</td>
              <td class="text-center"><span class="status-approved">Disetujui</span></td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer-section">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <p class="mb-0">&copy; 2025 Sistem Inventaris & Administrasi. All rights reserved.</p>
          <p class="mb-0">Powered by SisInv Technology</p>
        </div>
        <div class="col-md-6 text-md-right">
          <div class="footer-links">
            <a href="#" class="text-white">Tentang Kami</a>
            <a href="#" class="text-white">Kontak</a>
            <a href="#" class="text-white">Social media</a>
            <a href="#" class="text-white">Pengaturan</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap 4 -->
  <script src="{{asset('theme/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
  <!-- AdminLTE App -->
  <script src="{{asset('theme/dist/js/adminlte.js')}}"></script>

  <script>
    function changeLanguage(lang) {
      let url = new URL(window.location.href);
      url.searchParams.set("lang", lang);
      window.location.href = url.toString();
    }
    
    $(document).ready(async () => {
      let languages = await (await fetch("{{ url(asset('localizations/languages.json')) }}")).json();
      for (let code in languages) {
        let native = languages[code].nameNative;
        let english = languages[code].nameEnglish;

        $("#lang-dropdown").append(`
          <li onclick="changeLanguage('${ code }')" class="d-flex align-items-center justify-content-start gap-2 px-2">
            <div class="lang-icon lang-icon-${ code }"></div>
            <span class="ml-2 text-uppercase" style="font-size: .8rem" data-text="${ english }">${ code }</span>
          </li>
        `);
      }
    });
  </script>
</body>
</html>