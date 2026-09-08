# Saung RH — Smart Cafe & Resto

Sistem manajemen restoran. Satu aplikasi Laravel:

- **Laravel 12 + Livewire 3 (+ Volt)** — server-rendered, Blade sebagai wrapper.
- **Tailwind v4 CSS-first + daisyUI 5** — tema didefinisikan di `resources/css/app.css`, bukan di `tailwind.config.js`.
- **PostgreSQL** · Reverb (websocket) · Spatie Permission.

Portal: `landing` (publik), `auth`, `customer` (mobile-first, bottom-nav), `admin` (sidebar),
`pos`, `kds`, `staff`.

## Peta dokumen — jangan duplikasi isinya

| Butuh tahu | Baca |
|---|---|
| Arsitektur, layer, domain, autorisasi | `AGENTS.md` |
| Kenapa arsitekturnya begitu, alur lintas domain | `ARCHITECTURE.md` |
| UX, psikologi, heuristik, ethical guardrails | `DESIGN.md` |
| Alur kerja 6 peran saat membangun halaman | `docs/DAISYUI-BLUEPRINT.md` |
| Token, resep komponen, aturan per portal | skill `saung-ui` |
| Sintaks daisyUI 5 yang akurat | skill `daisyui` (`.agents/skills/daisyui/`) |

**Muat skill `saung-ui` sebelum menulis atau mengubah komponen, halaman, atau layout apa pun.**
Aturan di bawah berlaku tanpa pengecualian; kalau bertabrakan dengan dokumen lain, dokumen lain
yang salah dan harus diperbaiki.

---

## A. Aturan visual

### 1. Radius pakai skala tema

Tema mendefinisikan `--radius-box: 0.75rem` (kartu/panel/tabel/modal → `rounded-xl`),
`--radius-field: 0.5rem` (input/button/select), `--radius-selector: 0.5rem` (checkbox/toggle).
`rounded-full` **hanya** avatar dan gelembung jumlah.

**Dilarang:** `rounded-2xl`, `rounded-3xl`, dan radius arbitrer `rounded-[…]` ≥ 1rem.

### 2. Border dulu, shadow terakhir — dan di tema ini shadow hampir tak berarti

Tema terang menyetel `--border: 0` dan `--depth: 0`. Lebih jauh, `resources/css/app.css`
memaksa utility `.border` menjadi `border-color: transparent` di tema terang, meratakan
`shadow-sm|md|lg|xl` jadi satu nilai yang sama, dan tema gelap membuang semua shadow dengan
`box-shadow: none !important`.

Artinya: **memilih `shadow-lg` alih-alih `shadow-sm` tidak mengubah apa pun.** Hierarki dibuat
dari permukaan — `base-100` untuk konten di atas latar halaman `#eef2f7`, `base-200` untuk
panel/hover, `base-300` untuk pemisah — bukan dari elevasi.

Shadow hanya sah untuk yang benar-benar melayang: modal, dropdown, toast, sticky bar.

### 3. Satu kartu, satu gagasan

Tidak ada `<x-card>` di dalam `<x-card>`. Kelompokkan dengan jarak (`space-y-6`, `gap-4`) dan
`divide-y divide-base-300`, bukan bingkai kedua.

`<x-data-table>` sudah membawa pembungkusnya sendiri — jangan dibungkus lagi dengan `<x-card>`;
kalau memang perlu judul di atasnya, pakai `<x-card flush>`.

### 4. Eyebrow dihapus

**Dilarang** label mikro `uppercase` + `tracking-[0.2em]`/`[0.3em]` di atas heading. Judul
section satu baris, sentence case. Uppercase hanya di header tabel dan badge.

### 5. Tipografi dan angka

Skala ada di `DESIGN.md` § Typography: judul halaman lewat `<x-page-header>`, judul kartu
`text-lg font-semibold`, body `text-sm`, hint `text-xs text-base-content/60`. Minimum terbaca
`text-sm` — `text-xs` bukan untuk informasi penting.

Uang selalu `Rp {{ number_format((float) $x, 0, ',', '.') }}`. **Semua angka yang dibandingkan
antar baris — harga, stok, jumlah, persentase, nomor meja — wajib `tabular-nums`** dan rata kanan
di kolom tabel.

### 6. Warna itu makna

Warna hanya lewat token daisyUI. **Dilarang** `bg-white`, `text-black`, `bg-stone-200`,
`text-emerald-600`, hex mentah seperti `#ff4f55`, dan `dark:*` manual untuk warna yang sudah token.

| Peran | Token |
|---|---|
| Permukaan | `bg-base-100` → `bg-base-200` (panel/hover) → `bg-base-300` (pemisah) |
| Teks | `text-base-content` · `/70` sekunder · `/50` hint |
| Aksi utama | `primary` (merah `#ff4f55`) — satu per layar |
| Positif/konfirmasi | `accent` (hijau) |
| Status | `info` `success` `warning` `error` |

**Konflik yang harus dijaga:** `primary` merah dan `error` juga merah. Merah saja tidak pernah
berarti bahaya di sini. Aksi merusak butuh tiga-tiganya: `variant="error"`, kata kerja eksplisit
di label ("Hapus menu"), dan konfirmasi `data-confirm`.

### 7. Class dinamis harus utuh

Scanner Tailwind v4 tidak membaca kelas yang dirakit dari variabel.

```blade
{{-- ✕ --}}  <span class="badge badge-{{ $color }}">
{{-- ✓ --}}  <x-status-badge :status="$order->status" />
```

Kalau butuh peta sendiri, tulis nama kelas penuh di setiap cabang.

### 8. Gerak 150–300 ms, dua properti

Hanya `opacity` dan `transform: translate`. **Dilarang** `hover:scale-*` dan durasi > 300 ms.
Hormati `prefers-reduced-motion`.

### 9. Komposisi

Kartu sebaris wajib sejajar barisan aksinya — dorong blok harga+tombol dengan `mt-auto`.
Konten lebar (tabel, struk, kode) scroll di dalam wadahnya sendiri (`overflow-x-auto`), tidak
pernah menggeser halaman.

---

## B. Aturan kejujuran — data dan afordans

Ini yang paling sering dilanggar dan paling merusak kepercayaan. `DESIGN.md` § Ethical Guardrails
adalah versi panjangnya.

### Angka

- **Jangan menjumlahkan koleksi halaman yang sedang dibuka lalu memajangnya sebagai total.**
  `$orders->count()` atas hasil `paginate()` adalah jumlah baris di halaman ini, bukan total —
  pakai `$orders->total()`, atau minta agregatnya ke QueryUseCase/Repository.
- Dilarang menghitung metrik dari rumus lalu menampilkannya sebagai fakta.
- Kalau datanya tidak ada di domain, **UI tidak menampilkannya**. Pakai empty state, atau jangan
  render bloknya sama sekali. Jangan memperkirakan.

### Klaim

Dilarang menjanjikan hal yang tidak diketahui sistem: estimasi waktu saji, SLA, "terlaris",
rating, promo, hitung mundur, jumlah pengunjung, riwayat perusahaan. Kalau kolomnya tidak ada di
`app/Domains/*/Models` atau `database/migrations`, itu karangan.

"Rekomendasi Chef" dan "Terlaris" hanya boleh dari flag atau agregat nyata di database.

### Afordans

Dilarang afordans mati:

- `<button>` tanpa `wire:click`, `type="submit"`, atau handler Alpine
- `href="#"` atau `href=""`
- `cursor-pointer` pada elemen yang tidak bisa diklik
- form tanpa `action` atau `wire:submit`
- link ke rute yang tidak ada — cek dulu di `routes/`, dan **selalu pakai `route('nama')`**,
  bukan URL literal

### Identitas internal

UUID dan nilai backing enum tidak muncul di layar. `no_show`, `order_in`, `stock_opname_draft`
dilarang dirender — pakai `$status->label()` lewat `<x-status-badge>`. ID mentah hanya di layar
detail admin dengan label jelas ("ID pesanan").

### Menulis ke database

Dilarang menulis nilai karangan ke kolom nyata (nama kasir, nama kurir, catatan). Kalau harus
diisi manusia, sediakan input. Dilarang meminta field yang dibuang validasi — cek dulu Form
Request / Form Object-nya.

### Catatan developer

Dilarang merender memo untuk diri sendiri ke layar ("bisa dipakai untuk simulasi callback",
"kolom relasional menyusul"). Taruh di komentar Blade `{{-- --}}`.

---

## C. Livewire — state, loading, autorisasi

- **Dilarang query database di `render()`.** Baca lewat QueryUseCase (`AGENTS.md` § Livewire Rules).
- **Setiap `wire:click` yang memukul server butuh keadaan memuat.** `<x-button :loading="'save'">`
  mengurus disabled + spinner otomatis; untuk blok besar pakai `wire:loading` + `<x-skeleton>`.
  Ambang Doherty 400 ms — lihat `DESIGN.md` § 24.
- **`wire:model.live` wajib `.debounce.300ms`** kecuali memang toggle/select yang harus instan.
- Jangan menghitung nilai turunan di `mount()` lalu membiarkannya basi — turunkan saat render
  atau lewat computed property.
- Dilarang memanggil UseCase/endpoint yang hasilnya tidak dirender.
- Jangan memilih otomatis opsi pertama untuk field yang menentukan (meja, menu, role, metode
  bayar) — data bisa tersimpan ke tempat yang salah tanpa disadari.
- **Autorisasi tiga lapis wajib lengkap** (`AGENTS.md` § Authorization): `can:` di rute,
  `$this->authorize()` **di dalam method tulis** (bukan hanya `mount()`), `@can` di Blade.
  Menyembunyikan tombol itu kosmetik — method Livewire adalah endpoint HTTP.

### Empat state wajib

Setiap komponen yang mengambil data menangani keempatnya:

1. **Loading** — `<x-skeleton>` yang menyerupai bentuk isinya. Teks polos "Memuat data..." dihitung pelanggaran.
2. **Empty** — `<x-empty-state>` yang menjelaskan apa yang seharusnya ada **plus** aksi untuk mengisinya.
3. **Error** — `<x-alert type="error">` dengan kalimat yang bisa dibaca manusia + jalan keluar.
   Dilarang membuang pesan exception mentah ke layar.
4. **Success**

---

## D. Aturan form

- Pakai `<x-input>` / `<x-select>` / `<x-textarea>` / `<x-checkbox>` dengan prop `label`, `name`,
  `hint`, `required`. Jangan `<label>` terpisah tanpa `for`.
- **Footgun `name`:** komponen hanya memasang atribut `name` HTML kalau field **tidak** punya
  `wire:model`. Di `<form method="POST">` biasa, prop `name` wajib diisi — tanpa itu field tidak
  ikut terkirim sama sekali.
- **Dilarang `<select>` mentah.** Semua select di-upgrade jadi Tom Select oleh
  `resources/js/enhance.js`, dan yang mengirim `data-placeholder`/`data-clearable` adalah
  `<x-select>`. Opt out dengan `:enhance="false"`, bukan dengan menulis `<select>` sendiri.
- Urutan field: Label → Input → Hint → Error. Field wajib ditandai `required`.
- `type` valid: `tel` untuk telepon (+ `inputmode="numeric"`), `number` untuk kuantitas dengan
  `min`/`max`. Batasi dulu, validasi kemudian.
- Aturan validasi mengikuti Form Request/Form Object. Jangan mengklaim aturan yang tidak ditegakkan.
- Input tidak boleh hilang setelah validasi gagal.
- Aksi form di bawah-kanan lewat `<x-form-actions>`: Batal (ghost) lalu Simpan (primary).
- Penghapusan selalu lewat `data-confirm` (SweetAlert, `resources/js/app.js`).

---

## E. Komponen — jangan ditulis ulang

Library kanonik ada di `resources/views/components/`. **Di luar folder itu, dilarang menulis
`class="btn …"`, `class="badge …"`, `class="input …"`, `class="select …"`, `class="textarea …"`
sendiri.**

`button` · `badge` · `status-badge` · `alert` · `card` · `page-header` · `data-table` ·
`empty-state` · `stat-card` · `field` · `input` · `select` · `textarea` · `checkbox` ·
`search-input` · `password-input` · `form-actions` · `skeleton` · `spinner` · `modal` ·
`dropdown` · `nav-link`

Prop lengkapnya ada di skill `saung-ui`. Butuh varian baru → tambah prop di komponennya, jangan
bikin markup tandingan di halaman.

---

## F. Navigasi dan tombol

- Semua nav wajib punya active state dari `request()->routeIs('admin.orders.*')` — bukan
  pencocokan string URL.
- Satu `variant="primary"` per layar. Aksi merusak di samping aksi utama pakai `ghost`/`outline`;
  `variant="error"` solid disimpan untuk konfirmasi akhir.
- Tombol ikon-saja (`shape="square|circle"`) **wajib** `label` — itu yang jadi `aria-label` + `title`.
- Aksi yang berpindah halaman pakai `<x-button href="…" wire:navigate>`, bukan `<a>` yang
  dibungkus `<button>`.
- Urutan kolom aksi tabel selalu: Lihat → Ubah → Hapus.
- Target sentuh ≥ 44 px di portal customer. `btn-sm btn-square` hanya untuk tabel admin.

---

## G. Bahasa

UI berbahasa Indonesia, sentence case, tanpa lorem ipsum. Tombol menyebut aksinya persis
("Tambah menu", bukan "Submit"). Pesan error tenang dan memberi jalan keluar
("Waktu reservasi harus setelah jam sekarang", bukan "Input Anda salah"). Jangan memakai istilah
di luar domain restoran ("pipeline", "entity", "payload") di teks yang dilihat pengguna.

---

## H. Sebelum commit perubahan UI

```bash
npm run check:ui
```

Lalu `npm run build` (harus bersih) dan jalankan subagent **`ui-reviewer`** untuk review yang
lebih dalam — banyak aturan di dokumen ini (kejujuran data, empat state, autorisasi, hierarki)
tidak bisa ditangkap grep.

### Wajib: pastikan Blade-nya benar-benar ter-compile

Blade **gagal diam-diam**. Direktif atau komentar `{{-- --}}` di dalam tag komponen membuat tag
itu tidak dikenali, dan `@php(...)` dengan tanda kurung bersarang dua tingkat memutus kompilasi
sisa berkas — keduanya lolos linter, lolos `npm run build`, dan baru terlihat sebagai `<x-badge`
mentah di HTML pengguna.

```bash
php artisan view:clear && php artisan view:cache
grep -l '<x-[a-z]\|<?php(' storage/framework/views/*.php    # harus kosong
```

Aturannya: komentar selalu **di atas** tag komponen, kondisi lewat prop (`:checked="(bool) old(…)"`),
dan `@php … @endphp` bentuk blok kalau ekspresinya memanggil fungsi di dalam fungsi.

### Ratchet

`scripts/check-ui.mjs` memeriksa 17 aturan mekanis dari § A–G di seluruh `resources/views`.
`.check-ui-baseline.json` mencatat **plafon pelanggaran per file**; check gagal kalau angkanya
naik. Baseline saat ini **kosong** — ketujuh belas aturan nol pelanggaran, jadi apa pun yang
muncul adalah milik perubahan yang sedang kamu tulis.

```bash
npm run check:ui                                    # seluruh views
npm run check:ui -- resources/views/livewire/pos    # batasi ke path tertentu
npm run check:ui -- --all                           # tampilkan pelanggaran warisan juga
npm run check:ui -- --update                        # kunci penurunan ke baseline
```

Kalau sebuah baris memang pengecualian sah (shadow pada dropdown melayang, `wire:model.live` yang
memang harus instan), tulis komentar `check-ui-allow` beserta alasannya **di baris itu atau tepat
di atasnya**.

Per 2026-09-08 seluruh `resources/views` **nol pelanggaran** untuk ketujuh belas aturan (441
pelanggaran warisan dibereskan, dua berkas scaffolding Laravel yang tidak dirouting —
`welcome.blade.php` dan `livewire/welcome/navigation.blade.php` — dihapus).

Jangan menaikkan baseline supaya lolos. Kalau memang ada pengecualian sah, `check-ui-allow`
adalah jalannya; kalau tidak, perbaiki barisnya.
