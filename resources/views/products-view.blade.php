@extends('layout')

@section('title', 'Daftar Barang')

@section('content')
    <div class="space-y-6">
        {{-- Page Title --}}
        <div class="flex items-center gap-3">
            <div
                class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center">
                <i class="fa-solid fa-list text-white text-xl"></i>
            </div>
            <div>
                <h1
                    class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    Daftar Barang yang Tersedia
                </h1>
                <p class="text-sm text-gray-500 mt-1">Lihat semua barang yang dapat Anda pinjam</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex flex-col gap-4">
                {{-- Title & Mobile Count --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                            <i class="fa-solid fa-filter text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">Filter</h3>
                            <p class="text-xs text-gray-500 sm:hidden">
                                @if(isset($products) && method_exists($products, 'total'))
                                    {{ $products->total() }} produk
                                @else
                                    {{ $products->count() ?? 0 }} produk
                                @endif
                            </p>
                        </div>
                    </div>
                    {{-- Product Count (Desktop) --}}
                    <div class="hidden sm:block text-sm text-gray-500 font-medium">
                        @if(isset($products) && method_exists($products, 'total'))
                            {{ $products->total() }} dari {{ $totalProducts ?? 0 }}
                        @else
                            {{ $products->count() ?? 0 }} dari {{ $totalProducts ?? 0 }}
                        @endif
                    </div>
                </div>

                {{-- Filter Kotak (Dropdown) --}}
                <div class="flex items-center gap-3">
                    <label for="box-filter" class="text-xs font-medium text-gray-600 whitespace-nowrap">Filter Kotak</label>
                    <select id="box-filter" class="input-field text-sm flex-1">
                        <option value="">-- Semua Kotak --</option>
                        @foreach($boxes ?? [] as $box)
                            <option value="{{ $box->id }}" {{ (string) request('box') === (string) $box->id ? 'selected' : '' }}>
                                {{ $box->name }} ({{ $box->barcode }}) - {{ $box->products_count ?? 0 }} produk
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Kategori (Buttons) --}}
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-medium text-gray-600">Filter Kategori</label>
                    <div class="flex flex-wrap gap-2">
                        {{-- All Categories Button --}}
                        <a href="?"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ !request('category') ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            <i class="fa-solid fa-th-large"></i>
                            <span>Semua</span>
                            <span class="bg-white/20 px-2 py-0.5 rounded-full text-xs">{{ $totalProducts ?? 0 }}</span>
                        </a>

                        {{-- Dynamic Category Buttons --}}
                        @if(isset($allCategories) && count($allCategories) > 0)
                            @foreach($allCategories as $category)
                                <a href="?category={{ $category['slug'] }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ request('category') === $category['slug'] ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                                    title="{{ $category['name'] }} ({{ $category['count'] }} produk)">
                                    <i class="fa-solid fa-tag"></i>
                                    <span>{{ $category['name'] }}</span>
                                    <span class="{{ request('category') === $category['slug'] ? 'bg-white/20' : 'bg-gray-200' }} px-2 py-0.5 rounded-full text-xs">{{ $category['count'] }}</span>
                                </a>
                            @endforeach
                        @else
                            <span class="text-xs text-gray-400 italic px-3 py-2">
                                Belum ada kategori tersedia
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Active Filters Display --}}
            @if(request('category') || request('box'))
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-gray-600 flex items-center gap-1">
                            <i class="fa-solid fa-tag text-indigo-500"></i>
                            Filter aktif:
                        </span>

                        @if(request('category'))
                            @php
                                $activeCategory = collect($allCategories ?? [])->firstWhere('slug', request('category'));
                            @endphp
                            @if($activeCategory)
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-700 rounded-lg text-xs font-semibold border border-indigo-200">
                                    <i class="fa-solid fa-tag"></i>
                                    <span>{{ $activeCategory['name'] }}</span>
                                    <a href="?" class="hover:text-red-600 transition-colors">
                                        <i class="fa-solid fa-times"></i>
                                    </a>
                                </span>
                            @endif
                        @endif

                        @if(request('box'))
                            @php
                                $activeBox = ($boxes ?? collect())->firstWhere('id', (int) request('box'));
                            @endphp
                            @if($activeBox)
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-lg text-xs font-semibold border border-purple-200">
                                    <i class="fa-solid fa-box"></i>
                                    <span>{{ $activeBox->name }} ({{ $activeBox->barcode }})</span>
                                    <a href="{{ request()->fullUrlWithQuery(['box' => null]) }}" class="hover:text-red-600 transition-colors">
                                        <i class="fa-solid fa-times"></i>
                                    </a>
                                </span>
                            @endif
                        @endif

                        {{-- Clear All Button --}}
                        @if((request('category') || request('box')))
                            <a href="?" class="inline-flex items-center gap-1 text-xs text-red-600 hover:text-red-700 font-semibold ml-2 px-3 py-1.5 border-2 border-red-200 rounded-lg hover:bg-red-50 transition-all">
                                <i class="fa-solid fa-refresh"></i>
                                <span>Reset Semua</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Products Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse ($products as $product)
                <div
                    class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-xl transition-all duration-300 border border-gray-100 flex flex-col">
                    <div
                        class="w-full aspect-square bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center relative overflow-hidden">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">

                        @php $avail = $product->available_stock; @endphp
                        @if ($avail <= 1)
                            <div
                                class="absolute top-3 left-3 bg-red-500 text-white text-xs px-3 py-1 rounded-full font-semibold shadow-lg">
                                @if ($avail == 0)
                                    Kosong
                                @else
                                    Sisa {{ $avail }}
                                @endif
                            </div>
                        @else
                            <div
                                class="absolute top-3 left-3 bg-emerald-500 text-white text-xs px-3 py-1 rounded-full font-semibold shadow-lg">
                                Tersedia {{ $avail }}
                            </div>
                        @endif
                    </div>

                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="text-lg font-bold text-gray-800 line-clamp-2 mb-2">{{ $product->name }}</h3>

                        <div class="flex justify-between items-center mb-4 text-sm">
                            @if($product->category)
                                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-lg font-medium">{{ $product->category }}</span>
                            @endif
                            <p class="font-semibold {{ $avail > 5 ? 'text-gray-500' : ($avail > 0 ? 'text-orange-500' : 'text-red-500') }}">
                                Stok: {{ $avail }}
                            </p>
                        </div>

                        {{-- Box Info --}}
                        @if($product->boxes->count() > 0)
                            <div class="mb-4 text-xs bg-purple-50 border border-purple-200 rounded-lg p-2">
                                <p class="text-purple-700 font-medium">
                                    <i class="fa-solid fa-box mr-1"></i>
                                    @foreach($product->boxes as $box)
                                        @if($loop->count > 1)
                                            {{ $box->name }}{{ !$loop->last ? ',' : '' }}
                                        @else
                                            {{ $box->name }}
                                        @endif
                                    @endforeach
                                </p>
                            </div>
                        @endif

                        {{-- Action Button --}}
                        <a href="{{ route('loan.borrow') }}"
                            class="mt-auto w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center py-2.5 px-3 rounded-xl hover:shadow-md transition-all font-medium {{ $avail == 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $avail == 0 ? 'onclick="return false;"' : '' }}>
                            <i class="fa-solid fa-plus-circle mr-1"></i>Pinjam
                        </a>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 xl:col-span-3 text-center py-20">
                    <i class="fa-solid fa-box-open text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">
                        @if(request('category') || request('box'))
                            Barang dengan filter ini tidak ditemukan.
                        @else
                            Belum ada barang yang tersedia.
                        @endif
                    </h3>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Box Filter Dropdown
            const boxFilter = document.getElementById('box-filter');
            if (boxFilter) {
                boxFilter.addEventListener('change', function () {
                    const val = this.value;
                    const url = new URL(window.location.href);
                    if (val) {
                        url.searchParams.set('box', val);
                    } else {
                        url.searchParams.delete('box');
                    }
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                });
            }
        });
    </script>
@endsection
