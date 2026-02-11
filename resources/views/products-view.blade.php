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
                    class="group bg-white rounded-3xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-500 border-2 border-transparent hover:border-indigo-200 flex flex-col transform hover:-translate-y-1">
                    {{-- Product Image with Gradient Overlay --}}
                    <div class="relative w-full aspect-[4/3] overflow-hidden">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                            class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                        
                        {{-- Gradient Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        {{-- Stock Badge --}}
                        @php $avail = $product->available_stock; @endphp
                        @if ($avail <= 1)
                            <div class="absolute top-4 right-4 bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-bold px-4 py-2 rounded-full shadow-lg backdrop-blur-sm flex items-center gap-2 animate-pulse">
                                <i class="fa-solid fa-exclamation-triangle"></i>
                                @if ($avail == 0)
                                    Habis
                                @else
                                    Sisa {{ $avail }}
                                @endif
                            </div>
                        @else
                            <div class="absolute top-4 right-4 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-xs font-bold px-4 py-2 rounded-full shadow-lg backdrop-blur-sm flex items-center gap-2">
                                <i class="fa-solid fa-check-circle"></i>
                                <span>{{ $avail }}</span>
                            </div>
                        @endif

                        {{-- Category Badge --}}
                        @if($product->category)
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-md text-indigo-700 px-3 py-1.5 rounded-full text-xs font-bold shadow-md border border-indigo-100 flex items-center gap-1">
                                <i class="fa-solid fa-tag"></i>
                                <span>{{ $product->category }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Card Content --}}
                    <div class="p-5 flex-1 flex flex-col">
                        {{-- Product Name --}}
                        <h3 class="text-xl font-bold text-gray-900 line-clamp-2 mb-3 group-hover:text-indigo-600 transition-colors duration-300">
                            {{ $product->name }}
                        </h3>

                        {{-- Stock Info Row --}}
                        <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-100">
                            <div class="flex items-center gap-2 text-sm">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                                    <i class="fa-solid fa-cubes text-indigo-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Stok Tersedia</p>
                                    <p class="font-bold {{ $avail > 5 ? 'text-emerald-600' : ($avail > 0 ? 'text-orange-500' : 'text-red-500') }}">
                                        {{ $avail }} Unit
                                    </p>
                                </div>
                            </div>
                            
                            {{-- Quick Status Indicator --}}
                            @if($avail > 5)
                                <span class="text-xs bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-circle text-emerald-500" style="font-size: 6px;"></i>
                                    Ready
                                </span>
                            @elseif($avail > 0)
                                <span class="text-xs bg-orange-50 text-orange-700 px-3 py-1 rounded-full font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-circle text-orange-500" style="font-size: 6px;"></i>
                                    Terbatas
                                </span>
                            @else
                                <span class="text-xs bg-red-50 text-red-700 px-3 py-1 rounded-full font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-circle text-red-500" style="font-size: 6px;"></i>
                                    Kosong
                                </span>
                            @endif
                        </div>

                        {{-- Box Info --}}
                        @if($product->boxes->count() > 0)
                            <div class="mb-4 bg-gradient-to-r from-purple-50 to-pink-50 border-2 border-purple-200 rounded-xl p-3">
                                <p class="text-xs text-purple-700 font-semibold flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-md bg-purple-200 flex items-center justify-center">
                                        <i class="fa-solid fa-box text-purple-600 text-xs"></i>
                                    </div>
                                    <span class="flex-1 truncate">
                                        @foreach($product->boxes as $box)
                                            @if($loop->count > 1)
                                                {{ $box->name }}{{ !$loop->last ? ', ' : '' }}
                                            @else
                                                {{ $box->name }}
                                            @endif
                                        @endforeach
                                    </span>
                                </p>
                            </div>
                        @endif

                        {{-- Action Button --}}
                        <a href="{{ route('loan.borrow') }}"
                            class="mt-auto w-full bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 bg-size-200 bg-pos-0 hover:bg-pos-100 text-white text-center py-3.5 px-4 rounded-xl hover:shadow-xl transition-all duration-500 font-bold text-sm flex items-center justify-center gap-2 group {{ $avail == 0 ? 'opacity-50 cursor-not-allowed grayscale' : '' }}"
                            {{ $avail == 0 ? 'onclick="return false;"' : '' }}>
                            <i class="fa-solid fa-arrow-right-to-bracket group-hover:translate-x-1 transition-transform duration-300"></i>
                            <span>{{ $avail == 0 ? 'Tidak Tersedia' : 'Pinjam Sekarang' }}</span>
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

    <style>
        /* Custom utilities for gradient button animation */
        .bg-size-200 {
            background-size: 200% auto;
        }
        
        .bg-pos-0 {
            background-position: 0% center;
        }
        
        .bg-pos-100 {
            background-position: 100% center;
        }
    </style>

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
