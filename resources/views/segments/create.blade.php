@extends('layout')

@section('title', 'Tambah Segmen')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h1 class="text-2xl font-bold mb-4">Tambah Segmen Lokasi</h1>

        <form action="{{ route('segments.store') }}" method="POST" class="space-y-4" enctype="multipart/form-data">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                <input type="text" name="name" required class="input-field" placeholder="Contoh: Gudang A">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="4" class="input-field" placeholder="Opsional"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gambar</label>
                <input type="file" name="image_path" accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-indigo-50 file:to-purple-50 file:text-indigo-700" />
                <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, GIF. Maksimal 2MB</p>
            </div>

            <button class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-xl">Simpan Segmen</button>
        </form>
    </div>
</div>
@endsection