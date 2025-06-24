@extends('layouts.app')
@section('title','Dashboard')
@section('content')


      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-green">
              <div class="inner">
                <h3>{{$product_count}}</h3>

                <p class="font-weight-bold">{{ __("goods") }}</p>
              </div>
              <div class="icon">
                <i class="fas fa-boxes"></i>
              </div>
              <a href="{{route('barang')}}" class="small-box-footer">{{ __('messages.more-info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-red">
              <div class="inner">
                <h3>{{$category_count}}</h3>

                <p class="font-weight-bold">{{ __("types of goods") }}</p>
              </div>
              <div class="icon">
                <i class="ion ion-ios-pricetags"></i>
              </div>
              <a href="{{route('barang.jenis')}}" class="small-box-footer">{{ __('messages.more-info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-blue">
              <div class="inner">
                <h3>{{$unit_count}}</h3>

                <p class="font-weight-bold">{{ __("unit of goods") }}</p>
              </div>
              <div class="icon">
                <i class="ion ion-cube"></i>
              </div>
              <a href="{{route('barang.satuan')}}" class="small-box-footer">{{ __('messages.more-info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-pink">
              <div class="inner">
                <h3>{{$brand_count}}</h3>

                <p class="font-weight-bold">{{ __("brand of goods") }}</p>
              </div>
              <div class="icon">
                <i class="ion ion-ios-pricetag"></i>
              </div>
              <a href="{{route('barang.merk')}}" class="small-box-footer">{{ __('messages.more-info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>



          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-green">
              <div class="inner">
                <h3>{{$goodsin}}</h3>

                <p class="font-weight-bold">{{ __("incoming transaction") }}</p>
              </div>
              <div class="icon">
                <i class="ion ion-arrow-swap"></i>
              </div>
              <a href="{{route('transaksi.masuk.index')}}" class="small-box-footer">{{ __('messages.more-info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-orange">
              <div class="inner" style="color:white !important;">
                <h3>{{$goodsout}}</h3>

                <p class="font-weight-bold">{{ __("outbound transaction") }}</p>
              </div>
              <div class="icon">
                <i class="ion ion-arrow-swap"></i>
              </div>
              <a href="{{route('transaksi.keluar')}}" class="small-box-footer" style="color:white !important;">{{ __('messages.more-info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-purple">
              <div class="inner">
                <h3>{{$customer}}</h3>

                <p class="font-weight-bold">{{ __("customer") }}</p>
              </div>
              <div class="icon">
                <i class="ion ion-android-person"></i>
              </div>
              <a href="{{route('customer')}}" class="small-box-footer">{{ __('messages.more-info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

          {{-- <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-purple">
              <div class="inner">
                <h3>{{$cuti}}</h3>

                <p class="font-weight-bold">{{ __("cuti") }}</p>
              </div>
              <div class="icon">
                <i class="ion ion-android-person"></i>
              </div>
              <a href="{{route('cuti')}}" class="small-box-footer">{{ __('messages.more-info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div> --}}



          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-purple">
              <div class="inner">
                <h3>{{$supplier}}</h3>

                <p class="font-weight-bold">{{ __("Distributor") }}</p>
              </div>
              <div class="icon">
                <i class="fas fa-shipping-fast"></i>
              </div>
              <a href="{{route('supplier')}}" class="small-box-footer">{{ __('messages.more-info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-yellow" style="color:white !important;">
              <div class="inner">
                <h3>{{$staffCount}}</h3>

                <p class="font-weight-bold">{{ __("employee") }}</p>
              </div>
              <div class="icon">
                <i class="ion ion-android-person"></i>
              </div>
              <a href="{{route('settings.employee')}}" class="small-box-footer" style="color:white !important;">{{ __('messages.more-info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

  </div>
</div>

<script src="{{asset('theme/plugins/chart.js/Chart.min.js')}}"></script>
<script>

  function formatIDR(angka) {
      const strAngka = angka.toString().replace(/[^0-9]/g,'');
      if (!strAngka) return '';
      const parts = strAngka.split('.');
      let intPart = parts[0];
      const decPart = parts.length > 1 ? '.' + parts[1] : '';
      intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      const result = 'RP. ' + ''+intPart+decPart;
      return result;
  } 



</script>

@endsection
