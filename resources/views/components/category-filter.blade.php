{{-- Category & Box Filter Component --}}
{{-- Usage: @include('components.category-filter', ['categories' => $categories, 'boxes' => $boxes, 'products' => $products, 'totalProducts' => $totalProducts]) --}}

<div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6">
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
                        {{ $box->name }} ({{ $box->barcode }}) - {{ $box->products_count ?? $box->products->count() }} produk
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filter Kategori (Buttons) --}}
        <div class="flex flex-col gap-2">
            <label class="text-xs font-medium text-gray-600">Filter Kategori</label>
            <div class="flex flex-wrap gap-2">
                {{-- All Categories Button --}}
                <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ !request('category') ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <i class="fa-solid fa-th-large"></i>
                    <span>Semua</span>
                    <span class="bg-white/20 px-2 py-0.5 rounded-full text-xs">{{ $totalProducts ?? 0 }}</span>
                </a>

                {{-- Dynamic Category Buttons --}}
                @if(isset($categories) && count($categories) > 0)
                    @foreach($categories as $category)
                        <a href="{{ request()->fullUrlWithQuery(['category' => $category['slug'], 'page' => null]) }}"
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
    @if(request('category') || request('box') || request('search'))
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-gray-600 flex items-center gap-1">
                    <i class="fa-solid fa-tag text-indigo-500"></i>
                    Filter aktif:
                </span>

                @if(request('category'))
                    @php
                        $activeCategory = collect($categories ?? [])->firstWhere('slug', request('category'));
                    @endphp
                    @if($activeCategory)
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-700 rounded-lg text-xs font-semibold border border-indigo-200">
                            <i class="fa-solid fa-tag"></i>
                            <span>{{ $activeCategory['name'] }}</span>
                            <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="hover:text-red-600 transition-colors">
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

                @if(request('search'))
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-blue-50 to-cyan-50 text-blue-700 rounded-lg text-xs font-semibold border border-blue-200">
                        <i class="fa-solid fa-search"></i>
                        <span>"{{ request('search') }}"</span>
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-red-600 transition-colors">
                            <i class="fa-solid fa-times"></i>
                        </a>
                    </span>
                @endif

                {{-- Clear All Button --}}
                @if((request('category') || request('box') || request('search')))
                    <a href="{{ url()->current() }}" class="inline-flex items-center gap-1 text-xs text-red-600 hover:text-red-700 font-semibold ml-2 px-3 py-1.5 border-2 border-red-200 rounded-lg hover:bg-red-50 transition-all">
                        <i class="fa-solid fa-refresh"></i>
                        <span>Reset Semua</span>
                    </a>
                @endif
            </div>
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

        // Category Filter Buttons Loading
        const categoryButtons = document.querySelectorAll('a[href*="category"]');
        categoryButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                if (this.classList.contains('bg-gradient-to-r') && this.classList.contains('from-indigo-500')) {
                    return;
                }
                const originalContent = this.innerHTML;
                this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span class="hidden sm:inline ml-2">Loading...</span>';
                this.style.pointerEvents = 'none';
                setTimeout(() => {
                    if (this.style.pointerEvents === 'none') {
                        this.innerHTML = originalContent;
                        this.style.pointerEvents = 'auto';
                    }
                }, 10000);
            });
        });
    });
</script>
