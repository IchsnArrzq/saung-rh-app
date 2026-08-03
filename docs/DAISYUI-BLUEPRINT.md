# daisyUI Blueprint — Design Workflow (Tailwind v4 + daisyUI 5)

> Panduan membangun UI di saung-rh-app **tanpa MCP** — murni referensi.
> Alur kerja: **Blueprint** merancang konteks halaman, mengarahkan desain, mengambil sintaks
> komponen yang akurat, men-generate kode, lalu memverifikasi hasil.
>
> Jalankan 6 peran ini **berurutan** setiap kali membuat/mengubah halaman.
> Setup: Tailwind v4 CSS-first (`resources/css/app.css`) · daisyUI `^5.5.19` · Livewire 3 + Blade.

---

## Peran 1 — daisyUI Setup Expert
*Pastikan fondasi benar sebelum menulis markup.*

- **Tailwind v4 CSS-first** — tidak ada `@tailwind` directives. Entry: `@import "tailwindcss";` di `resources/css/app.css`.
- **Plugin daisyUI** dideklarasi via `@plugin "daisyui" { themes: ... }` di CSS, **bukan** di `tailwind.config.js`.
- **Dua tema aktif:**
  - `cr-cafe-resto` → `--default` (light, `color-scheme: light`)
  - `cr-cafe-resto-dark` → `--prefersdark` (dark)
- **`@source`** sudah menjangkau `../**/*.blade.php` + Livewire + pagination + compiled views. Komponen baru di path itu otomatis ke-scan — **jangan** hardcode class dinamis yang tak terbaca scanner (lihat Peran 2).
- **Build:** `npm run build` (Vite) / `npm run dev`. Setelah ubah tema/token → rebuild + `view:cache` clear.
- **Cek sebelum mulai:** komponen sudah ada belum? Reuse dulu (AGENTS §AI Agent Rules #6 "jangan duplikasi UI").

---

## Peran 2 — daisyUI Rules Enforcer
*Aturan wajib yang tak boleh dilanggar (selaras AGENTS §UI Rules).*

- **daisyUI-first, Tailwind kedua.** Pakai komponen (`btn`, `card`, `table`, `modal`, `drawer`, `badge`, `alert`, `dropdown`, `tabs`, `menu`, `stat`) sebelum utility mentah.
  - ❌ `<div class="bg-white rounded-lg shadow px-6 py-4 border">`
  - ✅ `<div class="card bg-base-100">`
- **Warna HANYA lewat token semantik** — jangan hardcode warna literal.
  - ❌ `bg-emerald-800`, `text-stone-500`, `bg-[#ff4f55]`
  - ✅ `bg-primary`, `text-base-content/70`, `bg-base-200`, `text-success`
  - Token proyek: `primary`(#ff4f55 merah), `accent`(#18bd85 hijau), `secondary`(abu), + `info/success/warning/error`, `base-100/200/300`, `*-content`.
- **Radius pakai skala tema** — `rounded-box`(0.75rem kartu), `rounded-field`(0.5rem input), `rounded-selector`. Konvensi existing: kartu `rounded-xl`.
- **Tema ini `--border:0` & `--depth:0`** → jangan andalkan shadow/border tebal untuk hierarki; pakai `bg-base-200`/`base-300` untuk separasi. Konvensi kartu existing: `border border-base-300 bg-base-100 rounded-xl`.
- **Dark mode gratis** kalau pakai token — **jangan** tulis `dark:` manual untuk warna yang sudah token-based.
- **Class dinamis harus utuh** (Tailwind v4 scanner tak baca string terpotong):
  - ❌ `class="text-{{ $color }}-500"`
  - ✅ `class="{{ $active ? 'bg-primary text-primary-content' : 'text-secondary' }}"` (kelas penuh di tiap cabang)
- **Blade = wrapper** (AGENTS §View): tanpa logika bisnis; UI kompleks → komponen Livewire/Blade component.

---

## Peran 3 — daisyUI Creative Director
*Arahkan rasa & konsistensi visual sebelum menyusun layout.*

- **Identitas:** kafe & resto modern, bersih, flat (tanpa depth/border tebal), aksen merah `primary` untuk aksi utama, hijau `accent` untuk status positif/konfirmasi.
- **Hierarki via warna base:** `base-100` permukaan utama → `base-200` panel/hover → `base-300` garis pemisah/kartu. Teks: `base-content` judul, `base-content/70` sekunder, `base-content/50` hint.
- **Aksi:** `btn btn-primary` (aksi utama, 1 per layar), `btn btn-ghost`/`btn-outline` (sekunder), `btn btn-error` (destruktif). Ukuran default; `btn-sm` untuk tabel.
- **Status pakai token semantik** (nanti dari Enum `->color()`): `badge badge-success` (available/paid), `badge-warning` (order_in/preparing), `badge-error` (occupied/cancelled), `badge-info`, `badge-secondary` (reserved).
- **Spacing konsisten:** `gap-4`/`gap-6`, padding kartu `card-body` (default), section `space-y-6`.
- **Responsif dulu:** grid `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`, tabel besar → `overflow-x-auto`. Portal customer mobile-first (bottom-nav), admin desktop-first (sidebar).
- **Tiga shell layout** (jaga konsisten internal masing-masing): guest, admin-sidebar, customer-bottomnav.

---

## Peran 4 — daisyUI Page Architect
*Susun struktur halaman sesuai pola konsistensi AGENTS §UI Rules.*

- **Halaman Index (list):**
  ```
  Header (judul + tombol aksi utama)
  → Filters (search / dropdown)
  → Table (atau grid card)
  → Pagination
  ```
- **Halaman Create/Edit:**
  ```
  Header
  → Form (fieldset/label + input)
  → Actions (Simpan primary / Batal ghost)
  ```
- **Dashboard:** baris `stat` (KPI) → grid kartu/chart.
- **Bungkus per komponen Livewire** (one component one purpose): `Table`, `Form`, `Detail` terpisah — bukan `Management`.
- **Modal via daisyUI** (`<dialog class="modal">`) untuk konfirmasi/quick-form, dikendalikan state Livewire.
- **Empty state & loading:** sediakan kondisi kosong (`text-base-content/50` + ilustrasi/ikon) dan `wire:loading` (skeleton/`loading loading-spinner`).
- **Aksesibilitas:** `label` terkait input, `aria-*` pada tombol ikon, kontras token sudah aman.

---

## Peran 5 — daisyUI Component Syntax Expert
*Sintaks komponen yang benar (daisyUI 5). Salin, jangan karang.*

- **Button:** `<button class="btn btn-primary">` · variasi `btn-outline btn-ghost btn-error btn-sm btn-circle` · loading: `<button class="btn"><span class="loading loading-spinner"></span></button>`
- **Card:** `<div class="card bg-base-100 border border-base-300 rounded-xl"><div class="card-body"><h2 class="card-title">..</h2>..</div></div>`
- **Table:** `<div class="overflow-x-auto"><table class="table"><thead>..</thead><tbody>..</tbody></table></div>` · zebra: `table-zebra`
- **Badge:** `<span class="badge badge-success">Tersedia</span>` · outline: `badge-outline`
- **Alert:** `<div role="alert" class="alert alert-success"><span>..</span></div>`
- **Modal (dialog):**
  ```html
  <dialog class="modal" :open="$wire.showModal">
    <div class="modal-box">..<div class="modal-action"><button class="btn" wire:click="close">Tutup</button></div></div>
  </dialog>
  ```
- **Form control (daisyUI 5):** `<label class="form-control"><span class="label-text">..</span><input class="input input-bordered" /></label>` · select `select select-bordered` · textarea `textarea textarea-bordered`
- **Tabs:** `<div role="tablist" class="tabs tabs-boxed"><a role="tab" class="tab tab-active">..</a></div>`
- **Dropdown / Menu / Drawer / Stat:** ikuti pola daisyUI 5 (`dropdown dropdown-end`, `menu`, `drawer drawer-side`, `stats`→`stat`→`stat-title/value/desc`).
- ⚠️ **Perubahan daisyUI 5 vs 4:** beberapa nama util berubah — verifikasi ke dokumen resmi versi 5 saat ragu, jangan pakai memori v4.

---

## Peran 6 — daisyUI Quality Inspector
*Checklist verifikasi sebelum anggap selesai.*

- [ ] **Zero warna hardcoded** — grep `bg-emerald|text-stone|bg-\[#|#[0-9a-f]{6}` di file yang diubah = kosong.
- [ ] **daisyUI-first** — tak ada re-implementasi manual komponen yang sudah ada (card/btn/modal).
- [ ] **Class dinamis utuh** — tak ada `text-{{...}}-500` yang lolos scanner.
- [ ] **Light + Dark** dua-duanya kebaca (token, bukan `dark:` manual).
- [ ] **Responsif** — cek mobile (customer) & desktop (admin); tabel lebar `overflow-x-auto`.
- [ ] **State lengkap** — empty, loading (`wire:loading`), error/validation (`input-error` + pesan).
- [ ] **Build bersih** — `npm run build` sukses, tak ada class tak dikenal.
- [ ] **Konsisten** dengan pola halaman sejenis (Index/Form) & shell layout terkait.
- [ ] **Reuse** — komponen/partial dipakai ulang, bukan duplikat.
- [ ] **Blade tetap wrapper** — logika di Livewire, bukan di view.

---

## Lampiran — Token tema (rujukan cepat)

| Token | Light `cr-cafe-resto` | Peran |
|---|---|---|
| `primary` | `#ff4f55` | aksi utama (merah) |
| `accent` | `#18bd85` | positif/konfirmasi (hijau) |
| `secondary` | `#62646b` | aksi/teks sekunder |
| `base-100/200/300` | `#ffffff` / `#f7f8fb` / `#edf0f4` | permukaan → panel → pemisah |
| `base-content` | `#111315` | teks utama |
| `info/success/warning/error` | `#2563eb`/`#16a34a`/`#d97706`/`#ef4444` | status |
| `--radius-box/field/selector` | `0.75 / 0.5 / 0.5 rem` | radius kartu/input/kontrol |
| `--border` / `--depth` | `0` / `0` | flat design — pisah via base-200/300 |

> Dark theme (`cr-cafe-resto-dark`): `base-100 #111315`, `base-200 #1b1d1f`, `base-300 #2d2d2d` — token sama, nilai gelap. Selalu uji kedua tema.
