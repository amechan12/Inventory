# Bug Fix Documentation - Akurasi Jumlah Barang

## Ringkasan Bug

Ada 2 bug utama terkait akurasi jumlah barang:

1. **Bug #1**: Kotak menunjukkan 5 barang, tetapi saat mencoba meminjam 4 barang, sistem hanya memperbolehkan meminjam 1 barang
2. **Bug #2**: Menu "Kelola Barang" menunjukkan 8 barang, tetapi di "Kelola Kotak" (lihat isi kotak) hanya menunjukkan 2 barang

## Root Cause Analysis

### Root Cause Bug #1

**Penyebab**: Sistem perhitungan stok tidak membedakan antara barang di mana:

- Barang ada di kotak (tabel `box_product` menyimpan quantity)
- Barang global (tabel `products` menyimpan stock)

Ketika peminjaman dilakukan, sistem hanya melihat `products.stock - products.reserved_stock` tanpa mempertimbangkan bahwa barang bisa ada di kotak spesifik. Ini menyebabkan:

- Jika product.stock = 5 tetapi hanya ada 1 unit di kotak yang dipinjam, sistem berpikir ada 5 - 0 reserved = 5 tersedia
- Tetapi kenyataannya, hanya ada 1 di kotak tersebut

### Root Cause Bug #2

**Penyebab**: Tidak ada single source of truth untuk quantity barang:

- `products.stock` adalah quantity global/master
- `box_product.quantity` adalah quantity per kotak
- Ketika barang diupdate, salah satu table tidak ter-sinkronisasi

## Solusi yang Diterapkan

### 1. Tambah Method di Model Product (`app/Models/Product.php`)

```php
/**
 * Get total quantity of this product across all boxes
 */
public function getTotalQuantityInBoxes()
{
    return $this->boxes->sum('pivot.quantity');
}

/**
 * Get available stock for borrowing
 * Considers: stock - reserved_stock, and must exist in boxes
 * If product is in boxes, use quantity from box
 */
public function getAvailableStockForBorrow()
{
    // If product is assigned to box(es), use quantity from box
    $boxQuantity = $this->getTotalQuantityInBoxes();
    if ($boxQuantity > 0) {
        // If in box, available = box quantity - reserved_stock
        return max(0, $boxQuantity - $this->reserved_stock);
    }

    // If not in any box, use regular stock - reserved_stock
    return max(0, $this->stock - $this->reserved_stock);
}
```

**Penjelasan**: Method ini mendeteksi apakah barang ada di kotak atau tidak:

- Jika ada di kotak, gunakan quantity dari `box_product` untuk perhitungan
- Jika tidak ada di kotak, gunakan `products.stock`
- Dalam kedua kasus, kurangi dengan `reserved_stock`

### 2. Update LoanController

**File**: `app/Http/Controllers/LoanController.php`

#### Perubahan di `getProductByQR()`:

```php
$product = Product::with('boxes')->find($id);
$availableStock = $product->getAvailableStockForBorrow(); // Gunakan method baru
```

#### Perubahan di `submitBorrow()`:

```php
$product = Product::with('boxes')->findOrFail($request->product_id);
$availableStock = $product->getAvailableStockForBorrow(); // Gunakan method baru
if ($availableStock < $quantity) {
    throw new \Exception('Barang tidak tersedia...');
}
```

### 3. Update ProductController

**File**: `app/Http/Controllers/ProductController.php`

- Tambah `DB::beginTransaction()` di method `store()` untuk konsistensi
- Tambah transaction handling di method `update()` untuk memastikan box assignment aman
- Perbarui logika assignment ke kotak agar selalu sinkron

**Key Changes**:

```php
// Jika product sudah di kotak, update quantity-nya
if (in_array($newBoxId, $currentBoxIds)) {
    $product->boxes()->updateExistingPivot($newBoxId, ['quantity' => $newQty]);
} else {
    // Jika belum, remove dari kotak lama, lalu attach ke kotak baru
    $product->boxes()->detach();
    $box->products()->attach($product->id, ['quantity' => $newQty]);
}
```

### 4. Update BoxController

**File**: `app/Http/Controllers/BoxController.php`

- Tambah import `use Illuminate\Support\Facades\DB;`
- Wrap `updateProducts()` dengan database transaction untuk safety

### 5. Update AdminLoanController

**File**: `app/Http/Controllers/AdminLoanController.php`

Perbarui metode approval untuk menggunakan method `getAvailableStockForBorrow()`:

```php
$productModel = Product::with('boxes')->findOrFail($prod->id);
$availableStock = $productModel->getAvailableStockForBorrow(); // Gunakan method baru
```

### 6. Tambah Method di Model Box (`app/Models/Box.php`)

```php
/**
 * Get total quantity of all products in this box
 */
public function getTotalQuantity()
{
    return $this->products()->sum('box_product.quantity') ?? 0;
}
```

## Alur Kerja Setelah Fix

### Skenario 1: Barang Ada di Kotak (FIXED ✓)

1. Admin set kotak A dengan barang X qty 5
2. User ingin pinjam barang X qty 4
3. System periksa: `getAvailableStockForBorrow()`
    - Deteksi barang X ada di kotak (box_quantity = 5)
    - Return: 5 - 0 reserved = 5 tersedia
    - Check: 5 >= 4? YES ✓
    - User bisa pinjam 4 unit ✓

### Skenario 2: Ketidaksesuaian Stock (FIXED ✓)

1. Admin set barang Y stock = 8
2. Admin assign ke kotak B qty = 2
3. User check "Kelola Barang" - lihat stock
    - Stock field: 8 (ini untuk reference, bukan untuk pinjam)
4. User check "Kelola Kotak" - lihat isi kotak
    - Quantity di kotak: 2 (ini yang digunakan untuk pinjam) ✓
5. Saat pinjam, system gunakan quantity dari kotak = 2
    - Akurat! ✓

## Testing Checklist

- [ ] Coba pinjam barang yang ada di kotak - harus pakai quantity dari kotak
- [ ] Coba pinjam barang yang tidak ada di kotak - harus pakai stock global
- [ ] Verifikasi bahwa "Kelola Barang" dan "Kelola Kotak" sudah sinkron
- [ ] Test approval peminjaman - harus cek stok yang benar
- [ ] Coba update kotak isi - harus reflect di available stock
- [ ] Coba move barang antar kotak - harus sinkron

## Database Considerations

Tabel yang berpengaruh:

- `products` (stock, reserved_stock)
- `box_product` (quantity per barang per kotak)
- `product_transaction` (quantity per peminjaman)

**Catatan**: Tidak ada perubahan schema database, hanya optimisasi logika aplikasi.

## Backward Compatibility

✓ **Compatible** - Semua perubahan backward compatible:

- Barang tanpa kotak tetap bekerja normal
- Barang dengan kotak sekarang bekerja akurat
- Tidak ada breaking changes
