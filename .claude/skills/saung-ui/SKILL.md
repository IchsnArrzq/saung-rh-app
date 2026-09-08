---
name: saung-ui
description: Design system saung-rh-app — token tema daisyUI, resep komponen Blade (button, card, data-table, status-badge, page-header, empty-state, form), pola halaman, dan aturan per portal. Muat skill ini sebelum menulis atau mengubah komponen, halaman, atau layout apa pun di saung-rh-app, termasuk saat diminta "bikin halaman", "tambah fitur", "perbaiki tampilan", atau "styling".
---

# Saung RH UI

Aturan wajib ada di `CLAUDE.md`. Di sini spesifikasi lengkap dan resep komponennya.

Prinsip dasarnya: **hierarki dibuat dari permukaan dan jarak, bukan dari radius dan shadow.**
Tema ini `--border: 0` dan `--depth: 0` — kalau sebuah elemen perlu menonjol, kurangi yang di
sekitarnya sebelum menambah dekorasi padanya.

Urutan kerja saat membuat/mengubah halaman ada di `docs/DAISYUI-BLUEPRINT.md` (6 peran).
Alasan UX di balik tiap aturan ada di `DESIGN.md`. Sintaks daisyUI 5 yang akurat ada di skill
`daisyui` — jangan mengarang nama kelas dari ingatan v4.

---

## 1. Token

Sumber kebenaran: `resources/css/app.css`, blok `@plugin "daisyui/theme"`. Dua tema:
`cr-cafe-resto` (terang, default) dan `cr-cafe-resto-dark` (`prefersdark`). Token yang sama,
nilai berbeda — **selalu uji keduanya.**

### Warna

| Token | Utility | Terang | Peran |
|---|---|---|---|
| `base-100` | `bg-base-100` | `#ffffff` | permukaan konten: kartu, panel, baris tabel |
| `base-200` | `bg-base-200` | `#f7f8fb` | panel sekunder, hover, header tabel |
| `base-300` | `bg-base-300` `border-base-300` | `#edf0f4` | pemisah, garis kartu |
| `base-content` | `text-base-content` | `#111315` | teks utama · `/70` sekunder · `/50` hint |
| `primary` | `bg-primary` `text-primary` | `#ff4f55` | aksi utama — satu per layar |
| `secondary` | `text-secondary` | `#62646b` | teks/aksi sekunder |
| `accent` | `bg-accent` | `#18bd85` | positif, konfirmasi, "tersedia" |
| `info` / `success` / `warning` / `error` | `badge-*` `text-*` | `#2563eb` / `#16a34a` / `#d97706` / `#ef4444` | status |

Latar halaman **bukan** `base-100`: tema terang menyetel `body { background: #eef2f7 }`, gelap
`#070616`. Konten duduk di atas itu sebagai `base-100`. Jangan membungkus `<main>` dengan kartu
putih; kartu adalah isi, bukan wadah.

**Konflik yang harus dijaga:** `primary` merah dan `error` merah. Pemisahnya bukan warna
melainkan bentuk dan kata: `primary` adalah tombol aksi utama; `error` muncul sebagai badge,
alert, atau tombol destruktif yang selalu berpasangan dengan kata kerja + konfirmasi.

### Radius, elevasi, gerak

`--radius-box: 0.75rem` → `rounded-xl` untuk kartu/panel/tabel/modal ·
`--radius-field: 0.5rem` → input/button/select (daisyUI sudah memakainya sendiri) ·
`--radius-selector: 0.5rem` → checkbox/toggle · `rounded-full` hanya avatar dan gelembung jumlah.

`rounded-2xl` ke atas sudah nol di seluruh `resources/views` (per 2026-09-08) — jangan
dikembalikan. Varian sisi ikut dihitung: `rounded-t-3xl` sama saja dengan `rounded-3xl`.

**Elevasi praktis nol.** `app.css` memaksa `.border` transparan di tema terang, meratakan
`shadow-sm|md|lg|xl` ke satu nilai, dan menghapus semua shadow di tema gelap. Jadi:

- Pemisahan kartu datang dari kontras permukaan (`base-100` di atas latar halaman), bukan garis.
- `shadow-lg` dan `shadow-sm` menghasilkan tampilan identik — memilih di antaranya sia-sia.
- Shadow hanya untuk yang benar-benar melayang: `modal`, `dropdown`, toast, sticky bar.

Gerak: `transition` 150–300 ms, hanya `opacity` dan `transform: translate`. Tanpa `hover:scale-*`.

### Tipografi

| Peran | Kelas |
|---|---|
| Judul halaman | `<x-page-header>` (`text-xl font-semibold`) |
| Judul section / kartu | `text-lg font-semibold` |
| Body & UI default | `text-sm` |
| Hint, timestamp | `text-xs text-base-content/60` |
| Angka & uang | wajib `tabular-nums`, rata kanan di tabel |

Font: Inter (`--font-sans`). `text-xs` tidak untuk informasi penting — minimum terbaca `text-sm`.

Uang selalu lewat `number_format((float) $x, 0, ',', '.')` dengan prefix `Rp`.

### Jarak

Kelipatan 4, dan berhenti di skala ini: `gap-2 · gap-3 · gap-4 · gap-6`, `space-y-6` antar
section, `p-4 md:p-5` isi kartu (sudah default `<x-card>`), `py-6` padding halaman.
Dilarang jarak arbitrer (`mt-[37px]`).

Susun grup dengan `flex`/`grid` + `gap`, bukan margin per elemen.

---

## 2. Aturan per portal

| Portal | Pekerjaan | Shell | Yang boleh menonjol |
|---|---|---|---|
| `landing` / `public` | "Ini jual apa?" | `layouts/guest` | Foto menu, heading besar, satu CTA |
| `auth` | "Saya mau masuk" | `layouts/auth` | Satu tombol submit |
| `customer` | "Pesanan saya sampai mana?" | `layouts/portals/customer` | Status pesanan, harga, keranjang |
| `admin` | "Apa yang harus saya kerjakan?" | `layouts/portals/admin` | Tabel, aksi cepat |
| `pos` | "Selesaikan transaksi ini" | `layouts/portals/admin` | Keranjang + total, tombol bayar |
| `kds` | "Apa yang harus dimasak?" | `layouts/portals/admin` | Tiket, umur pesanan |

**Customer mobile-first.** Bottom-nav, target sentuh ≥ 44 px, keranjang yang belum selesai selalu
terlihat (sticky). Berbasis status dan urutan waktu: pertanyaan pertama pengguna selalu "pesanan
saya di mana", jadi status pesanan terbaru muncul lebih dulu dari apa pun.

**Admin desktop-first.** Sidebar, tabel dulu, form kedua. Kepala halaman tipis: judul + aksi.

**POS & KDS dipakai sambil berdiri, sering, cepat.** Target besar, teks besar, sedikit warna.
Warna di KDS hanya untuk urgensi nyata (umur tiket), bukan dekorasi.

**Landing & auth** boleh sedikit lebih ekspresif — foto adalah elemen utama, jangan ditutupi
gradient scrim atau badge bertumpuk. Satu CTA per layar.

---

## 3. Resep komponen

Semuanya di `resources/views/components/`. **Di luar folder itu, dilarang menulis `class="btn …"`,
`class="badge …"`, `class="input …"`, `class="select …"` sendiri.** Butuh varian baru → tambah
prop di komponennya.

| Komponen | Untuk |
|---|---|
| `<x-button>` | Semua aksi, termasuk yang berpindah halaman (`href`) |
| `<x-card>` | Permukaan berbingkai; slot `actions`, `footer` |
| `<x-page-header>` | Judul halaman + slot `actions` |
| `<x-data-table>` | Tabel + pembungkus `overflow-x-auto`; slot `head`, `foot` |
| `<x-badge>` / `<x-status-badge>` | Chip generik / status dari Enum |
| `<x-stat-card>` | KPI dashboard |
| `<x-empty-state>` | Ikon → pesan → aksi |
| `<x-alert>` | Pesan sistem (bukan error per-field) |
| `<x-skeleton>` / `<x-spinner>` | Keadaan memuat |
| `<x-field>` `<x-input>` `<x-select>` `<x-textarea>` `<x-checkbox>` `<x-password-input>` | Form |
| `<x-search-input>` | Kotak pencarian |
| `<x-form-actions>` | Batal + Simpan di bawah-kanan |
| `<x-modal>` | Dialog Alpine (`name`, `show`, `maxWidth`) |

### Button

```blade
<x-button variant="primary" icon="ri-add-line">Tambah menu</x-button>
<x-button variant="ghost" :href="route('admin.menus.index')" wire:navigate>Batal</x-button>
<x-button variant="error" size="sm" shape="square" icon="ri-delete-bin-line"
    label="Hapus menu" wire:click="delete('{{ $menu->id }}')"
    data-confirm="Hapus menu ini?" />
<x-button type="submit" variant="primary" :loading="'save'">Simpan</x-button>
```

- `variant`: `primary` · `outline` · `ghost` · `error` · `accent` · `neutral` · token semantik lain.
- `shape="square|circle"` → **wajib** `label` (jadi `aria-label` + `title`).
- `loading` = nama method Livewire → spinner + `wire:loading.attr="disabled"` otomatis.
- `href` merender `<a>`; jangan pernah `<a>` di dalam `<button>`.
- Default `type` adalah `button` — tombol submit wajib menulis `type="submit"`.
- Satu `variant="primary"` per layar.

### Card

```blade
<x-card title="Ringkasan hari ini" description="Data pukul 00:00 sampai sekarang">
    <x-slot:actions><x-button size="sm" variant="outline">Ekspor</x-button></x-slot:actions>
    …
</x-card>
```

Tanpa shadow. Tanpa bingkai kedua di dalamnya — kelompokkan dengan `divide-y divide-base-300`.
`flush` mematikan padding untuk isi yang mengatur padding sendiri (tabel).

### DataTable

```blade
<x-data-table>
    <x-slot:head>
        <tr><th>Menu</th><th class="text-right">Harga</th><th class="w-px">Aksi</th></tr>
    </x-slot:head>

    @forelse ($menus as $menu)
        <tr>
            <td>{{ $menu->name }}</td>
            <td class="text-right tabular-nums">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</td>
            <td>…</td>
        </tr>
    @empty
        <tr><td colspan="3" class="py-8 text-center text-base-content/50">Belum ada menu.</td></tr>
    @endforelse
</x-data-table>

{{ $menus->links() }}
```

Kolom identitas pertama, kolom aksi terakhir (Lihat → Ubah → Hapus), kolom angka rata kanan +
`tabular-nums`. Sudah `overflow-x-auto`, jadi jangan dibungkus lagi. Baris kosong dan baris
skeleton hidup di dalam `<tbody>`, bukan di sekitar tabel.

### StatCard

```blade
<x-stat-card title="Pesanan aktif" :value="$activeOrders" icon="ri-file-list-3-line"
    :href="route('admin.orders.index')" />
```

Angka besar + label. `description` hanya kalau perbandingannya benar-benar dihitung dari data —
bukan "naik dari kemarin" yang tidak pernah dihitung. Kalau angkanya belum ada sumbernya, jangan
render kartunya sama sekali.

### StatusBadge

Satu-satunya cara menampilkan status. Label dan warna berasal dari Enum domain — jangan pernah
menulis label atau kelas warna status di view.

```blade
<x-status-badge :status="$order->status" />
{{-- kolom yang masih string mentah: sebutkan Enum-nya --}}
<x-status-badge :status="$table->status" :enum="\App\Domains\Table\Enums\TableStatus::class" />
```

Enum yang tersedia ada di `app/Domains/*/Enums/` — `OrderStatus`, `ReservationStatus`,
`PaymentStatus`, `PaymentMethod`, `TableStatus`, `MenuAvailability`, `ShiftStatus`,
`StockMovementType`, `DocumentStatus`, `SongStatus`, `SpecialRequestStatus`, `SubscriptionStatus`.
Tabel referensi label/warna ada di `DESIGN.md` § Restaurant Specific UI — kalau tabel dan Enum
berbeda, **Enum yang menang** dan dokumen yang diperbaiki.

Catatan `Order`, `Shift`, `SongRequest`, `SpecialRequest`, `Subscription` sudah di-cast ke Enum;
`Table`, `Menu`, `Reservation`, `Payment` masih string — cek modelnya sebelum menulis perbandingan.

### PageHeader

```blade
<x-page-header title="Daftar menu">
    <x-slot:actions>
        <x-button :href="route('admin.menus.create')" wire:navigate icon="ri-add-line">Tambah menu</x-button>
    </x-slot:actions>
</x-page-header>
```

Judul + aksi. `description` hanya kalau benar-benar menjelaskan sesuatu — bukan "Table Menu"
atau eyebrow hiasan.

### EmptyState

Menjelaskan apa yang seharusnya ada di situ, lalu menawarkan aksi untuk mengisinya.

```blade
<x-empty-state icon="ri-restaurant-line" title="Belum ada menu"
    description="Menu yang kamu tambahkan akan muncul di sini.">
    <x-slot:actions>
        <x-button :href="route('admin.menus.create')" wire:navigate>Tambah menu pertama</x-button>
    </x-slot:actions>
</x-empty-state>
```

Bukan "Belum ada data." satu baris tanpa jalan keluar.

### Form

```blade
<form wire:submit="save" class="grid gap-4 md:grid-cols-2">
    <x-input label="Nama menu" name="form.name" wire:model="form.name" required />
    <x-input label="Harga" name="form.price" type="number" min="0" inputmode="numeric"
        wire:model="form.price" required />
    <x-select label="Kategori" name="form.category_id" wire:model="form.category_id"
        :options="$categories" placeholder="Pilih kategori" required fieldClass="md:col-span-2" />
    <x-textarea label="Deskripsi" name="form.description" wire:model="form.description"
        fieldClass="md:col-span-2" />

    <x-form-actions class="md:col-span-2" :cancel-href="route('admin.menus.index')" :loading="'save'" />
</form>
```

- Urutan wajib: Label → Input → Hint → Error. `<x-field>` yang mengurusnya; error diambil otomatis
  dari bag validasi lewat `name`.
- `fieldClass` untuk kelas pembungkus grid; `class` biasa menempel ke elemen input.
- **`<select>` mentah dilarang** — `<x-select>` yang mengirim `data-placeholder`/`data-clearable`
  yang dibaca `resources/js/enhance.js` (Tom Select: cari + clear). `:enhance="false"` untuk
  memaksa select bawaan.
- **Footgun `name`:** atribut `name` HTML hanya dipasang kalau field tidak punya `wire:model`.
  Di `<form method="POST">` biasa, prop `name` wajib diisi.
- Maksimal dua kolom. Jangan memilih otomatis opsi pertama untuk field yang menentukan.
- Untuk memilih dari daftar bergambar/berstatus (meja, menu), pertimbangkan picker visual, bukan
  dropdown kode — `DESIGN.md` § Recognition over Recall.

### Modal

`<x-modal name="confirm-delete" maxWidth="md">` (Alpine). Untuk konfirmasi hapus, **jangan** bikin
modal sendiri — cukup `data-confirm="Hapus menu ini?"` pada tombol/form; `resources/js/app.js`
mengubahnya jadi dialog SweetAlert bergaya tema, dengan varian destruktif otomatis.

### Empat state

```blade
<div wire:loading.delay wire:target="search"><x-skeleton :rows="5" /></div>

<div wire:loading.remove wire:target="search">
    @forelse ($rows as $row) … @empty <x-empty-state … /> @endforelse
</div>

@error('load') <x-alert type="error">{{ $message }}</x-alert> @enderror
```

Loading berupa teks polos ("Memuat data...") dihitung pelanggaran — pakai skeleton yang menyerupai
bentuk isinya.

---

## 4. Pola halaman

**Index:** `PageHeader` → filter (`search-input` + `select`) → `DataTable` → paginasi.
**Create/Edit:** `PageHeader` → `Card` berisi form → `form-actions`.
**Dashboard:** baris `stat-card` → aktivitas terbaru → widget operasional → grafik.
**Detail:** `PageHeader` → ringkasan (label/nilai) → riwayat/timeline → aksi.

Satu komponen Livewire satu tujuan: `Table`, `Form`, `Detail` — bukan `Management`
(`AGENTS.md` § Livewire Rules). Blade tetap wrapper: tanpa logika bisnis, tanpa query.

---

## 5. Do / don't

```blade
{{-- ✕ JANGAN --}}
<div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
    <p class="text-xs uppercase tracking-[0.3em] text-emerald-500">Pesanan</p>
    <p class="mt-3 text-2xl font-semibold">{{ $orders->count() }}</p>
</div>

{{-- ✓ LAKUKAN --}}
<x-stat-card title="Pesanan aktif" :value="$orders->total()" />
```

```blade
{{-- ✕ JANGAN — status dirakit di view --}}
<span class="badge badge-{{ $order->status }}">{{ $order->status }}</span>

{{-- ✓ LAKUKAN — label & warna dari Enum --}}
<x-status-badge :status="$order->status" />
```

```blade
{{-- ✕ JANGAN — afordans mati --}}
<button class="btn btn-primary">Cetak struk</button>

{{-- ✓ LAKUKAN --}}
<x-button variant="primary" wire:click="print" :loading="'print'">Cetak struk</x-button>
```

---

## 6. Checklist sebelum selesai

**Visual**

- [ ] Tidak ada `rounded-2xl`/`3xl`/arbitrer; `rounded-full` hanya avatar & gelembung jumlah.
- [ ] Tidak ada shadow di permukaan statis.
- [ ] Tidak ada eyebrow uppercase + tracking lebar di atas heading.
- [ ] Tidak ada kartu di dalam kartu.
- [ ] Semua warna lewat token — tidak ada `bg-white`, `stone-*`, `emerald-*`, hex mentah, `dark:*` manual.
- [ ] Semua angka `tabular-nums`; uang lewat `number_format`.
- [ ] Kelas dinamis ditulis utuh (scanner Tailwind v4).
- [ ] Terbaca di tema terang **dan** gelap.

**Kejujuran**

- [ ] Tidak ada agregat yang dijumlahkan dari halaman terbuka lalu dipajang sebagai total.
- [ ] Tidak ada tombol tanpa handler, `href="#"`, atau form tanpa `wire:submit`/`action`.
- [ ] Semua link pakai `route()` dan rutenya ada di `routes/`.
- [ ] Tidak ada janji estimasi/promo/rating tanpa kolom datanya.
- [ ] Tidak ada UUID atau nilai backing enum mentah di layar.
- [ ] Tidak ada nilai karangan yang tersimpan ke database.
- [ ] Tidak ada catatan developer yang ikut dirender.

**Perilaku**

- [ ] Tidak ada query database di `render()`.
- [ ] Setiap `wire:click` ke server punya keadaan memuat; `wire:model.live` punya `.debounce.300ms`.
- [ ] Loading, empty, error, success semuanya ditangani.
- [ ] Nav punya active state via `request()->routeIs()`.
- [ ] Field penentu (meja, kategori, role, metode bayar) tidak dipilih otomatis.
- [ ] Penghapusan lewat `data-confirm`.
- [ ] `authorize()` ada di dalam method tulis, bukan hanya `mount()`; `@can` di Blade.

**Form**

- [ ] Label lewat prop `label`; tidak ada `<label>` lepas tanpa `for`.
- [ ] Prop `name` terisi untuk field di `<form method="POST">` non-Livewire.
- [ ] Tidak ada `<select>` mentah.
- [ ] `type`/`inputmode` valid; `min`/`max` untuk angka.
- [ ] Aturan validasi cocok dengan Form Request/Form Object.

**Penutup**

- [ ] Teks Indonesia, sentence case, tombol menyebut aksinya.
- [ ] `npm run check:ui` lolos (tidak menambah pelanggaran di atas baseline).
- [ ] `php artisan view:cache` lalu `grep -l '<x-[a-z]\|<?php(' storage/framework/views/*.php` kosong —
      tidak ada tag komponen yang gagal di-compile.
- [ ] `npm run build` bersih.
- [ ] Subagent `ui-reviewer` dijalankan untuk perubahan yang menyentuh halaman atau komponen.
- [ ] Checklist psikologi di `DESIGN.md` sudah dijalankan untuk layar baru.
