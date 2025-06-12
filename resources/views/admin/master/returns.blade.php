@extends('layouts.app')
@section('title', __('returns'))
@section('content')

<div class="container-fluid">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="d-flex justify-content-end align-items-center w-100">
                        @if(Auth::user()->role->name != 'staff')
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#TambahData">
                                <i class="fas fa-plus"></i> {{__('add return')}}
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Add/Edit Modal -->
                <div class="modal fade" id="TambahData" tabindex="-1" aria-labelledby="TambahDataModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ isset($return) ? route('return.update', $return->id) : route('return.save') }}">
                                @csrf
                                @if(isset($return))
                                    @method('PUT')
                                @endif
                                
                                <div class="modal-header">
                                    <h5 class="modal-title" id="TambahDataModalLabel">
                                        {{ isset($return) ? __('edit return data') : __('adding return data') }}
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                
                                <div class="modal-body">
                                    <div class="form-group mb-3">
                                        <label for="borrower_id">{{__('peminjam')}}</label>
                                        <select class="form-control" id="borrower_id" name="borrower_id" required>
                                            <option value="">{{__('pilih peminjam')}}</option>
                                            @foreach($customers as $customer)
                                                <option value="{{$customer->id}}" 
                                                    {{ (isset($return) && $return->borrower_id == $customer->id) || old('borrower_id') == $customer->id ? 'selected' : '' }}>
                                                    {{$customer->name}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="item_code">{{__('item code')}}</label>
                                        <select class="form-control" id="item_code" name="item_code" required>
                                            <option value="">{{__('select item')}}</option>
                                            @foreach($items as $item)
                                                <option value="{{$item->code}}" 
                                                    {{ (isset($return) && $return->item_code == $item->code) || old('item_code') == $item->code ? 'selected' : '' }}>
                                                    {{$item->code}} - {{$item->name}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="return_date">{{__('Tanggal Pengembalian')}}</label>
                                        <input type="date" class="form-control" id="return_date" name="return_date" 
                                            value="{{ isset($return) ? $return->return_date : (old('return_date') ?: date('Y-m-d')) }}" required>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="status">{{__('status')}}</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="Baik" {{ (isset($return) && $return->status == 'Baik') || old('status') == 'Baik' ? 'selected' : '' }}>
                                                {{__('Baik')}}
                                            </option>
                                            <option value="Rusak" {{ (isset($return) && $return->status == 'Rusak') || old('status') == 'Rusak' ? 'selected' : '' }}>
                                                {{__('Rusak')}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('cancel')}}</button>
                                    <button type="submit" class="btn btn-success">{{__('save')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="4%">No</th>
                                    <th class="border-bottom-0">{{__('Nama Peminjam')}}</th>
                                    <th class="border-bottom-0">{{__('item code')}}</th>
                                    <th class="border-bottom-0">{{__('Tanggal pengembalian')}}</th>
                                    <th class="border-bottom-0">{{__('item status')}}</th>
                                    @if(Auth::user()->role->name != 'staff')
                                        <th class="border-bottom-0" width="15%">{{__('action')}}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returns as $index => $returnItem)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $returnItem->customer->name ?? 'N/A' }}</td>
                                        <td>{{ $returnItem->item_code }}</td>
                                        <td>{{ \Carbon\Carbon::parse($returnItem->return_date)->format('d-m-Y') }}</td>
                                        <td>
                                            <span class="badge {{ $returnItem->status == 'Baik' ? 'badge-success' : 'badge-danger' }}">
                                                {{ $returnItem->status }}
                                            </span>
                                        </td>
                                        @if(Auth::user()->role->name != 'staff')
                                            <td>
                                                <a href="{{ route('return.edit', $returnItem->id) }}" 
                                                   class="btn btn-primary btn-sm m-1">
                                                    <i class="fas fa-edit"></i> {{__('edit')}}
                                                </a>
                                                
                                                <form method="POST" action="{{ route('return.delete', $returnItem->id) }}" 
                                                      style="display: inline-block;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this data?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm m-1">
                                                        <i class="fas fa-trash"></i> {{__('delete')}}
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ Auth::user()->role->name != 'staff' ? '6' : '5' }}" class="text-center">
                                            No return data found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($return))
<script>
    // Auto-open modal if editing
    document.addEventListener('DOMContentLoaded', function() {
        $('#TambahData').modal('show');
    });
</script>
@endif

@endsection