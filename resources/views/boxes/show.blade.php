@extends('layouts.app')

@section('content')
<div class="container">
        <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
            <div>
                <h3 class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent flex items-center gap-2">
                    <i class="fa-solid fa-box-open text-indigo-500"></i>
                    <span>Isi Kotak: {{ $box->name }}</span>
                </h3>
                <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-xs text-gray-600">
                        <i class="fa-solid fa-barcode mr-1"></i>{{ $box->barcode }}
                    </span>
                    @if($box->segment)
                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-indigo-50 text-xs text-indigo-700">
                            <i class="fa-solid fa-map-location-dot mr-1"></i>{{ $box->segment->name }}
                        </span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('boxes.qr.show', $box->id) }}" class="inline-flex items-center px-3 py-2 rounded-xl text-sm bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 border border-purple-100 hover:shadow-md">
                    <i class="fa-solid fa-qrcode mr-1"></i>Lihat QR Kotak
                </a>
            </div>
        </div>

        {{-- Tambah Cepat Barang ke Kotak --}}
        <div class="bg-white p-5 md:p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500 text-white">
                            <i class="fa-solid fa-bolt"></i>
                        </span>
                        <span>Tambah Cepat Barang</span>
                    </h4>
                    <p class="text-xs text-gray-500 mt-1">Buat barang baru dan langsung masukkan ke kotak ini.</p>
                </div>
            </div>

            <form id="quickAddForm" class="grid grid-cols-12 gap-4 items-end" enctype="multipart/form-data">
                <input type="hidden" name="stock" value="0">

                <div class="col-span-12 md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
                    <input name="name" id="quick_name" placeholder="Masukkan nama barang" required class="w-full border rounded px-3 py-2 text-sm" />
                </div>

                <div class="col-span-12 md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <input name="category" id="quick_category" placeholder="Elektronik, Peralatan, dll" class="w-full border rounded px-3 py-2 text-sm" />
                </div>

                <div class="col-span-12 md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kotak</label>
                    <input type="text" value="{{ $box->name }} ({{ $box->barcode }})" class="w-full border rounded px-3 py-2 text-sm bg-gray-50" disabled />
                    <input type="hidden" name="box_id" value="{{ $box->id }}">
                </div>

                <div class="col-span-6 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah di Kotak</label>
                    <input type="number" id="quick_box_qty" name="box_quantity" min="1" value="1" class="w-full border rounded px-3 py-2 text-sm text-center" />
                </div>

                <div class="col-span-12 md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gambar</label>
                    <input type="file" name="image_path" accept="image/*" class="w-full text-sm text-gray-500" />
                </div>

                <div class="col-span-12 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-sm hover:shadow-md">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>Tambah Cepat</span>
                    </button>
                </div>
            </form>

            <!-- search box removed per request -->
        </div>

        {{-- Daftar Isi Kotak --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <style>
                .increase-qty,
                .decrease-qty {
                    display: none !important;
                }
            </style>
            <div class="px-6 py-4 border-b bg-gradient-to-r from-indigo-50 to-purple-50 flex items-center justify-between">
                <div>
                    <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-indigo-500"></i>
                        <span>Daftar Isi Kotak</span>
                    </h4>
                    <p class="text-xs text-gray-500 mt-1">Kelola barang yang berada di dalam kotak ini.</p>
                </div>
                <div class="hidden md:flex items-center gap-2 text-xs text-gray-500">
                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100">
                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>Edit
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100">
                        <span class="w-2 h-2 rounded-full bg-yellow-500 mr-2"></span>Restock
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100">
                        <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>Hapus
                    </span>
                </div>
            </div>
            <div class="px-6 py-3 border-b bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="flex items-center space-x-4 text-sm">
                    <button id="mode_edit" class="mode-btn font-medium text-indigo-600 inline-flex items-center gap-1">
                        <i class="fa-solid fa-pen-to-square"></i><span>Edit</span>
                    </button>
                    <button id="mode_restock" class="mode-btn text-gray-600 inline-flex items-center gap-1">
                        <i class="fa-solid fa-plus"></i><span>Restock</span>
                    </button>
                    <button id="mode_remove" class="mode-btn text-red-600 inline-flex items-center gap-1">
                        <i class="fa-solid fa-trash-can"></i><span>Hapus</span>
                    </button>
                </div>
                <div class="w-full md:w-64">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 text-xs">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input id="boxItemsSearch" type="text" placeholder="Cari barang di kotak..." class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-gray-200 text-xs text-gray-700 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 bg-white" />
                    </div>
                </div>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">Nama</th>
                            <th class="px-4 py-2 text-center font-medium text-gray-700">ID</th>
                            <th class="px-4 py-2 text-center font-medium text-gray-700">Kategori</th>
                            <th class="px-4 py-2 text-center font-medium text-gray-700">Jumlah</th>
                            <th class="px-4 py-2 text-center font-medium text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($box->products as $p)
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-4 py-3 text-gray-800">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $p->name }}</span>
                                        @if($p->category)
                                            <span class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                                <i class="fa-solid fa-tag text-gray-400"></i>{{ $p->category }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center text-gray-700 font-mono text-xs">{{ $p->id }}</td>
                                <td class="px-4 py-3 text-center text-gray-600 text-xs">{{ $p->category ?? '-' }}</td>
                                <td class="px-4 py-3 text-center prod-qty font-semibold {{ ($p->pivot->quantity ?? 0) > 5 ? 'text-gray-700' : (($p->pivot->quantity ?? 0) > 0 ? 'text-orange-500' : 'text-red-500') }}" data-id="{{ $p->id }}">
                                    {{ $p->pivot->quantity ?? 0 }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="inline-flex items-center space-x-2 justify-center">
                                        <button data-id="{{ $p->id }}" class="action-edit hidden inline-flex items-center justify-center h-8 px-3 rounded-xl bg-green-50 text-green-700 border border-green-100 text-xs font-medium gap-1">
                                            <i class="fa-solid fa-pen-to-square"></i><span>Edit</span>
                                        </button>
                                        <button data-id="{{ $p->id }}" class="action-restock hidden inline-flex items-center justify-center h-8 px-3 rounded-xl bg-yellow-50 text-yellow-700 border border-yellow-100 text-xs font-medium gap-1">
                                            <i class="fa-solid fa-plus"></i><span>Restock</span>
                                        </button>
                                        <button data-id="{{ $p->id }}" class="action-remove hidden inline-flex items-center justify-center h-8 px-3 rounded-xl bg-red-50 text-red-600 border border-red-100 text-xs font-medium gap-1">
                                            <i class="fa-solid fa-trash-can"></i><span>Hapus</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const addUrl = '{{ route('boxes.products.add', $box) }}';
            const updateBase = '{{ url('/boxes/'.$box->id.'/products') }}';
            const removeBase = updateBase;
            const csrf = '{{ csrf_token() }}';

            const search = document.getElementById('productSearch');
            const productsList = document.getElementById('productsList');

            if (search && productsList) {
                search.addEventListener('input', function(){
                    const q = (this.value || '').toLowerCase();
                    productsList.querySelectorAll('.product-row').forEach(row => {
                        const name = row.querySelector('.font-semibold').textContent.toLowerCase();
                        row.style.display = name.includes(q) ? '' : 'none';
                    });
                });

                document.getElementById('addSelectedProduct').addEventListener('click', function(){
                    const raw = search.value.trim();
                    if (!raw) return alert('Masukkan nama atau ID produk.');
                    const byId = productsList.querySelector(`.product-row[data-id="${raw}"]`);
                    const visibleRow = Array.from(productsList.querySelectorAll('.product-row')).find(r => r.style.display !== 'none');
                    const targetRow = byId || visibleRow || productsList.querySelector('.product-row');
                    if (!targetRow) return alert('Produk tidak ditemukan.');
                    const pid = targetRow.getAttribute('data-id');
                    const qtyInput = targetRow.querySelector('.prod-qty-input');
                    const qty = parseInt(qtyInput.value || '1', 10) || 1;
                    fetch(addUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ product_id: pid, quantity: qty }) })
                        .then(r => r.json()).then(j => {
                            if (j && j.success) {
                                const category = (j.product && j.product.category) ? j.product.category : null;
                                upsertProductRow(j.product.id, j.product.name, j.quantity, category);
                                showTmpNotification('Produk disimpan ke kotak');
                            } else {
                                alert(j.message || 'Gagal menambahkan produk');
                            }
                        }).catch(e => { console.error(e); alert('Gagal menambahkan produk'); });
                });

                productsList.addEventListener('click', function(e){
                    if (e.target.classList.contains('save-to-box')) {
                        const pid = e.target.dataset.id;
                        const input = productsList.querySelector(`.prod-qty-input[data-id="${pid}"]`);
                        const qty = parseInt(input.value || '1', 10) || 1;
                        fetch(`${updateBase}/${pid}`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ quantity: qty }) })
                            .then(r => r.json()).then(j => {
                                if (j && j.success) {
                                    // Ambil kategori dari baris yang sudah ada di tabel atau dari response
                                    const existingRow = document.querySelector(`tr td.prod-qty[data-id="${pid}"]`);
                                    let category = null;
                                    if (existingRow) {
                                        const row = existingRow.closest('tr');
                                        const categoryCell = row ? row.querySelector('td:nth-child(3)') : null;
                                        category = categoryCell ? categoryCell.textContent.trim() : null;
                                        if (category === '-') category = null;
                                    }
                                    upsertProductRow(pid, productsList.querySelector(`.product-row[data-id="${pid}"] .font-semibold`).textContent, qty, category);
                                    showTmpNotification('Jumlah produk diperbarui');
                                } else {
                                    alert(j.message || 'Gagal menyimpan');
                                }
                            }).catch(e => { console.error(e); alert('Gagal menyimpan'); });
                    }

                    if (e.target.classList.contains('remove-from-list')) {
                        const pid = e.target.dataset.id;
                        if (!confirm('Hapus produk dari kotak?')) return;
                        fetch(`${removeBase}/${pid}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                            .then(r => r.json()).then(j => {
                                if (j && j.success) {
                                    removeProductRow(pid);
                                    showTmpNotification('Produk dihapus dari kotak');
                                } else {
                                    alert(j.message || 'Gagal menghapus');
                                }
                            }).catch(e => { console.error(e); alert('Gagal menghapus'); });
                    }
                });
            }

            document.querySelectorAll('.remove-from-box').forEach(btn => btn.addEventListener('click', function(){
                const pid = this.dataset.id; if (!confirm('Hapus produk dari kotak?')) return; fetch(`${removeBase}/${pid}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } }).then(r => r.json()).then(j => { if (j && j.success) { removeProductRow(pid); showTmpNotification('Produk dihapus dari kotak'); } else alert(j.message || 'Gagal menghapus'); }).catch(e => { console.error(e); alert('Gagal menghapus'); });
            }));

            function changeQty(pid, delta) {
                const cell = document.querySelector(`td.prod-qty[data-id="${pid}"]`);
                if (!cell) return;
                let cur = parseInt(cell.textContent || '0', 10) || 0;
                cur = Math.max(1, cur + delta);
                fetch(`${updateBase}/${pid}`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ quantity: cur }) }).then(r => r.json()).then(j => { if (j && j.success) { cell.textContent = cur; showTmpNotification('Jumlah diperbarui'); } else alert(j.message || 'Gagal mengubah jumlah'); }).catch(e => { console.error(e); alert('Gagal mengubah jumlah'); });
            }

            function upsertProductRow(id, name, qty, category = null) {
                const tbody = document.querySelector('table tbody');
                let cell = tbody ? tbody.querySelector(`td.prod-qty[data-id="${id}"]`) : null;
                if (cell) {
                    // Jika produk sudah ada di daftar isi kotak, cukup update jumlahnya
                    cell.textContent = qty;
                    // Update kategori jika ada
                    if (category !== null) {
                        const row = cell.closest('tr');
                        if (row) {
                            const categoryCell = row.querySelector('td:nth-child(3)');
                            if (categoryCell) categoryCell.textContent = category || '-';
                            const categoryTag = row.querySelector('.text-xs.text-gray-500');
                            if (categoryTag) {
                                if (category) {
                                    categoryTag.innerHTML = `<i class="fa-solid fa-tag text-gray-400"></i>${category}`;
                                    categoryTag.style.display = '';
                                } else {
                                    categoryTag.style.display = 'none';
                                }
                            }
                        }
                    }
                    return;
                }
                if (!tbody) return;

                // Tambah baris baru dengan struktur kolom yang sama seperti di Blade:
                // Nama | ID | Kategori | Jumlah | Aksi
                const categoryDisplay = category || '-';
                const categoryTagHtml = category ? `<span class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                <i class="fa-solid fa-tag text-gray-400"></i>${category}
                            </span>` : '';
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50/80';
                tr.innerHTML = `
                    <td class="px-4 py-3 text-gray-800">
                        <div class="flex flex-col">
                            <span class="font-medium">${name}</span>
                            ${categoryTagHtml}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-700 font-mono text-xs">${id}</td>
                    <td class="px-4 py-3 text-center text-gray-600 text-xs">${categoryDisplay}</td>
                    <td class="px-4 py-3 text-center prod-qty font-semibold ${qty > 5 ? 'text-gray-700' : (qty > 0 ? 'text-orange-500' : 'text-red-500')}" data-id="${id}">
                        ${qty}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="inline-flex items-center space-x-2 justify-center">
                            <button data-id="${id}" class="action-add hidden inline-flex items-center justify-center h-8 px-3 rounded-xl bg-blue-50 text-blue-700 border border-blue-100 text-xs font-medium gap-1">
                                <i class="fa-solid fa-plus"></i><span>Tambah</span>
                            </button>
                            <button data-id="${id}" class="action-edit hidden inline-flex items-center justify-center h-8 px-3 rounded-xl bg-green-50 text-green-700 border border-green-100 text-xs font-medium gap-1">
                                <i class="fa-solid fa-pen-to-square"></i><span>Edit</span>
                            </button>
                            <button data-id="${id}" class="action-restock hidden inline-flex items-center justify-center h-8 px-3 rounded-xl bg-yellow-50 text-yellow-700 border border-yellow-100 text-xs font-medium gap-1">
                                <i class="fa-solid fa-plus"></i><span>Restock</span>
                            </button>
                            <button data-id="${id}" class="action-remove hidden inline-flex items-center justify-center h-8 px-3 rounded-xl bg-red-50 text-red-600 border border-red-100 text-xs font-medium gap-1">
                                <i class="fa-solid fa-trash-can"></i><span>Hapus</span>
                            </button>
                        </div>
                    </td>
                `;

                tbody.appendChild(tr);
                // Pastikan tombol aksi di baris baru mengikuti mode yang sedang aktif
                if (typeof setMode === 'function') {
                    setMode(currentMode);
                }
            }

            function removeProductRow(id) {
                const tbody = document.querySelector('table tbody');
                const tr = tbody ? tbody.querySelector(`tr td.prod-qty[data-id="${id}"]`) : null;
                if (tr) tr.closest('tr').remove();
                const pRow = document.getElementById('productsList') ? document.getElementById('productsList').querySelector(`.product-row[data-id="${id}"]`) : null;
                if (pRow) pRow.querySelector('.prod-qty-input').value = 1;
            }

            window.showTmpNotification = function(msg) {
                const n = document.createElement('div'); n.className = 'fixed top-6 right-6 bg-black text-white p-3 rounded shadow-lg'; n.textContent = msg; document.body.appendChild(n); setTimeout(()=>{ n.remove(); }, 1800);
            }

            // Quick add form handler
            const quickForm = document.getElementById('quickAddForm');
            const storeUrl = '{{ route('products.store') }}';
            if (quickForm) {
                quickForm.addEventListener('submit', function(e){
                    e.preventDefault();
                    const fd = new FormData(quickForm);
                    // box_id and box_quantity are already in the form, so always add to box
                    const boxQty = document.getElementById('quick_box_qty').value || '1';
                    // Set stock produk sama dengan jumlah di kotak
                    fd.set('stock', boxQty);

                    fetch(storeUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
                        .then(res => {
                            const ct = (res.headers.get('content-type') || '').toLowerCase();
                            if (ct.includes('application/json')) return res.json();
                            // non-json (normal form submit) -> reload page to show changes
                            window.location.reload();
                        })
                        .then(j => {
                            if (!j) return;
                            if (j.success && j.product) {
                                const prod = j.product;
                                // insert into productsList
                                createProductRow(prod.id, prod.name);
                                showTmpNotification('Produk dibuat');
                                // Always add to box since box_id is in the form
                                const qty = boxQty;
                                const category = prod.category || null;
                                if (j.added_to_box && j.quantity) {
                                    upsertProductRow(prod.id, prod.name, j.quantity, category);
                                    showTmpNotification('Produk ditambahkan ke kotak');
                                } else {
                                    // If server didn't add to box, try client-side add
                                    fetch(addUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ product_id: prod.id, quantity: parseInt(qty,10) || 1 }) })
                                        .then(r => r.json()).then(bj => { 
                                            if (bj && bj.success) {
                                                upsertProductRow(prod.id, prod.name, bj.quantity || qty, category);
                                                showTmpNotification('Produk ditambahkan ke kotak');
                                            }
                                        });
                                }
                                // Reset form
                                quickForm.reset();
                                document.getElementById('quick_box_qty').value = '1';
                                document.querySelector('input[name="box_id"]').value = '{{ $box->id }}';
                            } else if (j && j.errors) {
                                alert(Object.values(j.errors).flat().join('\n'));
                            } else {
                                window.location.reload();
                            }
                        }).catch(err => { console.error(err); alert('Gagal membuat produk'); });
                });
            }

            // Mode switching for actions (add, edit, restock, remove)
            const modeButtons = document.querySelectorAll('.mode-btn');
            let currentMode = 'edit';
            function setMode(m) {
                currentMode = m;
                // update tab styles
                modeButtons.forEach(b => b.classList.remove('font-semibold', 'text-indigo-600'));
                const active = document.getElementById('mode_' + m);
                if (active) active.classList.add('font-semibold', 'text-indigo-600');

                document.querySelectorAll('table tbody tr').forEach(tr => {
                    const id = tr.querySelector('[data-id]') ? tr.querySelector('[data-id]').getAttribute('data-id') : null;
                    // hide all action buttons then show the one for mode
                    const addBtn = tr.querySelector('.action-add');
                    const editBtn = tr.querySelector('.action-edit');
                    const restockBtn = tr.querySelector('.action-restock');
                    const removeBtn = tr.querySelector('.action-remove');
                    if (addBtn) addBtn.classList.add('hidden');
                    if (editBtn) editBtn.classList.add('hidden');
                    if (restockBtn) restockBtn.classList.add('hidden');
                    if (removeBtn) removeBtn.classList.add('hidden');
                    if (m === 'add' && addBtn) addBtn.classList.remove('hidden');
                    if (m === 'edit' && editBtn) editBtn.classList.remove('hidden');
                    if (m === 'restock' && restockBtn) restockBtn.classList.remove('hidden');
                    if (m === 'remove' && removeBtn) removeBtn.classList.remove('hidden');
                });
            }

            ['add','edit','restock','remove'].forEach(m => {
                const el = document.getElementById('mode_' + m);
                if (el) el.addEventListener('click', () => setMode(m));
            });
            // initialize
            setMode('edit');

            function createProductRow(id, name) {
                if (!productsList) return;
                const existing = productsList.querySelector(`.product-row[data-id="${id}"]`);
                if (existing) return;
                const div = document.createElement('div');
                div.className = 'product-row flex items-center space-x-3';
                div.setAttribute('data-id', id);
                div.innerHTML = `<div class="font-semibold">${name}</div><input type="number" min="1" class="prod-qty-input w-20 border p-1" data-id="${id}" value="1"><button class="save-to-box px-2 py-1 bg-green-50 text-green-700 border rounded" data-id="${id}">Simpan</button><button class="remove-from-list px-2 py-1 bg-red-50 text-red-700 border rounded" data-id="${id}">Hapus</button>`;
                productsList.prepend(div);
            }
        })();
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const productBase = '{{ url('/products') }}';
        const apiProductBase = '/api/product';
        const csrf = '{{ csrf_token() }}';
        const updateBase = '{{ url('/boxes/'.$box->id.'/products') }}';
        const removeBase = updateBase;

        async function parseJsonResponse(res) {
            const ct = (res.headers.get('content-type') || '').toLowerCase();
            if (ct.includes('application/json')) {
                return res.json();
            }
            const text = await res.text();
            console.error('Non-JSON response', res.status, text);
            throw new Error('Server returned non-JSON response: ' + res.status + '\n' + text.slice(0, 500));
        }

        const modal = document.getElementById('editProductModal');
        const editForm = document.getElementById('editProductForm');

        function showModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        function hideModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

        document.querySelector('table').addEventListener('click', async function(e){
            const btn = e.target.closest('button'); if (!btn) return;

            // Edit (show modal)
            if (btn.classList.contains('action-edit') || btn.classList.contains('edit-product')) {
                const id = btn.dataset.id;
                const row = btn.closest('tr');
                const nameCell = row.querySelector('td');
                const nameText = nameCell ? nameCell.textContent.trim() : '';
                document.getElementById('edit_product_id').value = id;
                document.getElementById('edit_name').value = nameText;
                document.getElementById('edit_category').value = '';
                try {
                    const res = await fetch(`${apiProductBase}/${encodeURIComponent(id)}`);
                    if (res.ok) {
                        const j = await res.json();
                        if (j && j.product) {
                            document.getElementById('edit_name').value = j.product.name || nameText;
                            document.getElementById('edit_category').value = j.product.category || '';
                        }
                    }
                } catch(e) {}
                showModal();
            }

            // Remove from box (detach) -- action-remove
            if (btn.classList.contains('action-remove')) {
                const id = btn.dataset.id;
                if (!confirm('Hapus produk dari kotak?')) return;
                try {
                    const res = await fetch(`${removeBase}/${encodeURIComponent(id)}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const status = res.status;
                    const j = await parseJsonResponse(res);
                    if ((j && j.success) || status === 404) {
                        const qtyCell = document.querySelector(`td.prod-qty[data-id="${id}"]`);
                        if (qtyCell) qtyCell.closest('tr').remove();
                        showTmpNotification('Produk dihapus dari kotak');
                    } else {
                        alert((j && j.message) || 'Gagal menghapus produk dari kotak');
                    }
                } catch(err) {
                    console.error(err);
                    alert('Gagal menghapus produk dari kotak:\n' + (err.message || err));
                }
            }

            // Restock global product stock (action-restock)
            if (btn.classList.contains('action-restock')) {
                const id = btn.dataset.id;
                try {
                    // Ambil jumlah saat ini di kotak (pivot quantity), bukan stock global
                    const cell = document.querySelector(`td.prod-qty[data-id="${id}"]`);
                    const currentQty = cell ? parseInt(cell.textContent || '0', 10) : 0;
                    
                    // Ambil juga stock global untuk referensi
                    const infoRes = await fetch(`${apiProductBase}/${encodeURIComponent(id)}`);
                    let currentStock = null;
                    if (infoRes.ok) {
                        const info = await infoRes.json();
                        if (info && info.product) currentStock = info.product.stock;
                    }
                    
                    const promptMsg = currentQty > 0
                        ? `Jumlah di kotak saat ini: ${currentQty}${currentStock !== null ? `\nStock global: ${currentStock}` : ''}\nTambah jumlah (jumlah):`
                        : `Tambah jumlah (jumlah):`;
                    const amt = prompt(promptMsg, '1');
                    if (!amt) return;
                    const n = parseInt(amt, 10);
                    if (isNaN(n) || n <= 0) return alert('Masukkan angka yang valid');

                    const res = await fetch(`${productBase}/${encodeURIComponent(id)}/restock`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ stock_added: n })
                    });
                    const j = await parseJsonResponse(res);
                    if (j && j.success) {
                        // Update jumlah di kotak (pivot quantity sudah diupdate di backend)
                        const newQty = currentQty + n;
                        if (cell) {
                            cell.textContent = newQty;
                            // Update warna berdasarkan jumlah baru
                            cell.className = cell.className.replace(/text-(gray|orange|red)-\d+/g, '');
                            if (newQty > 5) {
                                cell.classList.add('text-gray-700');
                            } else if (newQty > 0) {
                                cell.classList.add('text-orange-500');
                            } else {
                                cell.classList.add('text-red-500');
                            }
                        }
                        showTmpNotification(`Jumlah di kotak diperbarui (sekarang: ${newQty})`);
                    } else {
                        alert((j && j.message) || 'Gagal merestock produk');
                    }
                } catch(err) {
                    console.error(err);
                    alert('Gagal merestock produk:\n' + (err.message || err));
                }
            }

            // Add to box (action-add) -> increase pivot quantity by entered amount
            if (btn.classList.contains('action-add')) {
                const id = btn.dataset.id;
                const cell = document.querySelector(`td.prod-qty[data-id="${id}"]`);
                const cur = parseInt((cell && cell.textContent) || '0', 10) || 0;
                const amt = prompt('Tambah jumlah ke kotak (angka):', '1');
                if (!amt) return;
                const n = parseInt(amt, 10);
                if (isNaN(n) || n <= 0) return alert('Masukkan angka yang valid');
                const newQty = Math.max(1, cur + n);
                try {
                    const res = await fetch(`${updateBase}/${encodeURIComponent(id)}`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: JSON.stringify({ quantity: newQty }) });
                    const j = await res.json();
                    if (j && j.success) {
                        if (cell) cell.textContent = newQty;
                        showTmpNotification('Jumlah di kotak diperbarui');
                    } else {
                        alert(j.message || 'Gagal menambahkan jumlah');
                    }
                } catch(err) { console.error(err); alert('Gagal menambahkan jumlah'); }
            }
        });

        document.getElementById('editCancel').addEventListener('click', hideModal);

        editForm.addEventListener('submit', async function(e){
            e.preventDefault();
            const id = document.getElementById('edit_product_id').value;
            const name = document.getElementById('edit_name').value.trim();
            const category = document.getElementById('edit_category').value.trim();
            if (!id) return;
            try {
                const res = await fetch(`${productBase}/${encodeURIComponent(id)}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name, category })
                });
                const j = await parseJsonResponse(res);
                if (j && (j.success || j.product)) {
                    const qtyCell = document.querySelector(`td.prod-qty[data-id="${id}"]`);
                    if (qtyCell) {
                        const row = qtyCell.closest('tr');
                        if (row) row.querySelector('td').textContent = name;
                    }
                    hideModal();
                    const n = document.createElement('div');
                    n.className = 'fixed top-6 right-6 bg-black text-white p-3 rounded shadow-lg';
                    n.textContent = 'Produk diperbarui';
                    document.body.appendChild(n);
                    setTimeout(() => { n.remove(); }, 1800);
                } else if (j && j.errors) {
                    alert(Object.values(j.errors).flat().join('\n'));
                } else {
                    alert('Gagal menyimpan perubahan');
                }
            } catch (err) {
                console.error(err);
                alert('Gagal menyimpan');
            }
        });
    });
    </script>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center">
        <div class="bg-white rounded-lg w-96 p-4">
            <h3 class="text-lg font-semibold mb-3">Edit Produk</h3>
            <form id="editProductForm" class="space-y-3">
                @csrf
                <input type="hidden" id="edit_product_id">
                <div>
                    <label class="text-sm">Nama</label>
                    <input id="edit_name" class="w-full border rounded px-2 py-1" required>
                </div>
                <div>
                    <label class="text-sm">Kategori</label>
                    <input id="edit_category" class="w-full border rounded px-2 py-1">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="editCancel" class="px-3 py-1 border rounded">Batal</button>
                    <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


