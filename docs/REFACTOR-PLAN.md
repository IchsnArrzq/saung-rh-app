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

### Fase A — Quick wins (risiko rendah, ~1–2 hari)
- [ ] A1. G4 — hapus interface service 1-impl, rename `*Implement`→`*Service`, rapikan `ServiceBindingsProvider`.
- [ ] A2. G6 — pecah routes ke subfolder; `web.php` jadi loader.
- [ ] A3. Verifikasi: `route:list`, `view:cache`, `npm run build`, smoke test.

### Fase B — Pilot domain **Order** end-to-end (~3–5 hari)
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
- [ ] C1. **Table** (+TableStatus Enum, TablePolicy transitions)
- [ ] C2. **Menu** (+MenuAvailability Enum)
- [ ] C3. **Payment** (+PaymentStatus Enum; audit interface poly di sini)
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
