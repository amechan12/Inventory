@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Tambah Kotak</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('boxes.store') }}" method="post">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Segmen</label>
            <select name="segment_id" class="form-control" required>
                <option value="">-- Pilih Segmen --</option>
                @foreach($segments as $segment)
                    <option value="{{ $segment->id }}" {{ old('segment_id') == $segment->id ? 'selected' : '' }}>{{ $segment->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Barcode Kotak</label>
            <input type="text" name="barcode" class="form-control" value="(akan digenerate otomatis)" readonly>
        </div>
        
        <button class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
