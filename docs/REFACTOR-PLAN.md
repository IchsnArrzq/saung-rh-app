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
> **Sengaja BELUM dilakukan (catat untuk Fase C/D):**
> - Model `Order` **tidak** di-cast ke Enum (`'status' => OrderStatus::class`) — supaya Blade yang
>   membandingkan string tidak pecah. Domain memakai `OrderStatus::from($order->status)`. Cast menyusul
>   saat Blade ikut dirapikan.
> - `SettleBillUseCase` masih memanggil `TableTurnoverService` lintas domain & memakai
>   `PaymentService::METHOD_OPTIONS` → keduanya sudah ditandai `@todo` (Fase D / C3).
> - Consumer Order lain belum dialihkan: `Pos/OrderCard`, `Frontend/CartCheckout`, `Frontend/OrderStatus`,
>   sisa 3 query di `Admin/Orders/Table`. Query Order di domain lain (Reporting/Dashboard/Reservation/
>   Payment/Manager/Staff/Export) memang jatah Fase C.

#### Checklist asli
- [ ] B1. Buat skeleton `app/Domains/Order/{UseCases,QueryUseCases,Actions,Services,Repositories,DTO,Enums,Policies,Events}`.
- [ ] B2. `OrderRepository` (+Interface) — pindahkan SEMUA query Order dari service & Livewire.
- [ ] B3. Write UseCases: `CreateOrderUseCase`, `UpdateOrderUseCase`, `CancelOrderUseCase`, `SettleBillUseCase`.
- [ ] B4. `CalculateOrderTotalAction` (contoh reuse eksplisit di AGENTS).
- [ ] B5. QueryUseCases: `GetOrderListQueryUseCase`, `GetKitchenQueueQueryUseCase`.
- [ ] B6. `OrderStatus` Enum + `OrderStatusPolicy` (transition rules).
- [ ] B7. Livewire Order/POS panggil UseCase. **Test runtime end-to-end** (pola verifikasi di memory `refactor-audit-findings`).
- [ ] B8. **Review pola bersama pemilik** → jadikan template Fase C.

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
- [ ] C4. **Reservation** (+ReservationStatus Enum)
- [ ] C5. **Kitchen** (KDS — sebagian besar QueryUseCase + Events)
- [ ] C6. **Inventory** (Ingredient/Stock/Purchase/Supplier/Sale — domain terbesar)
- [ ] C7. **Customer**
- [ ] C8. **Employee** (Shift/Tip/ServiceLog)
- [ ] C9. **Reporting** (murni QueryUseCase lintas domain)
- [ ] C10. **User/System** + domain Social (Song/Special/Chat/Visitor)

### Fase D — Boundary lintas domain (~2–3 hari)
- [ ] D1. Ganti call antar-domain langsung → **Events + Listener** (ARCH §Domain Dependencies). Manfaatkan `OrderCreated`/`OrderUpdated` yang sudah ada; tambah listener di Kitchen & Table.
- [ ] D2. Audit statis: tidak ada `use App\Domains\X\...` dari dalam domain Y kecuali `Events`.

### Fase E — Konsolidasi
- [ ] E1. Pindahkan Eloquent Models ke `app/Domains/{X}/Models` (opsional, terakhir — hati-hati namespace/migration).
- [ ] E2. Update `AGENTS.md`/`ARCHITECTURE.md` bila ada deviasi final yang disepakati.
- [ ] E3. Test suite penuh + `migrate:fresh --seed` + verifikasi browser end-to-end (customer + kasir loop).
- [ ] E4. Update memory `refactor-audit-findings` & `proposal-alignment-roadmap`.

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
