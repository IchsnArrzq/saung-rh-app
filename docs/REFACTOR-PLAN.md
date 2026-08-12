# Refactor Plan — Menyelaraskan Kode dengan AGENTS.md & ARCHITECTURE.md

> Status: **DISETUJUI (scope penuh)** · Dibuat 2026-07-31
> Keputusan pemilik: **(1) Full scope — semua domain**, **(2) Status → hardcoded Enum**, **(3) Plan disimpan di sini sebagai roadmap yang dilacak**.
>
> Tujuan: memigrasikan aplikasi dari struktur *Service-layer per-role* menjadi arsitektur
> **Feature-First Domain + UseCase/Action/Repository** persis seperti yang dijanjikan
> `AGENTS.md` dan `ARCHITECTURE.md`, **tanpa melanggar** Core Principle #1 ("jangan over-engineer" / Merge Rule).

---

## 0. Baseline audit (kondisi 2026-07-31)

Diverifikasi langsung terhadap kode, bukan asumsi:

- ❌ **Tidak ada `app/Domains/`** — struktur per-layer/role: `app/Services/{Admin,Customer,Pos,...}`, `app/Livewire/`, `app/Repositories/`, `app/Models/`.
- ❌ **Tidak ada satu pun `UseCase` / `QueryUseCase` / `Action`** — Livewire memanggil Service langsung atau query model langsung.
- ❌ **Repository nyaris tak dipakai** — hanya `app/Repositories/Admin/DashboardRepository`. Service & Livewire query Eloquent langsung (mis. `OrderCartServiceImplement.php:40` `TableStatus::query()`, `Customers/Table.php:36` `Customer::query()`).
- ❌ **Interface 1:1** — 33 `*ServiceInterface` untuk 33 `*ServiceImplement`, di-bind manual di `app/Providers/ServiceBindingsProvider.php`. Ini persis "interface just-in-case" yang dilarang AGENTS §Service.
- ❌ **Status DB-driven** — model `TableStatus`, `MenuStatus`, `OrderStatusLog` + kolom string. AGENTS §State Machine mengharuskan **hardcoded Enum + Policy**.
- 🟡 **Routes flat** — `routes/{admin,customer,pos,kds,staff}.php` (bukan subfolder nested). Semangatnya sudah benar (bukan semua di `web.php`).
- ✅ **Sudah patuh:** Policies (`app/Policies/*` lengkap), Observer audit (`PaymentObserver`), Events (`OrderCreated`/`OrderUpdated`/dll), Livewire one-component-one-purpose (Table/Form/Detail terpisah), daisyUI design-system.

---

## 1. Peta Domain target (`app/Domains/`)

Domain final (gabungan AGENTS §Feature First + ARCHITECTURE §Core Domains):

| Domain | Models | Service aktual → pindah | Livewire terkait |
|---|---|---|---|
| **Menu** | Menu, MenuCategory, MenuStatus, MenuIngredient, Media | MenuService, MenuCategoryService, MediaService, MenuCatalogService, PublicHomeService | Admin/Menus\*, Admin/MenuCategories\*, Admin/MenuStatuses\*, Admin/MenuMedia, Admin/MenuIngredients, Frontend/MenuCatalog, Customer/MenuOrder, Landing/Home |
| **Order** | Order, OrderItem, OrderNote, OrderStatusLog | OrderService, OrderCartService, PublicCartService | Admin/Orders\*, Pos/Order/\*, Pos/OrderCard, Frontend/CartCheckout, Frontend/OrderStatus |
| **Reservation** | Reservation, ReservationItem | ReservationService, BookingService, CheckInService, ReservationDepositService, ReservationReleaseService | Admin/Reservations\*, Customer/BookingForm, Staff/Receptionist/BookingBoard |
| **Table** | Table, TableCategory, TableStatus, TableSession | TableService, TableCategoryService, TableStatusService, TableTurnoverService | Admin/Tables\*, Admin/TableCategories\*, Admin/TableStatuses\*, Admin/TableQrPage, Customer/TablePicker, Staff/Receptionist/TableMap |
| **Kitchen** | (Order/OrderItem via KDS) | (logika KDS di controller/Livewire) | Kds/Board, Staff/Waiter/\* |
| **Payment** | Payment, PaymentAccount | PaymentService, BillingService | Admin/Payments\*, Pos/TableBills, Admin/System/PaymentAccounts |
| **Inventory** | Ingredient, StockMovement, StockOpname(+Item), Purchase(+Item), Supplier, Sale(+Item) | InventoryService, StockOpnameService, PurchaseService, SaleService | Admin/{Ingredients,Stock,StockMovements,StockOpnames,Purchases,Suppliers,Sales}\* |
| **Customer** | Customer | Customer/DashboardService | Customer/Dashboard, Admin/Customers\* |
| **Employee** | Shift, Tip, ServiceLog | ShiftService, ManagerAnalyticsService | Staff/Manager/\*, Staff/Waiter/TipsServiceLog |
| **Reporting** | (read-only lintas domain) | ReportService, DashboardService (Admin) | Admin/Reports/ReportBoard, Admin/Dashboard |
| **User** | User, Role, Permission, Subscription | (LicenseService, AppSettings) | Admin/System/\*, RolePermission |
| _Lintas/Social_ | SongRequest, SpecialRequest, VisitorLog, Chat | Songs, SpecialRequests, Chat, ManagerAnalytics | Frontend/{SongRequest,SpecialRequestForm,TableChat}, Staff/\* |

Struktur folder tiap domain:
```
app/Domains/{Domain}/
├── UseCases/          # write flow — 1 flow bisnis = 1 UseCase (transaction boundary)
├── QueryUseCases/     # read flow — tipis, langsung Repository
├── Actions/           # HANYA bila reusable ≥2 UseCase (Merge Rule)
├── Services/          # kalkulasi/rule murni, TANPA query DB
├── Repositories/      # semua query Eloquent (Interface + impl)
├── DTO/               # payload >3 field atau butuh type-safety
├── Enums/             # status hardcoded (lihat §3)
├── Policies/          # transition rule status
├── Events/            # komunikasi lintas domain
└── Models/            # opsional: pindahkan Eloquent model ke domain (fase akhir)
```

---

> **Blueprint method-level per domain** (mapping tiap method service → layer target) ada di
> [REFACTOR-PLAN-DOMAINS.md](REFACTOR-PLAN-DOMAINS.md).

## 2. Pola transformasi per-layer (template berulang)

Contoh **domain Order**:
```
SEBELUM
  Livewire/Pos/Order/Create.php  →  OrderServiceImplement  (query Eloquent inline)

SESUDAH — write flow
  Livewire/Pos/Order/Create.php
    → CreateOrderUseCase              (DB::transaction)
        → CalculateOrderTotalAction   (reuse ≥2 → file sendiri)
        → OrderService                (rule/kalkulasi murni, NO query)
        → OrderRepository             (semua ::where / ::query di sini)

SESUDAH — read flow
  Livewire/Admin/Orders/Table.php
    → GetOrderListQueryUseCase → OrderRepository
```

**5 aturan pemindahan wajib:**
1. Semua `Model::query()/where()/find()` di Service **dan** Livewire → pindah ke **Repository**.
2. Setiap write-flow (create/update/settle/cancel/approve) → dibungkus **UseCase** + `DB::transaction`.
3. Setiap read-flow (Table/Index/Board/Report/Catalog) → **QueryUseCase** tipis → Repository.
4. Ekstrak **Action** hanya bila dipakai ≥2 UseCase. Kalau 1×, fold ke UseCase (Merge Rule).
5. **Hapus interface service 1-impl** (§4).

---

## 3. G5 — Migrasi status DB-driven → Enum (KEPUTUSAN: Enum)

### 3.1 Enum yang dibuat
| Enum | Values (`key` jadi backing value) | Sumber sekarang |
|---|---|---|
| `TableStatus` | `available`, `occupied`, `order_in`, `reserved`, `cleaning` | tabel `table_statuses` (TableStatusSeeder) |
| `MenuAvailability` | `available`, `unavailable`, `sold_out`, `seasonal` | tabel `menu_statuses` (MenuStatusSeeder) |
| `OrderStatus` | `draft`, `pending`, `confirmed`, `preparing`, `ready`, `served`, `completed`, `paid`, `cancelled` | string literal tersebar (35× `paid`, 35× `confirmed`, dst.) |
| `PaymentStatus` | `pending`, `paid`, `cancelled` (verifikasi di kode) | string literal |
| `ReservationStatus` | (derive dari ReservationSeeder/kode) | string literal |

### 3.2 Metadata UI harus ikut pindah
`TableStatus`/`MenuStatus` DB menyimpan `name` (label ID), `color` (token daisyUI), `sort_order`, `is_active`, `is_default`. Saat jadi Enum, metadata ini jadi **method Enum**:
```php
enum TableStatus: string {
    case Available = 'available';
    case Occupied  = 'occupied';
    case OrderIn   = 'order_in';
    case Reserved  = 'reserved';
    case Cleaning  = 'cleaning';

    public function label(): string  { return match($this){ self::Available=>'Tersedia', ... }; }
    public function color(): string  { return match($this){ self::Available=>'success', ... }; }
    public function sortOrder(): int { ... }
}
```
Transition rule (mis. `order_in → cleaning` saat lunas) → **Policy**, dipanggil dari UseCase (AGENTS §State Machine).

### 3.3 Langkah migrasi status (hati-hati — menyentuh data)
1. Buat semua Enum + method label/color di `app/Domains/{X}/Enums/`.
2. Ganti seluruh string literal & referensi model status di kode → Enum case.
3. Model cast: `protected $casts = ['status' => TableStatus::class]`.
4. **Migrasi data**: kolom FK `table_status_id`/`menu_status_id` → kolom string `status` berisi `key`. Tulis migration konversi (baca key lama → tulis ke kolom baru). Hapus tabel `table_statuses`/`menu_statuses` + seeder-nya **setelah** verifikasi.
5. ⚠️ **Risiko**: fitur admin "kelola status" (`Admin/TableStatuses/*`, `Admin/MenuStatuses/*` — CRUD status via UI) menjadi **usang** karena status kini hardcoded. Konfirmasi fitur ini memang boleh dihapus sebelum eksekusi (kemungkinan besar ya — itu justru inti keputusan Enum).
6. Blade yang render badge status kini pakai `$order->status->color()` / `->label()` bukan relasi.

---

## 4. G4 — Hapus interface service 1-impl

1. Audit 33 pasangan: adakah yang **benar-benar** punya (atau realistis akan punya) ≥2 impl? Kandidat: `PaymentService` (Cash/Qris/Transfer), `NotificationService` (jika ada). Sisanya = 1 impl selamanya.
2. Untuk 1-impl: rename `XxxServiceImplement` → `XxxService`, hapus `XxxServiceInterface`, inject kelas konkret.
3. Kosongkan entri terkait di `app/Providers/ServiceBindingsProvider.php` (sisakan hanya binding yang genuinely poly).
4. `Repository` interface **tetap dipertahankan** (AGENTS §Repository — swap datasource/mock di test itu kebutuhan nyata).

---

## 5. G6 — Reorganisasi routes

`routes/admin.php` → `routes/admin/{dashboard,tables,menus,reservations,orders,payments,reports,users,inventory}.php`.
`routes/customer.php` → `routes/customer/{home,menu,reservations}.php`.
`web.php` hanya `require` file-file route. `pos.php`/`kds.php`/`staff.php` masuk ke grup admin sesuai portal.

---

## 6. Fase eksekusi (urut)

### Fase A — Quick wins (risiko rendah, ~1–2 hari) — ✅ SELESAI (2026-08-03)
- [x] A1. G4 — hapus interface service 1-impl, rename `*Implement`→`*Service`, rapikan `ServiceBindingsProvider`.
      Hasil: 33 pasangan Interface/Implement dihapus/direname; `ServiceBindingsProvider.php` dihapus total (dropped dari `bootstrap/providers.php`); semua 30 consumer file (Livewire/Controller/Observer/Console/Test) diupdate ke concrete class. Bonus: ketemu & fix bug pre-existing tak terkait — class `AppSettingsManager.php` sebelumnya bernama `AppSettingsInterfaceManager` (typo lama), menyebabkan Livewire tag `<livewire:admin.system.app-settings-manager />` gagal resolve.
      Verifikasi: `composer dump-autoload -o` bersih (8776 class), `php -l` 64 file clean, container resolve OK lintas 5 domain via tinker (Admin/Pos/Customer/Settings/Livewire).
- [x] A2. G6 — pecah routes ke subfolder; `web.php` jadi loader.
      Hasil: `routes/admin.php` (150 baris flat) → 11 file modul di `routes/admin/{dashboard,tables,menus,orders,payments,reservations,reports,users,system,inventory,customers}.php` + `admin.php` jadi loader (middleware group + require). `routes/customer.php` → 3 file di `routes/customer/{home,menu,reservations}.php` + loader. `web.php` sudah loader sejak awal (tak diubah). `pos.php`/`kds.php`/`staff.php`/`landing.php` DIBIARKAN flat (deviasi sengaja dari teks doc — sudah portal-scoped & kecil, tak perlu dipecah lagi).
- [x] A3. Verifikasi: `route:list` (150 routes sebelum & sesudah split — tak ada yang hilang/duplikat), `view:cache` bersih, `npm run build` sukses (891kB chunk warning pre-existing, tak terkait).

> ### ⚠️ REGRESI A1 — ditemukan & diperbaiki saat mulai Fase B (2026-08-03)
> **Apa yang terjadi:** 4 interface yang dihapus ternyata mendeklarasikan `public const`, dan class
> mewarisinya lewat `implements`. Begitu `implements` dihapus, konstanta itu lenyap → **fatal error saat runtime**.
>
> | Konstanta | Dulu di interface | Dipakai di |
> |---|---|---|
> | `STATUS_OPTIONS` | `OrderServiceInterface` | `OrderService::statusOptions()`, `validate()` |
> | `METHOD_OPTIONS`, `STATUS_OPTIONS` | `PaymentServiceInterface` | `PaymentService` (4×), `BillingService:96`, `TableBills:66` |
> | `STATUS_OPTIONS` | `ReservationServiceInterface` | `ReservationService` (2×) |
> | `ORDERABLE_STATUSES` | `OrderCartServiceInterface` | `OrderCartService:128` |
>
> **Perbaikan:** konstanta dipindahkan ke class konkret sebagai `public const` (visibility sama seperti asalnya —
> wajib `public` karena `BillingService`/`TableBills` mengaksesnya dari luar). Total 10 titik pemakaian pulih.
>
> **PELAJARAN PENTING UNTUK FASE C:** `php -l`, `route:list`, `view:cache`, dan `npm run build`
> **SEMUANYA LOLOS** padahal ada fatal error — karena konstanta di-resolve saat *runtime*, bukan saat parse.
> Verifikasi Fase C **wajib** menyertakan eksekusi kode nyata (tinker invoke method / render Livewire),
> bukan hanya lint+build. Sebelum menghapus interface apa pun: `git show HEAD:<file> | grep const`.
> (Catatan: `OrderStatus`/`PaymentStatus`/`ReservationStatus` Enum di Fase B–C akan menggantikan
> konstanta-konstanta ini secara permanen — termasuk duplikatnya di `Livewire/Admin/{Orders,Payments,Reservations}/Form.php`.)

### Fase B — Pilot domain **Order** end-to-end — ✅ INTI SELESAI (2026-08-03)

> **Temuan besar saat mulai:** `app/Services/Admin/OrderService.php` ternyata **100% dead code**
> (nol referensi di seluruh proyek). Logika order yang benar-benar hidup tersebar di Livewire
> (`Admin/Orders/Form`, `Kds/Board`) + `OrderCartService` + `BillingService`. Jadi Fase B bukan
> "migrasi OrderService" seperti asumsi plan, melainkan **mengangkat logika dari Livewire**.
> `generateOrderNumber()` ditemukan **terduplikasi di 6 tempat** (satu di antaranya sudah drift
> ke algoritma berbeda) → jadi `GenerateOrderNumberAction`.
>
> **Yang dibangun (19 file di `app/Domains/Order/`):** Enum `OrderStatus` (+label/color/transisi),
> `OrderRepository`(+Interface, di-bind di `AppServiceProvider`), Actions
> (`CalculateOrderTotalAction`, `GenerateOrderNumberAction`), DTO (`OrderTotals`, `CreateOrderData`,
> `UpdateOrderData`), UseCases (`CreateOrder`, `UpdateOrder`, `SettleBill`, `ChangeOrderStatus`,
> `AdvanceKitchenTicket`, `MarkOrderItemReady`, `VoidOrderItem`), QueryUseCases (`GetOrderList`,
> `GetKitchenQueue`, `GetOpenBills`), Service (`OrderBillingService`).
>
> **Consumer yang sudah dialihkan:** `Pos/TableBills`, `Kds/Board`, `Admin/Orders/Table` (render),
> `Admin/Orders/Form` (save). **Dihapus:** `Services/Admin/OrderService` (dead) & `Services/Pos/BillingService`
> (digantikan domain) — plus duplikat `STATUS_OPTIONS`/`normalizeItems`/`generateOrderNumber` di Form.
>
> **Verifikasi RUNTIME (bukan cuma lint):** 17 class resolve dari container; transisi Enum benar
> (`confirmed→ready` boleh, `paid→cancelled` & `ready→draft` ditolak); read flow nyata (21 order,
> KDS 3 bucket, 10 tagihan outstanding Rp1.539.930); write flow di transaksi ter-rollback
> (create 52.500 → update 102.500 → status guard menolak `preparing→draft` → kitchen cascade
> order+item ke `ready`), DB kembali bersih di 21 order. `view:cache` + `route:list` (150) OK.
>
> **Sengaja BELUM dilakukan saat itu (catat untuk Fase C/D):**
> - Model `Order` **tidak** di-cast ke Enum (`'status' => OrderStatus::class`) — supaya Blade yang
>   membandingkan string tidak pecah. Domain memakai `OrderStatus::from($order->status)`. Cast menyusul
>   saat Blade ikut dirapikan.
> - `SettleBillUseCase` masih memanggil `TableTurnoverService` lintas domain & memakai
>   `PaymentService::METHOD_OPTIONS` → keduanya sudah ditandai `@todo` (Fase D / C3).
> - Consumer Order lain belum dialihkan: `Pos/OrderCard`, `Frontend/CartCheckout`, `Frontend/OrderStatus`,
>   sisa 3 query di `Admin/Orders/Table`. Query Order di domain lain (Reporting/Dashboard/Reservation/
>   Payment/Manager/Staff/Export) memang jatah Fase C.

#### Checklist asli
- [x] B1. Buat skeleton `app/Domains/Order/{UseCases,QueryUseCases,Actions,Services,Repositories,DTO,Enums,Policies,Events}`.
- [x] B2. `OrderRepository` (+Interface) — pindahkan SEMUA query Order dari service & Livewire.
- [x] B3. Write UseCases: `CreateOrderUseCase`, `UpdateOrderUseCase`, `CancelOrderUseCase`, `SettleBillUseCase`.
      *Deviasi:* pembatalan tidak jadi `CancelOrderUseCase` sendiri — `cancelled` hanyalah satu transisi,
      jadi ditangani `ChangeOrderStatusUseCase` (Merge Rule).
- [x] B4. `CalculateOrderTotalAction` (contoh reuse eksplisit di AGENTS).
- [x] B5. QueryUseCases: `GetOrderListQueryUseCase`, `GetKitchenQueueQueryUseCase`.
- [x] B6. `OrderStatus` Enum + transition rules. *Deviasi:* aturan transisi tinggal di dalam Enum
      (`canTransitionTo`), bukan kelas `OrderStatusPolicy` terpisah — satu-satunya konsumennya adalah
      Enum itu sendiri, jadi kelas terpisah cuma menambah indireksi.
- [x] B7. Livewire Order/POS panggil UseCase. **Test runtime end-to-end**.
- [ ] B8. **Review pola bersama pemilik** → jadikan template Fase C.
      *(Praktis sudah terlampaui: C1–C10 semuanya dikerjakan di atas pola ini dan lolos verifikasi E3.
      Centangnya sengaja dibiarkan kosong karena review-nya keputusan pemilik, bukan hasil kerja kode.)*

#### ✅ Utang carry-over Fase B — LUNAS (2026-08-08)

Tiga consumer terakhir + cast Enum dibereskan; domain Order kini tidak punya sisa query Eloquent
di Livewire.

**Consumer yang dialihkan**
- `Frontend/CartCheckout` → `PlaceGuestOrderUseCase` (+`PlaceGuestOrderData`). Daftar meja lewat
  `TableRepository::orderable()`.
- `Pos/OrderCard` → `PlacePosOrderUseCase` (+`PlacePosOrderData`); menu lewat
  `GetMenuCatalogQueryUseCase::availableFiltered()`, meja lewat `TableRepository::allOrdered()`.
- `Frontend/OrderStatus` → `GetTableOrderTrackingQueryUseCase` (posisi antrian dihitung dari
  `OrderRepository::kitchenQueueIds()`, urutan identik dengan KDS).
- `Admin/Orders/Table` — 3 query terakhir habis: `delete` → `DeleteOrderUseCase`,
  `showDetail` → `GetOrderListQueryUseCase::detail()` + `OrderBillingService`,
  `createPayment` → `SettleBillUseCase`.

**Action baru (Merge Rule terpenuhi, 2 konsumen):** `Payment\Actions\RecordPaymentAction` — satu-satunya
tempat yang menulis baris payment lunas, dipakai `SettleBillUseCase` dan `PlacePosOrderUseCase`.
Kolom `paid_at`/`status` tak mungkin lagi melenceng antar-jalur, dan itu persis yang dibaca
`PaymentObserver` untuk memotong stok.

**Cast Enum:** `Order::$casts['status'] = OrderStatus::class`. Semua pembaca ikut dirapikan —
4 `OrderStatus::from($order->status)` di UseCase jadi `$order->status`, KDS blade tak perlu
`::from()` lagi, `dashboard.blade` & `orders/table.blade` pakai `<x-status-badge>`, dan
`table-bills.blade` yang tadinya me-render badge `neutral` + string mentah kini dapat label/warna
Enum **tanpa perubahan view** (karena `OrderBillingService::summarize()` sekarang mengoper enum).

**Perbaikan sekalian (bug nyata, bukan kosmetik):**
- ⚠️ `Pos/OrderCard::showMenuDetail()` masih eager-load relasi `status:id,name,key,color` yang
  **dihapus di C2** → `RelationNotFoundException` setiap kali tombol detail menu diklik. Sudah
  dikonfirmasi lewat runtime (bukan dugaan) lalu diperbaiki ke `MenuAvailability`.
- `CreateOrderUseCase` kini `DB::afterCommit()` saat dispatch `OrderCreated` — tanpa itu, UseCase
  pembungkus yang punya transaksi sendiri akan menyiarkan order ke KDS sebelum commit.
- `Admin/Orders/Table::createPayment` kini memakai jalur yang sama dengan kasir, sehingga order yang
  dilunasi dari daftar admin **ikut membebaskan mejanya** (dulu meja tetap terkunci).

**Verifikasi RUNTIME:** 33 assert lewat tinker (ter-rollback) — cast enum; 7 kelas resolve; POS
bayar-di-muka (order confirmed + payment `paid` qris + meja→`order_in`) dan **POS tidak menimpa meja
`reserved`**; guest QR `occupied`→`order_in` dengan nama default "Tamu Meja T-02"; tracking
(queueTotal 3, posisi 3); settle → `BILL-*`, order `paid`, meja `cleaning`, settle kedua ditolak;
delete. **8 komponen Livewire di-render** + 3 jalur aksi yang tidak jalan saat mount dipanggil
langsung (`TableMap::selectTable`, `Orders\Table::showDetail`, `OrderCard::showMenuDetail`).
Pemeriksa enum-tanpa-import bersih. `route:list` 141 tetap, `view:cache` + `npm run build` sukses.

**Keputusan pemilik (2026-08-08):** `DeleteOrderUseCase` **menolak** order yang membawa uang —
punya baris payment, atau berstatus `Paid` tanpa baris payment (anomali yang masih bisa dibuat form
admin). Alasannya: tiket lunas adalah catatan akuntansi yang direkonsiliasi laporan, daftar tagihan
kasir, dan ledger stok; menghapusnya meninggalkan payment yang menunjuk ke ketiadaan. Jalur yang
benar adalah membatalkan (`ChangeOrderStatusUseCase`). Diverifikasi runtime: order polos terhapus,
order berpayment & order `Paid` ditolak dengan pesan Indonesia, order tak ditemukan ditolak.

**Dead view dihapus (2026-08-08):** `resources/views/admin/{orders,payments,reservations}/_form.blade.php`
— partial form POST pra-Livewire (punya `<script>` sendiri, class `border-stone-200`, input
`name="items[..]"`), nol referensi, sudah digantikan komponen Livewire masing-masing. Setelah dihapus
`view:cache` bersih dan byte-count render 8 komponen **identik** dengan sebelumnya, jadi memang tak
ada yang memakainya.

### Fase C — Replikasi domain sisa (~3–5 minggu)
Urutan by dependency (daun→akar) agar Events antar-domain rapi:
- [x] C1. **Table** (+TableStatus Enum) — ✅ SELESAI (2026-08-03)
      **Pre-flight checklist berbuah lagi:** `TableService`, `TableStatusService`, `TableCategoryService`
      ternyata **dead code** (0 consumer) — pola sama seperti `OrderService` di Fase B. Dihapus, tidak dimigrasi.
      **Kunci yang membuat migrasi aman:** `Table::getStatusAttribute()` ternyata SUDAH mengekspos
      `$table->status` sebagai string dari relasi, jadi semua *pembaca* sudah string-based; hanya ~20 *penulis*
      (`table_status_id`) yang perlu diubah.
      **Migrasi data (2 langkah, reversible):** (1) `add_status_to_tables` — tambah kolom string + backfill
      via join dari `table_statuses.key`; FK lama sengaja dipertahankan dulu. Diverifikasi: 21/21 baris,
      0 NULL, **0 mismatch** vs FK. (2) `drop_table_statuses_table` — buang FK, kolom, dan tabelnya;
      `down()` merekonstruksi tabel + 5 baris seed + relink dari kolom `status`.
      **Dihapus:** CRUD status meja (controller, 2 Livewire, blade, route, nav ×2, seeder, model,
      policy) + `TableStatusService`/`TableService`/`TableCategoryService` (dead) + `CheckInService` &
      `TableTurnoverService` (digantikan UseCase) + `admin/tables/_form.blade.php` (dead view).
      **Dibangun:** `app/Domains/Table/` — Enum (label/color/sortOrder/default/isOrderable/isFree),
      `TableRepository`(+Interface), `ChangeTableStatusUseCase`, `CheckInTableUseCase`,
      `ReleaseTableUseCase`, `GetTableListQueryUseCase`. `SettleBillUseCase` kini memanggil
      `ReleaseTableUseCase` (bukan service lintas domain).
      **Bug pre-existing yang ikut ketemu & diperbaiki:** `table-map.blade.php` menyuntikkan token daisyUI
      (`success`, `error`) langsung ke `style="border-color: …"` — **CSS tidak valid, warnanya tidak pernah
      muncul**. Diganti class token penuh (`border-success bg-success/10`) sesuai DAISYUI-BLUEPRINT.
      **Verifikasi RUNTIME:** skema benar (kolom baru ada, FK & tabel lama hilang); data utuh 21 meja dengan
      distribusi identik; 14 class resolve; read flows (paginate/all/selectable/search/countByStatus/
      dashboard/tableSelectionData) benar; write flows di transaksi ter-rollback (ChangeStatus by-model &
      by-id, CheckIn available→occupied + sesi aktif, Release →cleaning + sesi tertutup, resolver
      orderable vs free); **6 komponen Livewire benar-benar di-render** (HTML keluar, label Indonesia &
      token daisyUI muncul). `view:cache` OK, `route:list` 150→**144** (6 rute CRUD status hilang, sesuai),
      `npm run build` sukses.
      ⚠️ **Deviasi disengaja:** tidak ada transition guard pada `TableStatus` — status meja memang bebas
      berpindah (staf mengoreksi manual); menambah aturan justru akan mengubah perilaku, bukan mengamankan.
      Toggle "Tampilkan status nonaktif" di status board hilang bersama kolom `is_active`.
- [x] C2. **Menu** (+MenuAvailability Enum) — ✅ SELESAI (2026-08-03)
      **Pre-flight (3× berturut-turut berbuah):** `MenuService`, `MenuCategoryService`, `MenuCatalogService`
      = **dead code** (0 consumer). Dihapus.
      **Beda penting dari C1:** `Menu::status()` adalah **relasi** (bukan accessor string seperti Table),
      jadi `$menu->status` dulu mengembalikan *model*. Untungnya cakupannya kecil — hanya 2 titik nyata
      (`Pos/OrderCard` status_name/color) + `getIsAvailableAttribute` + `scopeAvailable`/`scopeUnavailable`,
      dan **nol pemakaian di Blade**.
      **Migrasi 2 langkah reversible:** `add_status_to_menus` (backfill: 11 available + 1 unavailable,
      0 NULL, **0 mismatch**) lalu `drop_menu_statuses_table` (`down()` rekonstruksi + relink).
      **Dibangun:** `app/Domains/Menu/` — Enum `MenuAvailability` (label/color/sortOrder/default/
      isSellable/**fromToggle**), `MenuRepository`(+Interface), `GetMenuCatalogQueryUseCase`.
      `scopeAvailable`/`scopeUnavailable` kini query kolom langsung (bukan `whereHas`) — lebih cepat.
      **Dihapus:** CRUD status menu (controller, 2 Livewire, 5 blade, 3 route, nav ×2, seeder, model, policy)
      + 3 service dead.
      **Catatan produk:** form admin hanya pernah menghasilkan `available`/`unavailable` (toggle boolean);
      `sold_out` & `seasonal` ter-seed tapi **tak terjangkau UI dan tak dipakai baris mana pun**. Keduanya
      dipertahankan di Enum (`fromToggle` memetakan toggle lama) — kini justru bisa dipakai bila dapur mau.
      **Verifikasi RUNTIME:** skema benar; data utuh 12 menu; scope memartisi tepat (11+1=12); accessor
      `is_available` benar dua arah; read flows (catalog/featured/adminPaginate/PublicHome/OrderCart) benar;
      write ter-rollback (set `sold_out` → `is_available=false` & masuk `scopeUnavailable`, toggle balik);
      **7 komponen Livewire di-render** (Menus Table/Form/MenuCard, OrderCard, MenuCatalog, Landing Home,
      MenuIngredients Form). `route:list` 144→**141**, `view:cache` + `npm run build` sukses.
- [x] C3. **Payment** (+PaymentStatus & PaymentMethod Enum) — ✅ SELESAI (2026-08-04)
      **Pre-flight:** method `PaymentService` **dead** (yang dipakai cuma konstantanya) → dihapus.
      **Tidak ada migrasi data:** `payments.status`/`method` sejak awal kolom string biasa, tak pernah
      ada tabel lookup — jadi C3 jauh lebih ringan dari C1/C2.
      **Dibangun:** `app/Domains/Payment/` — `PaymentStatus` (pending/paid/failed/refunded, +`isSettled()`),
      `PaymentMethod` (6 metode, +`label()`), `PaymentRepository`(+Interface), `GetPaymentListQueryUseCase`.
      **Duplikasi yang dihapus:** konstanta di `PaymentService` **dan** salinannya di
      `Livewire/Admin/Payments/Form`, **plus** peta label metode yang di-hardcode di
      `livewire/pos/table-bills.blade.php`. Form admin dulu menampilkan label mentah
      (`str_replace('_',' ')` / `ucfirst`) — kini label Indonesia dari Enum.
      **Consumer dialihkan:** `SettleBillUseCase` (todo C3 ditutup), `TableBills`+blade, `Payments/Form`+blade,
      `Payments/Table`→QueryUseCase, `PaymentObserver`, `ReservationDepositService`, `Admin/Orders/Table`,
      `Pos/OrderCard`, `OrderBillingService`, `OrderRepository`.
      ⚠️ **BUG LAMA DITEMUKAN & DIPERBAIKI (penting):** `stock_movements.reference_id` dibuat lewat
      `$table->nullableMorphs('reference')` → kolom **bigint**, padahal semua model referensi
      (Payment/Purchase/Sale/StockOpname) memakai **UUID**. Akibatnya **pemotongan stok otomatis akan
      selalu gagal** (`invalid input syntax for type bigint`) begitu ada resep menu. Selama ini tak
      ketahuan karena DB belum punya bahan/resep, sehingga loop `deductFromPayment` tak pernah jalan.
      Diperbaiki migrasi `fix_stock_movements_reference_id_type` (tabel 0 baris → aman; indeks lama masih
      bernama `stock_opnames_*` sisa rename, di-drop eksplisit).
      **Verifikasi RUNTIME:** enum + label + `isSettled`; repository (18 payment, search, sumSettled=202.020);
      openBills lewat OrderBillingService; **rantai stok end-to-end di transaksi ter-rollback** — bahan
      stok 100 + resep 2/porsi + order 3 porsi → payment `paid` → **stok 94, 1 stock_movement `out` −6,
      ref_type=Payment**; payment `pending` **tidak** memotong; `pending→paid` (hook `updated`) memotong.
      5 komponen Livewire di-render (label Indonesia muncul). `route:list` 141 tetap, `view:cache` +
      `npm run build` sukses.
- [x] C4. **Reservation** (+ReservationStatus Enum) — ✅ SELESAI (2026-08-04)
      **Pre-flight (4× berturut-turut):** `ReservationService` **dead code** (0 consumer) → dihapus.
      **Tidak ada migrasi data**, tapi ada catatan penting: kolom dibuat dengan `$table->enum('status', [...])`,
      yang di Postgres jadi **CHECK constraint**. Jadi Enum PHP **wajib** sinkron dengan constraint DB —
      menambah case tanpa migrasi akan gagal saat write. Sudah diverifikasi cocok persis (6 nilai).
      **Dibangun:** `app/Domains/Reservation/` — `ReservationStatus` (label/color/`isHolding()`/
      `holdingValues()`/`isFinal()`), `ReservationRepository`(+Interface), `GetReservationListQueryUseCase`
      (mode `forAdmin` & `forBoard` — board diurut berdasarkan urgensi pending→confirmed→sisanya).
      **Konstanta salah tempat dibereskan:** `Reservation::RESERVED_STATUS_KEY = 'reserved'` sebenarnya
      **status MEJA**, bukan status reservasi — diganti `TableStatus::Reserved`. `HOLDING_STATUSES`
      → `ReservationStatus::holdingValues()`. `BookingService::STATUS_PENDING` dan duplikat
      `STATUS_OPTIONS` di `Livewire/Admin/Reservations/Form` juga dihapus.
      `BookingBoard::ACTIONS` **dipertahankan** (label kata kerja tombol "Konfirmasi"/"Check-in" memang
      beda konsep dari label status "Dikonfirmasi"/"Sudah Duduk") tapi kuncinya kini dari Enum.
      Form admin dulu menampilkan `str_replace('_',' ')` → kini label Indonesia.
      **Verifikasi RUNTIME:** Enum dibandingkan langsung dengan CHECK constraint di `pg_constraint`;
      read (forAdmin 22, forBoard filter pending 7, urutan board benar, countsByStatus, countToday);
      **siklus kunci-meja end-to-end ter-rollback**: `lockTable`→meja `reserved`; `releaseTable` saat
      reservasi lain masih `seated` → meja **tetap** reserved; setelah yang lain `completed` → meja
      `available`. `releaseExpired()` (job) jalan: 7 expired hold + 4 no-show.
      4 komponen Livewire di-render. `route:list` 141 tetap, `view:cache` + `npm run build` sukses.
      *Catatan proses:* percobaan pertama tampak gagal (meja tak jadi `available`) — ternyata meja uji
      sudah dipegang reservasi seed berstatus `seated`, jadi penolakannya benar. Diulang dengan meja
      tanpa reservasi dan hasilnya sesuai.
- [x] C5. **Kitchen** (KDS) — ✅ SELESAI (2026-08-04)
      ⚠️ **Keputusan arsitektur: Kitchen TIDAK dijadikan domain terpisah.** Alasannya konkret —
      tidak ada model/tabel `KitchenTicket`; KDS sepenuhnya membaca & menulis agregat **Order**
      (order + item-nya). UseCase-nya (`AdvanceKitchenTicket`, `MarkOrderItemReady`, `VoidOrderItem`,
      `GetKitchenQueue`) sudah dibuat di Fase B dan **benar** berada di domain Order — memindahkannya
      keluar hanya akan menciptakan kopling lintas-domain tanpa manfaat. Membuat `app/Domains/Kitchen/`
      kosong justru melanggar Core Principle #1 (abstraksi harus menyelesaikan masalah yang ADA sekarang).
      Kitchen = *view* atas domain Order, bukan domain sendiri.
      **Yang benar-benar dikerjakan (level view + konsumen):**
      - `kds/board.blade.php`: literal status mentah (`=== 'confirmed'`, `in_array([...])`) → Enum
        (`isKitchenBound()`, `label()`); status yang tadinya tampil mentah ("confirmed") kini
        label Indonesia; class warna dipetakan penuh per cabang (aturan scanner Tailwind).
      - `OrderStatus::isInService()` + `inServiceValues()` — set `['confirmed','preparing','ready','served']`
        ternyata **diduplikasi 3×** identik (DashboardRepository, TableMap, TipsServiceLog) + 1 varian
        di SidebarNavigation. Semua kini memakai satu sumber.
      - `KdsController`: buang import `Request` yang tak terpakai, tambah return type.
      ⚠️ **BUG YANG SAYA PERKENALKAN DI C1, ditemukan & diperbaiki di sini:**
      `Livewire/Staff/Receptionist/TableMap.php` memakai `TableStatus::tryFrom(...)` **tanpa import** →
      PHP mencari `App\Livewire\Staff\Receptionist\TableStatus` → fatal "Class not found". Lolos dari
      `php -l` (nama class tak di-resolve saat parse) DAN dari uji render C1 (baris itu ada di
      `selectTable()`, sebuah action yang tidak dipanggil saat mount). Diperbaiki, lalu diverifikasi
      dengan **memanggil `selectTable()` sungguhan** → mengembalikan label "Terisi" dari key `occupied`.
      **Alat baru:** skrip pemeriksa `scratchpad/check_enum_imports.php` — memindai seluruh `app/`
      untuk enum yang dipakai tanpa import/FQCN. Kini **bersih**. Jalankan ini di tiap fase berikutnya;
      grep bash tidak andal untuk pola berisi backslash.
      **Verifikasi RUNTIME:** enum baru; KDS render + label Indonesia muncul & status mentah hilang;
      `selectTable()` (jalur yang tadinya fatal); DashboardRepository activeOrders=11; TipsServiceLog
      render. `route:list` 141, `view:cache` + `npm run build` sukses.
- [x] C6. **Inventory** (Ingredient/Stock/Purchase/Sale/StockOpname/Supplier) — ✅ SELESAI (2026-08-07)
      **Pre-flight — rangkaian dead code BERAKHIR:** untuk pertama kalinya keempat service (`InventoryService`,
      `PurchaseService`, `SaleService`, `StockOpnameService`) semuanya **hidup dan dipakai**. Jadi ini
      migrasi sungguhan, bukan penghapusan.
      **Tidak ada migrasi data:** seluruh tabel inventory **kosong** (0 baris) — subsistem ini belum
      pernah dipakai dengan data nyata. Status `draft/posted` sudah kolom string + CHECK constraint.
      **Dibangun (`app/Domains/Inventory/`):**
      - Enum `DocumentStatus` (draft/posted) — **satu enum dipakai bertiga** (Purchase/Sale/StockOpname);
        ketiganya dibuat dengan `$table->enum('status',['draft','posted'])` yang identik dan berperilaku
        sama, jadi tiga salinan dua nilai tak ada gunanya. Enum `StockMovementType` (in/out/adjustment).
      - **Action reusable — inti fase ini:** `RecordStockMovementAction` (dipakai ketiganya; satu-satunya
        tempat yang menulis ledger + menggeser saldo, sehingga keduanya tak mungkin melenceng),
        `AddStockAction`, `ReduceStockAction`, `AdjustStockAction`.
      - `IngredientRepository`(+Interface), UseCases `PostPurchase`, `PostSale`, `PostStockOpname`,
        `CreateStockOpnameDraft`, `DeductStockForPayment`.
      **Dialihkan:** `PaymentObserver` → `DeductStockForPaymentUseCase`; 3 Livewire Form → UseCase;
      `isPosted()` di model Purchase/Sale/StockOpname → `DocumentStatus`. **4 service lama dihapus**
      (total 11 service dihapus sejak Fase A).
      **Verifikasi RUNTIME — keempat jalur stok dijalankan sungguhan (ter-rollback):**
      bahan stok 100 → (a) PostPurchase +25 → **125**, status `posted`, `cost_per_unit` ikut ter-update,
      dan **post ulang tidak menggandakan** (idempoten); (b) PostSale −10 → **115**; (c) PostStockOpname
      hitung fisik 90 → **90**, selisih −25 tercatat di baris; (d) Payment `paid` dengan resep 3/porsi ×
      2 porsi → **84**. Ledger: `in/out/adjustment/out` dengan `reference_type`
      Purchase/Sale/StockOpname/Payment — sekaligus **membuktikan perbaikan `reference_id` UUID dari C3
      bekerja untuk SEMUA tipe dokumen**, bukan cuma Payment. 11 komponen Livewire render bersih.
      *Catatan proses:* render pertama menghasilkan byte-count kecil (form 1.5KB) karena view cache stale
      setelah komponen Blade baru ditambahkan; setelah `view:clear` angkanya normal (9.8KB). Sinyal kotor
      jangan diterima apa adanya — ulangi dengan cache bersih.
- [x] C7. **Customer** — ✅ SELESAI (2026-08-08)
      **Pre-flight:** untuk pertama kalinya *tak ada* dead code — ketiga service (`Customer\DashboardService`,
      `OrderCartService`, `BookingService`) hidup dan dipakai. (Catatan proses: grep pertama sempat
      melaporkan `DashboardService` mati karena pola `Customer\\DashboardService` yang salah escape;
      ternyata dipakai `Livewire\Customer\Dashboard`. Verifikasi ulang sebelum menghapus apa pun.)
      **Temuan pemetaan:** `Customer\DashboardService` sebenarnya **query Reservation murni** — nol model
      domain Customer. Jadi `GetCustomerDashboardQueryUseCase` tinggal di domain Customer (portal pelanggan
      = view atas domain lain) tetapi membaca lewat `ReservationRepository`, bukan query sendiri.
      `OrderCartService` ternyata **satu kelas untuk tiga domain**: cart sesi (Customer), katalog menu
      (Menu), resolusi meja (Table), dan pembuatan order (Order) — dipecah sesuai batasnya.
      **Dibangun (`app/Domains/Customer/`):** `CustomerRepository`(+Interface), `CustomerData` DTO
      (normalisasi string kosong→null jadi satu tempat, dulu 6 baris `?: null` di form),
      `Create/Update/DeleteCustomerUseCase`, `GetCustomerListQueryUseCase`,
      `GetCustomerDashboardQueryUseCase`, dan `Services/CustomerCart` — cart sesi **per meja**, tanpa
      satu pun query DB (Service sejati per AGENTS §Service). Beda dari `Support\RestaurantCart` yang
      melayani tamu QR anonim (satu cart per sesi, tanpa kunci meja).
      **Dibangun di domain lain:** `Order\PlaceCustomerOrderUseCase`(+DTO) — sibling ketiga setelah
      POS & guest; `Reservation\PlaceReservationUseCase`(+DTO) — potongan C4 yang tertinggal karena
      file-nya duduk di `Services/Customer`; `Table\QueryUseCases\FindTableQueryUseCase`
      (`byId`/`orderable`/`free`) dan `Table\Actions\ClaimTableForOrderAction`.
      **Action baru menghapus duplikasi nyata:** aturan "order masuk → meja `available`|`occupied`
      jadi `order_in`" tadinya ditulis ulang di 3 tempat. Kini satu Action, dipakai `PlaceGuestOrder`
      dan `PlaceCustomerOrder`. POS **sengaja tidak** memakainya (kasir hanya mengklaim meja yang
      benar-benar kosong) dan alasannya ditulis di docblock, bukan disembunyikan di balik flag.
      **Repo yang ditambah:** Reservation (`upcomingForUser`, `historyForUser`, `hasOverlappingHold`,
      `createWithItems`), Menu (`findWithCategory`, `findManyKeyedById`,
      `activeCategoriesWithAvailableCounts`), Table (`search()` kini juga mencakup kapasitas/status/
      kategori — sebelumnya `groupedByStatus` akan menyempitkan pencarian meja pelanggan).
      **Dihapus:** 3 service (total **14** sejak Fase A). `Pos/OrderCard` kehilangan query
      `MenuCategory` terakhirnya.
      ⚠️ **BUG C2 KEDUA DITEMUKAN:** `Customer/MenuOrder::showMenuDetail()` juga masih eager-load relasi
      `status:id,name,key,color` yang dihapus di C2 — fatal setiap kali detail menu diklik, kembaran
      persis dari bug `Pos/OrderCard` yang ditambal di utang Fase B. Dua-duanya kini lewat
      `GetMenuCatalogQueryUseCase::find()`.
      ⚠️ **5 BLADE RUSAK TOTAL DITEMUKAN (pre-existing, bukan dari C7):** `@disabled(...)` **di dalam
      tag `<x-...>`** membuat compiler Blade kehilangan pembuka `if ($component->shouldRender())`
      tetapi tetap menulis `endif` penutupnya → `syntax error, unexpected token "endif"`. Halaman
      langsung 500, bukan salah render: `customer/booking-form`, `admin/reservations/form`,
      `frontend/song-request`, `staff/waiter/table-status-updater`, `menus/partials/show-content`.
      Diperbaiki jadi `:disabled="..."`. `@disabled` pada tag HTML biasa (`<input>`, `<select>`)
      tetap aman dan tidak diubah.
      **Alat baru:** `scratchpad/compile_all_views.php` — meng-compile **seluruh 206 view** lalu
      mem-parse hasilnya. Inilah yang menemukan kelima view di atas; `view:cache` **tidak** menangkapnya
      karena hanya meng-compile, tidak mem-parse. Jalankan tiap fase berikutnya.
      **Verifikasi RUNTIME:** 43 assert (ter-rollback) — 11 kelas resolve; read (list+search pelanggan,
      `groupedByStatus` 21/21 meja & 5 status urut, kategori+`menus_count`, katalog); dashboard (stats
      konsisten, riwayat hanya milik user itu); CRUD pelanggan (trim & kosong→null, update, delete,
      404 ditolak); **cart terbukti terpisah per meja** (2 vs 1, `empty` satu meja tak menyentuh yang
      lain); order pelanggan (customer_id, tag sumber, total 3 porsi, meja `occupied`→`order_in`,
      **ronde kedua di meja `order_in` tetap boleh**, meja `cleaning` & cart kosong ditolak); reservasi
      (snapshot harga & nama, **bentrok 90 menit ditolak, di luar jendela boleh**).
      **11 komponen Livewire di-render** + 4 aksi yang tidak jalan saat mount dipanggil langsung.
      206 view compile+parse bersih, pemeriksa enum-import bersih, `route:list` 141 tetap,
      `view:cache` + `npm run build` sukses.
- [x] C8. **Employee** (Shift/Tip/ServiceLog) — ✅ SELESAI (2026-08-08)
      **Pre-flight:** `ShiftService` & `ManagerAnalyticsService` dua-duanya hidup — tak ada dead code.
      **Dibangun (`app/Domains/Employee/`):** Enum `ShiftStatus` (menggantikan `Shift::STATUSES`) dan
      `ServiceType` (menggantikan peta label `ServiceLog::TYPES` **di dalam model** — label UI tidak
      lagi menempel di Eloquent). Keduanya diverifikasi **langsung terhadap CHECK constraint Postgres**
      di `pg_constraint`, sesuai pelajaran C4.
      `ShiftRepository`(+Interface) dan `StaffActivityRepository`(+Interface) — Tip + ServiceLog sengaja
      **satu aggregate**: satu layar mencatat keduanya dan satu papan skor menilai keduanya, jadi dua
      repository hanya akan selalu di-inject berbarengan. `ShiftData` DTO, UseCase `ScheduleShift`,
      `SetShiftStatus`, `DeleteShift`, `LogTip`, `LogService`, QueryUseCase `GetWeekSchedule`,
      `GetStaffKpi`, `GetWaiterActivity`, `GetTopCustomers`.
      **`schedulableStaff()` ditaruh di `ShiftRepository`, bukan UserRepository** (deviasi dari blueprint):
      "siapa yang boleh masuk roster" adalah aturan penjadwalan, bukan fakta tentang record user — dan
      domain User baru lahir di C10.
      **Duplikasi ketiga yang dihapus:** blok `match` penghitung awal rentang waktu ada **2 salinan
      identik** (`ManagerAnalyticsService` dan `Staff/Receptionist/TopAnalytics`) — dua layar bertetangga
      di portal yang sama, bebas melenceng. Jadi Enum `AnalyticsRange`
      (today/week/month + `startsAt()`/`label()`/`fromRequest()`). Ditaruh di
      **`app/Domains/Reporting/Enums/`** — domain C9 dimulai dari sini karena semua dashboard membacanya.
      **Skor KPI diberi nama:** rumus `(tips/10000) + services + (requests*2)` yang dulu jadi komentar
      satu baris kini punya konstanta `TIP_DIVISOR`/`REQUEST_WEIGHT` beserta alasannya (satu tip besar
      tak boleh mengalahkan kerja satu shift; permintaan khusus dihargai dua kali layanan rutin).
      **Cast Enum:** `Shift::status` dan `ServiceLog::type`. Blade log layanan kini `$log->type->label()`
      (dulu lookup array `$serviceTypes[$log->type]` yang akan pecah begitu di-cast).
      **Dialihkan:** `ShiftScheduler`, `StaffKpi`, `TopCustomers`, `TipsServiceLog`, `TopAnalytics`;
      `TipsServiceLog` juga berhenti query Table/Order langsung (lewat repository, `inServiceRecent()`
      baru). Literal `'scheduled'` di `SpecialRequestService` dan dua seeder diarahkan ke Enum.
      **Dihapus:** 2 service (total **16** sejak Fase A; `app/Services/` tinggal 13 file dari 29).
      ⚠️ **Deviasi disengaja:** `SetShiftStatusUseCase` **tanpa transition guard** — manajer mengoreksi
      roster manual dan boleh mengembalikan shift dari `absent` ke `scheduled`; penjaga yang benar di
      sini adalah parameter bertipe Enum (nilai tak sah jadi mustahil), sementara service lama justru
      **diam-diam mengabaikan** status tak dikenal.
      **Verifikasi RUNTIME:** 45 assert (ter-rollback) — Enum vs CHECK constraint untuk dua kolom;
      11 kelas resolve; `AnalyticsRange` (3 batas waktu, fallback ngawur & null); cast dua model;
      read (7 kolom Senin–Minggu, `shiftsByDay` 9/9 & kunci `Y-m-d`, `schedulableStaff` 9 staf tanpa
      customer, KPI terurut, TopCustomers terurut Rp811.410); write (shift dibuat→completed→**boleh
      dikoreksi balik**→dihapus, 404 ditolak dua kali; tip 25.000 & log layanan tercatat, trim &
      kosong→null, **total tip hari ini 0→25.000 dan KPI ikut bergerak ke score 3.5**).
      **7 komponen Livewire di-render.** 206 view compile+parse bersih, enum-import bersih,
      `route:list` 141 tetap, `view:cache` + `npm run build` sukses.
- [x] C9. **Reporting** (murni QueryUseCase lintas domain) — ✅ SELESAI (2026-08-08)
      **Pre-flight:** `ReportService` & `Admin/DashboardService` dua-duanya hidup. (Jebakan grep C7
      terulang: pola ber-escape `Admin\\DashboardService` lagi-lagi nihil padahal kelasnya dipakai
      `DashboardController` — selalu grep nama pendeknya.)
      **`app/Repositories/` HABIS.** `DashboardRepository`(+Interface) adalah repository legacy
      terakhir; isinya dibongkar ke repository domain masing-masing sesuai blueprint, bukan dipindah
      utuh. Direktori `app/Repositories` kini tidak ada — semua query hidup di `app/Domains/*/Repositories`.
      **Query yang dipulangkan ke pemiliknya:** Payment (`sumSettledBetween`,
      `countSettledOrdersBetween`, `methodBreakdownForDate`), Order (`countByStatus`, `countInService`,
      `countStaleKitchenOrders`, `recent`, `topMenuItemsForDate`, `topMenuItemsBetween`,
      `sumPaidTotalBetween`, `countBetweenWithStatuses`, `revenueByCashierBetween`,
      `paidTotalsBetween`), Menu (`countAll`), Reservation (`listForDate`). Table/Menu/Reservation
      sisanya sudah punya method yang dibutuhkan sejak C1–C4.
      **Dibangun (`app/Domains/Reporting/`):** `Enums/AnalyticsRange` (sudah lahir di C8),
      `Services/SalesTrendService`, `QueryUseCases/GetSalesReportQueryUseCase` dan
      `GetAdminDashboardQueryUseCase`. Keduanya **murni komposisi** — nol query Eloquent.
      **`SalesTrendService` diberi nama & alasan:** logika paling rumit di ReportService lama (bucket
      per jam / hari / bulan tergantung panjang rentang) kini punya kelas sendiri dengan penjelasan
      *kenapa* — grafik 400 kolom harian tak terbaca, grafik sehari dengan satu kolom tak bercerita,
      dan bucket kosong sengaja ditulis 0 supaya sumbu-x tetap rata dan Selasa yang sepi terlihat.
      **Literal status habis:** `['confirmed','preparing','ready','served','paid']` di ReportService,
      `['served','paid']` di topMenus, `'paid'` di 5 tempat, dan enam label chip dashboard
      (`'draft'`, `'confirmed'`, …) semuanya lewat `OrderStatus`/`TableStatus`/`PaymentStatus`.
      `SalesReportExport` ikut memakai `OrderStatus::Paid` (query-nya tetap `FromQuery` karena
      maatwebsite butuh Builder, bukan hasil).
      **Dihapus:** 2 service + 2 file repository legacy (total **18 service** sejak Fase A;
      `app/Services/` tinggal **11** dari 29).
      ⚠️ **Perubahan tampilan yang disengaja:** chip status order & tile status meja di dashboard kini
      memakai `label()` dari Enum, jadi berbahasa Indonesia (`Dikonfirmasi`/`Disiapkan`/`Siap` dan
      `Tersedia`/`Terisi`/`Pesanan Masuk`/`Perlu Dibersihkan`) — dulu masih mentah berbahasa Inggris.
      Ini menutup satu-satunya layar yang belum ikut penyeragaman C1–C5. Ikon dan token warna
      **tidak** diubah: `TableStatus::color()` untuk `occupied` adalah `error` sedangkan dashboard
      memakai tone `warning`, dan peta `$toneClasses` di blade tak punya kunci itu — menyeragamkannya
      adalah pekerjaan desain, bukan refactor.
      **Verifikasi RUNTIME:** 40 assert — `app/Repositories` benar-benar lenyap & interface lama tak
      bisa di-resolve; 3 kelas resolve; **paritas 10 method repository baru vs query mentah**
      (jumlah, urutan, dan konsistensi `countByStatus` meja = total meja, `countAll` menu =
      tersedia+tidak); `SalesTrendService` diuji ketiga cabangnya dengan data sintetis (24 bucket jam
      & jam 09:00 menjumlahkan dua order; 5 bucket harian dengan hari kosong = 0; 6 bucket bulanan,
      Maret = 22.000); sales report (kunci lengkap, label==nilai, totalSales & totalCustomers cocok
      query mentah, rentang 3 bulan jalan); dashboard (11 kunci, 4 metric, 6 chip **tanpa** paid,
      4 tile, 7 titik grafik, shortcut ber-URL); **dan dashboard terbukti bereaksi pada data baru** —
      satu Payment 123.456 di transaksi ter-rollback mengubah "Penjualan Hari Ini" Rp 0 → Rp 123.456
      dan menaikkan titik terakhir grafik.
      **ReportBoard + halaman `dashboard.blade` penuh (85 KB) ter-render.** 206 view compile+parse
      bersih, enum-import bersih, `route:list` 141 tetap, `view:cache` + `npm run build` sukses.
      *Catatan proses:* dua assert sempat merah — ternyata **uji sayanya** yang salah (`sort()` pada
      koleksi objek Carbon tidak membandingkan seperti dugaan, plus query diulang tiga kali sehingga
      urutan `ordered_at` kembar bisa bergeser). Repository-nya benar; diperiksa dengan membandingkan
      timestamp integer dari satu query. Sinyal merah tetap harus dibuktikan sebelum dipercaya —
      dan begitu juga sinyal hijau.
- [x] C10. **User/System** + domain Social (Song/Special/Chat/Visitor) — ✅ SELESAI (2026-08-08)
      **`app/Services/` HABIS — Fase C tuntas.** Dari 29 service di awal, nol tersisa; direktorinya
      tidak ada lagi. Struktur `app/` kini: Console, Domains, Events, Exports, Http, Livewire, Models,
      Observers, Policies, Providers, Support, View.
      **Pre-flight:** `TableCategoryService` **dead code** (0 consumer) — yang keenam sejak Fase B.
      Dihapus, tidak dimigrasi. Sepuluh sisanya hidup.
      **Dibangun (`app/Domains/Social/`):** Enum `SongStatus` (+`next()` sebagai mesin transisi,
      `activeValues`/`finishedValues`), `SpecialRequestStatus`, `SpecialRequestCategory`;
      `SongRequestRepository` & `SpecialRequestRepository` (+Interface); UseCase `RequestSong`,
      `AdvanceSong`, `RejectSong`, `SubmitSpecialRequest`, `ApproveSpecialRequest`,
      `RejectSpecialRequest`, `CompleteSpecialRequest`; QueryUseCase `GetSongQueue`,
      `GetSpecialRequestBoard`; `Services/WaiterMatchmaker` + `Services/ChatService` (dipindah apa
      adanya — cache/Redis, bukan Eloquent, jadi memang tak butuh Repository).
      **Dibangun (`app/Domains/System/`):** Enum `SubscriptionStatus`; `UserRepository`,
      `AppSettingRepository`, `SubscriptionRepository` (+Interface); `Services/AppSettings`;
      `QueryUseCases/GetLicenseStatusQueryUseCase` (menggantikan `LicenseService` seluruhnya — ketiga
      methodnya read); `UseCases/SaveLicenseUseCase`.
      **`WaiterMatchmaker` memisahkan aturan dari query:** `bestWaiter()` lama mencampur tiga query
      dengan satu aturan penyortiran. Kini query pindah ke repository (`UserRepository::activeWithRole`,
      `ShiftRepository::onShiftUserIdsForDate`, `SpecialRequestRepository::activeLoadByAssignee`) dan
      aturannya — on-shift menang, seri dipecah oleh beban paling ringan — jadi Service murni yang bisa
      diuji dengan array biasa. Terbukti: keempat cabangnya diuji tanpa menyentuh database.
      **Sisa domain terdahulu ikut dituntaskan:** `MediaService` → `Menu/Services`;
      `ReservationDepositService` → `RecordReservationDepositUseCase`; `ReservationReleaseService` →
      `ReleaseExpiredReservationsUseCase` (+ repo `expiredHolds`/`noShowCandidates`);
      `PublicHomeService` & `PublicCartService` **dihapus** — isinya duplikat
      `MenuRepository::featured()` dan `RestaurantCart::count()`/`addItem()`.
      **Cast Enum:** `SongRequest::status`, `SpecialRequest::status` + `category`,
      `Subscription::status`. Empat konstanta label/status terakhir lenyap dari model
      (`SongRequest::STATUSES`/`ACTIVE_STATUSES`, `SpecialRequest::CATEGORIES`,
      `Subscription::STATUSES`) dan empat blade berhenti menulis peta warna sendiri — semuanya
      `<x-status-badge>`.
      ⚠️ **BUG LAMA DITEMUKAN & DIPERBAIKI:** `AppSettings::setMany($values, $group = 'general')`
      menstempel `group` ke **setiap** baris pada tiap simpan massal, sedangkan satu-satunya pemanggil
      (`AppSettingsManager::save`) tak pernah mengoper grup. Artinya sekali admin menekan Simpan,
      ke-9 setting jatuh ke grup `general` dan **seksi-seksi form itu sendiri runtuh permanen**
      (DB nyata punya 3 grup: social/finance/profile). Parameternya dibuang; bulk save kini hanya
      menyentuh `value`. Diverifikasi: distribusi grup identik sebelum & sesudah `setMany`.
      ⚠️ **Utang C6 ditemukan:** `tests/Feature/Admin/StockOpnameTest.php` masih meng-import
      `App\Services\Admin\StockOpnameService` yang dihapus di C6 — file test itu fatal saat di-load.
      Tak ketahuan karena suite memang tak bisa jalan di mesin ini (sqlite, lihat memory
      `env-and-tooling`). Diarahkan ke `CreateStockOpnameDraftUseCase`/`PostStockOpnameUseCase`.
      **Verifikasi RUNTIME:** 62 assert (ter-rollback) — `app/Services` benar-benar lenyap;
      **4 Enum dibandingkan langsung dengan CHECK constraint**; 18 kelas resolve; cast 4 model;
      `SongStatus::next()` keempat cabang termasuk **klik ganda aman** (done tetap done);
      `WaiterMatchmaker` keempat cabang tanpa DB; alur lagu (trim, `requested_by` kosong→null,
      **cap antrean 2 ditegakkan**, advance mengisi `played_at` lalu tidak menimpanya, slot bebas
      lagi setelah selesai); alur permintaan khusus end-to-end (submit→approve **auto-assign ke
      "Waiter Saung RH"**→complete, dan **waiter lain ditolak menutup tugas orang**);
      AppSettings (grup utuh, `set()` menulis grup & tipe, `get()` lewat cache); lisensi
      (`expiring`/`active`/`expired`/`none` + `isValid` saat ditangguhkan); deposit reservasi
      (pending→confirmed, `hold_until` dilepas, nominal 0 ditolak); job pelepasan.
      **11 komponen Livewire di-render**, perintah `reservations:release-expired` dijalankan sungguhan.
      206 view compile+parse bersih, enum-import bersih, `route:list` 141 tetap,
      `view:cache` + `npm run build` sukses.
      *Catatan proses (dua sinyal merah, dua sebab berbeda):*
      (1) `AdvanceSongUseCase` sempat fatal `SongStatus::from()` menerima enum — **bug asli saya**,
      kembaran persis dari yang ditambal di utang Fase B; diperbaiki lalu disapu seluruh proyek untuk
      pola sejenis (bersih).
      (2) `Livewire::mount(TableChat)` gagal dengan "Unable to evaluate dynamic event name placeholder:
      `{tableId}`" — **bukan bug**: komponen itu memakai `#[On('echo:chat.table.{tableId},…')]` dan
      di aplikasi nyata hanya dirender di dalam `@if (TableSessionContext::current())`. Dibuktikan
      dengan request HTTP sungguhan (`GET /menu` tanpa QR → **200**, panel sosial memang tidak
      dirender) dan dengan me-mount ulang setelah sesi meja diisi (**14 KB, PASS**). Harness
      `Livewire::mount()` tidak bisa dipakai apa adanya untuk komponen ber-placeholder dinamis.

### Fase D — Boundary lintas domain — ✅ SELESAI (2026-08-08)

> **Aturan batas diperjelas dulu (keputusan Fase D).** Teks D2 asli — "tidak ada
> `use App\Domains\X` dari dalam domain Y kecuali Events" — bertabrakan dengan §10 dokumen ini
> sendiri, yang menyatakan Reporting **boleh** membaca lintas domain lewat Repository. Melarang
> semuanya berarti tiap domain menyalin ulang query domain lain. Aturan yang dipakai:
>
> | Yang diimpor lintas domain | Boleh? | Alasan |
> |---|---|---|
> | `Events`, `Listeners` | ✅ | Justru inilah batasnya |
> | `Enums`, `DTO` | ✅ | Nilai, bukan perilaku |
> | `Repositories`, `QueryUseCases` | ✅ | Kontrak baca/persistensi milik domain itu sendiri |
> | `UseCases`, `Actions`, `Services` | ❌ | Menulis di domain lain harus jadi **reaksi atas Event** |
>
> Pengecualian dicatat eksplisit di alat audit beserta alasannya, supaya pengecualian adalah
> keputusan yang diambil seseorang, bukan sesuatu yang menyelinap masuk.

- [x] D1. **6 kopling tulis → 4 jadi Event, 2 tetap (beralasan).**
      **Event baru (`app/Domains/Order/Events/`):** `OrderPlaced` (orderId, tableId, source) dan
      `TableBillsCleared` (tableId, settledOrderId). Keduanya **bukan** broadcast — murni kabel
      internal; `OrderCreated`/`OrderUpdated` yang lama tetap melayani KDS.
      **Listener baru (`app/Domains/Table/Listeners/`):** `ClaimTableOnOrderPlaced`,
      `ReleaseTableOnBillsCleared`. Didaftarkan eksplisit di `AppServiceProvider::registerDomainListeners()`
      — Laravel hanya auto-discover `app/Listeners`, dan daftar eksplisit sekaligus jadi peta
      "domain mana bereaksi ke domain mana".
      **`TableBillsCleared` sengaja lebih sempit dari "satu tagihan dibayar":** satu rombongan bisa
      pesan beberapa ronde, jadi domain Order — pemilik pertanyaan "masih ada yang belum lunas?" —
      menjawabnya sendiri dan baru mengumumkan saat meja benar-benar tidak berhutang. Domain Table
      cukup membebaskan meja tanpa tahu apa pun soal tagihan.
      **Enum `OrderSource` menyatukan kosakata yang tadinya terbelah dua:** tiga UseCase menulis
      literal `'Sumber: POS'`/`'Sumber: DINE-IN QR'`/`'Sumber: CUSTOMER ORDER'`, sementara layar kasir
      **mengendus balik** dengan `str_contains($notes, 'QR')`. Dua sisi satu kosakata, bebas melenceng.
      Kini `composeNotes()` dan `fromNotes()` ada di satu Enum, dan `OrderSource::claimsOccupiedTable()`
      memindahkan perbedaan aturan POS-vs-dine-in ke tempat yang bisa dibaca — dulu tersembunyi
      sebagai `in_array` berbeda di dua UseCase.
      **Dua yang TETAP panggilan langsung, dengan alasan:** `Order → Payment\RecordPaymentAction`
      (pembayaran **adalah** pelunasannya — satu transaksi, dan pemanggil butuh baris Payment-nya
      kembali; listener tak bisa mengembalikan nilai) dan `Reservation → PaymentRepository` (deposit
      adalah bagian dari konfirmasi booking). Yang pertama terdaftar sebagai pengecualian resmi.
      ⚠️ **Konsekuensi yang disengaja:** klaim/pembebasan meja kini terjadi **setelah** transaksi order
      commit (`DB::afterCommit`), bukan di dalamnya. Kalau reaksi meja gagal, order tetap tersimpan dan
      status meja bisa dikoreksi staf — pertukaran normal dari decoupling, dan uangnya yang dilindungi.
- [x] D2. **Alat audit `scratchpad/audit_domain_deps.php`** memindai seluruh `app/Domains`,
      mengklasifikasi tiap `use App\Domains\...` lintas domain per layer, dan **keluar dengan exit
      code 1** bila ada domain yang menulis langsung ke domain lain. Juga melaporkan pengecualian yang
      terdaftar tapi tak lagi dipakai, supaya daftarnya tak jadi sampah.
      **Hasil akhir: CLEAN** — 29 impor lintas domain tersisa, semuanya kategori boleh
      (13 Enums, 13 Repositories, 2 Events, 1 QueryUseCase) + 1 pengecualian tercatat.
      Sebelum D1: 6 pelanggaran tulis.

**Verifikasi RUNTIME Fase D:** 32 assert — `OrderSource` (compose/from bolak-balik utuh untuk ketiga
case, catatan lama tanpa tag jatuh ke App, aturan klaim per sumber); **kedua listener benar-benar
terdaftar** di dispatcher; UseCase **mengumumkan, bukan menulis** (dengan `Event::fake`, meja terbukti
tidak berubah); **gerbang `TableBillsCleared` terbukti**: melunasi satu dari dua tagihan meja **tidak**
menerbitkan event, melunasi sisanya menerbitkan; listener diuji per cabang (`available`+POS→order_in,
`occupied`+POS→**tetap occupied**, `occupied`+QR→order_in, `reserved`→tak disentuh siapa pun, order
takeaway tanpa meja & meja hilang→aman, release→cleaning + sesi ditutup); dan **rantai penuh tanpa
`Event::fake` sama sekali** — place → listener klaim meja → settle → listener bebaskan meja → kasir
membaca sumber "QR". Jumlah baris DB identik sebelum & sesudah (20 order, 20 payment).
206 view compile+parse bersih, enum-import bersih, `route:list` 141 tetap,
`view:cache` + `npm run build` sukses.

*Catatan proses:* sebuah skrip ad-hoc sempat melaporkan "meja tidak dibebaskan". Ditelusuri, itu
**perilaku yang benar** — meja yang dipilih skrip kebetulan masih punya tagihan lain dari data seed,
jadi gerbangnya memang menahan event. Dibuktikan dengan sengaja mengulang skenario di meja T-19
(punya 1 tagihan lain): event tertahan, lalu setelah tagihan itu ikut dilunasi syaratnya terpenuhi.
Skrip ad-hoc itu juga punya bug sendiri — memulihkan status meja lewat model Eloquent yang sudah
basi, sehingga `update()` tidak melihat atribut kotor dan tak mengirim query sama sekali.

### Fase E — Konsolidasi
- [ ] E1. Pindahkan Eloquent Models ke `app/Domains/{X}/Models` (opsional, terakhir — hati-hati namespace/migration).
- [x] E2. **`AGENTS.md`/`ARCHITECTURE.md` diselaraskan dengan kode — ✅ SELESAI (2026-08-10)**

      Enam deviasi yang dicatat, bukan didiamkan:
      1. **Aturan batas** — larangan menyeluruh "domain tidak mengimpor kelas domain lain" diganti
         tabel Fase D (Events/Enums/DTO/Repository/QueryUseCase boleh; UseCase/Action/Service tidak),
         plus alat audit dan satu pengecualian terdaftar.
      2. **Tidak ada domain `Kitchen`** — tiket dapur adalah Order berstatus `preparing`/`ready`,
         jadi KDS tinggal di domain Order. Memisahnya berarti dua domain memiliki satu baris yang sama.
      3. **Tidak ada domain `User`** — jadi `System`; ditambah domain `Social` yang tak ada di doc lama.
         Daftar 11 domain aktual menggantikan daftar lama.
      4. **Aturan transisi ada di dalam Enum**, bukan kelas Policy. Policy menjawab *boleh tidak
         pengguna ini*, sedangkan status apa boleh menyusul status apa adalah sifat statusnya sendiri.
      5. **Enum ≠ selalu cast Eloquent** — `Order`/`Shift`/`SongRequest`/`SpecialRequest`/`Subscription`
         di-cast; `Table`/`Menu`/`Reservation`/`Payment` masih string dan dibandingkan lewat `->value`.
         Dicatat apa adanya sebagai urutan migrasi, bukan prinsip, plus arah rapinya ke mana.
         Komentar menyesatkan di `app/Models/Table.php` (mengaku sama dengan `Order::$status`,
         padahal Order justru di-cast) ikut dibetulkan.
      6. **Peta routes** di ARCHITECTURE.md diganti dengan struktur `routes/` yang sebenarnya.

      Contoh `OrderStatus` di AGENTS.md juga diganti dari karangan (`Submitted`/`Completed`) ke
      tujuh case yang benar-benar ada.
- [x] E3. **Test suite penuh + `migrate:fresh --seed` + verifikasi end-to-end — ✅ SELESAI (2026-08-10)**

      **`php artisan test`: 48 lolos, 151 assert, 0 gagal.** Sebelum diperbaiki ada 4 gagal, semuanya
      di scaffolding Breeze bawaan — bukan domain. Tiga di antaranya assertion basi yang menuntut rute
      `/dashboard` yang memang sudah tidak ada (aplikasi mengarahkan per peran: `dashboard` →
      `/admin/dashboard`, pendaftar publik → `/customer/dashboard`); testnya disesuaikan dan kini
      memakai `route()`, bukan path harfiah, supaya tak basi lagi.
      Satu sisanya **bug asli**: `AppLayout::render()` merangkai `auth()->user()->roles->first()->name`
      tanpa pengaman, jadi pengguna tanpa peran meruntuhkan **seluruh** kerangka halaman dengan 500.
      Diperbaiki jadi null-safe — `layouts.app` memang sudah punya fallback portal.

      **`migrate:fresh --seed`: bersih** (65 langkah), menghasilkan 20 order / 20 payment / 21 meja /
      12 menu / 19 user.

      **Smoke-crawl 675 request — 9 peran × 75 route GET, hasil akhir 0 gagal.** Putaran pertama
      menemukan **35 kegagalan pada 13 halaman staf** (semua portal manager/receptionist/waiter/ob +
      `song-queue`). Penyebabnya `@livewire($component, ...)` di `resources/views/staff/page.blade.php`:
      di dalam anonymous component, `$component` adalah nama yang **sudah dipakai Blade** untuk objek
      komponennya sendiri, jadi Livewire menerima `Illuminate\View\AnonymousComponent` alih-alih nama
      komponen. Kunci datanya diganti `livewireComponent` (view + 13 pemanggil di `PortalController`).
      ⚠️ **Bug ini pre-existing** — identik di `master` sejak commit "fase 3", bukan akibat refactor;
      artinya 13 halaman staf itu sudah mati sejak lama tanpa ketahuan. Ini justru alasan E3 ada.

      **Loop bisnis end-to-end: 17 assert, 0 gagal** (`PlaceGuestOrderUseCase` → dapur → `SettleBillUseCase`,
      dijalankan lewat container, bukan menulis model langsung): order tersimpan `confirmed` dengan total
      sesuai `CalculateOrderTotalAction`, nama tamu ter-default dari kode meja, `OrderSource` terbaca
      balik sebagai QR, **meja diklaim listener** (bukan ditulis domain Order), tiket maju
      preparing→ready→served, pembayaran tercatat penuh, order jadi `paid`, meja dibebaskan ke `cleaning`.
      Gerbang `TableBillsCleared` diuji ulang: dua tagihan di satu meja → melunasi yang pertama **tidak**
      membebaskan meja, melunasi yang kedua baru membebaskan.

      *Catatan data seed:* seeder menghasilkan meja berstatus `available` yang masih menggantung tagihan
      belum lunas. Sempat terbaca sebagai kegagalan, ternyata perilaku yang benar — gerbang event memakai
      **sisa tagihan**, bukan status order, jadi meja memang ditahan. Yang perlu dirapikan seedernya,
      bukan aturannya.

      `route:list` 141 tetap, `view:cache` bersih, `npm run build` sukses (peringatan chunk 891 kB
      pre-existing).
- [x] E4. **Memory diperbarui — ✅ SELESAI (2026-08-10)**
      `refactor-to-docs-plan` ditulis ulang (dulu masih bilang "eksekusi belum dimulai"); status
      per-fase menunjuk ke dokumen ini, bukan disalin. `refactor-audit-findings` dan
      `proposal-alignment-roadmap` diberi peringatan jalur basi di paling atas + tabel pemetaan
      nama kelas lama→baru (`OrderCartService`→`CustomerCart`, `Pos\BillingService`→
      `OrderBillingService`+`SettleBillUseCase`, `TableTurnoverService::release`→listener
      `ReleaseTableOnBillsCleared`), supaya sesi berikutnya tidak mengejar kelas yang sudah tak ada.

---

## 7. Estimasi & risiko

| Fase | Effort | Risiko | Catatan |
|---|---|---|---|
| A | 1–2 hari | Rendah | Langsung selaras Core Principle #1 |
| B (pilot Order) | 3–5 hari | Sedang | Validasi pola sebelum replikasi |
| C (10 domain) | 3–5 minggu | **Tinggi** | Beban utama; kerjakan per-domain, merge terpisah |
| D | 2–3 hari | Sedang | Events sudah sebagian ada |
| E | 3–4 hari | Sedang | Migrasi model & data status |
| **Total** | **~5–7 minggu** | | Aplikasi sudah terverifikasi jalan → tiap fase harus test-verified sebelum lanjut |

**Risiko khusus yang harus diwaspadai:**
- **Migrasi data status (G5)** — konversi FK→string menyentuh data produksi; wajib migration reversible + backup.
- **Fitur CRUD status admin** (`Admin/TableStatuses`, `Admin/MenuStatuses`) menjadi usang → konfirmasi hapus.
- **Cakupan test** — repo punya keterbatasan test sqlite (lihat memory `env-and-tooling`); verifikasi runtime pakai Postgres + `php artisan serve` + browser, seperti audit sebelumnya.
- **Big-bang vs incremental** — WAJIB per-domain incremental; jangan refactor semua domain sekaligus tanpa test di antaranya.

---

## 8. Aturan kerja selama refactor
1. Satu domain = satu branch/PR, di-test end-to-end sebelum merge.
2. Setiap PR: `route:list` + `view:cache` + `npm run build` bersih, plus smoke test loop bisnis terkait.
3. Patuhi Merge Rule: **jangan** bikin Action/DTO/Interface yang cuma dipakai 1× — itu mengulang overengineering yang sedang kita hapus.
4. Repository interface = selalu; Service interface = hanya bila poly nyata.
5. Update checklist di dokumen ini (§6) tiap item selesai.
