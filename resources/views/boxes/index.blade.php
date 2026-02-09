@extends('layout')

@section('title', 'Kelola Kotak')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold" style="background: linear-gradient(to right, #0078fe, #0056b3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
            <i class="fa-solid fa-boxes mr-3"></i>Kelola Kotak
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Kotak</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $totalBoxes ?? $boxes->count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fa-solid fa-boxes text-indigo-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Produk di Kotak</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $totalProductsInBoxes ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fa-solid fa-box text-indigo-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Segmen Tersedia</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $segments->count() ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fa-solid fa-map-location-dot text-indigo-600"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-6">

        <div class="flex items-center gap-3 mb-6">
            <a href="#" id="openCreateBox" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-sm">Tambah Kotak</a>
        </div>

        @if (session('success'))
            <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-xl">{{ session('success') }}</div>
        @endif

        @if($boxes->count() > 0)
            <div id="boxes-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($boxes as $box)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col justify-between min-h-[12rem]">
                        <div>
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">{{ $box->name }}</h3>
                                    <p class="text-xs text-gray-500">Barcode: <span class="font-mono">{{ $box->barcode }}</span></p>
                                    <p class="text-xs text-gray-500">Segmen: <span class="font-semibold text-gray-700">{{ $box->segment?->name ?? '-' }}</span></p>
                                </div>
                                <div class="ml-4 flex-shrink-0 text-right">
                                    <div class="w-20 h-20 bg-gray-100 border rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-box text-gray-300 text-2xl"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-sm text-gray-500">Produk: <span class="font-semibold text-gray-700">{{ $box->products->count() }}</span></div>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('boxes.show', $box) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-purple-50 text-purple-700 text-sm">Lihat Isi</a>
                                <a href="{{ route('boxes.qr.show', $box->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-indigo-50 text-indigo-700 text-sm">Lihat QR</a>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" class="edit-box-btn px-3 py-2 rounded-xl bg-yellow-50 text-yellow-700 text-sm" data-id="{{ $box->id }}" data-name="{{ e($box->name) }}" data-segment-id="{{ $box->segment_id }}">Edit</button>
                                <form action="{{ route('boxes.destroy', $box) }}" method="POST" onsubmit="return confirm('Hapus kotak ini?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-2 rounded-xl bg-red-50 text-red-700 text-sm">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-20 bg-white rounded-2xl border border-gray-100">
                <i class="fa-solid fa-boxes text-6xl text-gray-300 mb-4 block"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Belum ada kotak.</h3>
                <a href="#" id="openCreateBoxEmpty" class="text-indigo-600 hover:text-indigo-700 font-medium"><i class="fa-solid fa-plus-circle mr-2"></i>Tambah kotak pertama</a>
            </div>
        @endif
    </div>

    <!-- Create Modal -->
    <div id="createBoxModal" class="fixed inset-0 bg-black/60 hidden z-50">
        <div class="relative top-10 mx-auto p-6 border max-w-2xl shadow-2xl rounded-2xl bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Tambah Kotak</h3>
                <button id="closeCreateBoxModal" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <form action="{{ route('boxes.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                    <input type="text" name="name" required class="block w-full border border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: Kotak A">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Segmen</label>
                    <select name="segment_id" required class="block w-full border border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Segmen --</option>
                        @foreach($segments as $segment)
                            <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                        @endforeach
                    </select>
                </div>

                

                <div class="flex justify-end">
                    <button type="submit" class="w-full md:w-auto bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-2 px-4 rounded-xl">Simpan Kotak</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editBoxModal" class="fixed inset-0 bg-black/60 hidden z-50">
        <div class="relative top-10 mx-auto p-6 border max-w-2xl shadow-2xl rounded-2xl bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Edit Kotak</h3>
                <button id="closeEditBoxModal" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <form id="editBoxForm" action="#" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                    <input id="edit-name" type="text" name="name" required class="block w-full border border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Nama kotak">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Segmen</label>
                    <select id="edit-segment" name="segment_id" required class="block w-full border border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Segmen --</option>
                        @foreach($segments as $segment)
                            <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                        @endforeach
                    </select>
                </div>

                

                <div class="flex justify-end">
                    <button type="submit" class="w-full md:w-auto bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-2 px-4 rounded-xl">Update Kotak</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Create modal
            const openCreate = document.getElementById('openCreateBox');
            const openCreateEmpty = document.getElementById('openCreateBoxEmpty');
            const createModal = document.getElementById('createBoxModal');
            const closeCreate = document.getElementById('closeCreateBoxModal');
            if (openCreate && createModal) {
                openCreate.addEventListener('click', function(e){ e.preventDefault(); createModal.classList.remove('hidden'); });
                if (openCreateEmpty) openCreateEmpty.addEventListener('click', function(e){ e.preventDefault(); createModal.classList.remove('hidden'); });
                closeCreate.addEventListener('click', function(){ createModal.classList.add('hidden'); });
                createModal.addEventListener('click', function(e){ if (e.target === createModal) createModal.classList.add('hidden'); });
            }

            // Edit modal
            const editModal = document.getElementById('editBoxModal');
            const closeEdit = document.getElementById('closeEditBoxModal');
            const editForm = document.getElementById('editBoxForm');

            document.querySelectorAll('.edit-box-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name || '';
                    const segId = this.dataset.segmentId || '';

                    if (editForm) {
                        editForm.action = '{{ url('/boxes') }}' + '/' + id;
                        document.getElementById('edit-name').value = name;
                        document.getElementById('edit-segment').value = segId;
                        editModal.classList.remove('hidden');
                    }
                });
            });

            if (closeEdit) closeEdit.addEventListener('click', function(){ editModal.classList.add('hidden'); });
            if (editModal) editModal.addEventListener('click', function(e){ if (e.target === editModal) editModal.classList.add('hidden'); });
        });
    </script>

@endsection
