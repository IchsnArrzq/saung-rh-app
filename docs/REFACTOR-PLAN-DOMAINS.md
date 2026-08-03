# Refactor Blueprint — Method-Level Mapping per Domain

> Pendamping [REFACTOR-PLAN.md](REFACTOR-PLAN.md). Dibuat 2026-07-31.
> Memetakan **setiap method service aktual** → layer target (UseCase / QueryUseCase / Action / Service / Repository)
> sesuai AGENTS.md. Signature diambil langsung dari `app/Services/**/*Implement.php`.
>
> **Konvensi kolom "Target":**
> - **W-UseCase** = write flow, dibungkus `DB::transaction` di UseCase.
> - **Q-UseCase** = read flow, QueryUseCase tipis → Repository.
> - **Action** = HANYA jika reusable ≥2 UseCase (Merge Rule). Kalau 1× → fold ke UseCase.
> - **Service** = kalkulasi/rule murni, TANPA query DB.
> - **Repo** = pindahkan semua query Eloquent ke sini.
>
> **Aturan `create/update/delete(Request $request)`:** semua service saat ini menerima `Request` langsung —
> ini melanggar AGENTS §Controller (Request tak boleh bocor ke layer bawah). Target: Controller/Livewire
> mem-validasi → bikin **DTO** → UseCase terima DTO, bukan `Request`.

---

## Catatan lintas-domain (berlaku semua)

| Method-pola | Klasifikasi | Keterangan |
|---|---|---|
| `paginate($perPage,$search)` | Q-UseCase → Repo | `GetXListQueryUseCase`; query pindah ke `XRepository::paginate()` |
| `statusOptions()`/`methodOptions()` | **Enum** | Setelah G5, opsi status berasal dari `Enum::cases()`, bukan query/array |
| `tables()`/`categories()`/`orders()` (dropdown data) | Q-UseCase → Repo | data referensi untuk form |
| `create/update/delete(Request)` | W-UseCase (+DTO) | validasi di Controller/Livewire → DTO → UseCase |
| `withItems(Order)` | Q-UseCase / Repo | eager-load, tak ada state change |

**G4 (interface):** Setelah audit signature, **tidak ada** service yang punya ≥2 implementasi nyata —
semua CRUD/domain-specific. Jadi **semua 33 interface dihapus**, service jadi konkret. `PaymentService` di sini
adalah CRUD pembayaran admin (bukan strategy gateway), jadi tidak poly. Interface **Repository tetap** dibuat.

---

## 1. Domain Order

Service sumber: `Admin/OrderService`, `Customer/OrderCartService`, `Landing/PublicCartService`, `Pos/BillingService`.

### OrderService (admin CRUD)
| Method sekarang | Target | Kelas/method baru |
|---|---|---|
| `paginate($perPage,$search)` | Q-UseCase → Repo | `GetOrderListQueryUseCase` → `OrderRepository::paginate()` |
| `statusOptions()` | Enum | `OrderStatus::cases()` |
| `tables()` | Q-UseCase → Repo | pinjam `TableRepository::selectable()` (via QueryUseCase, bukan import lintas-domain — lihat catatan Events) |
| `availableMenus()` | Q-UseCase → Repo | `MenuRepository::available()` |
| `withItems(Order)` | Repo | `OrderRepository::loadItems()` |
| `create(Request)` | W-UseCase +DTO | `CreateOrderUseCase(CreateOrderData)` → `CalculateOrderTotalAction` |
| `update(Request,Order)` | W-UseCase +DTO | `UpdateOrderUseCase(UpdateOrderData)` → `CalculateOrderTotalAction` |
| `delete(Order)` | W-UseCase | `DeleteOrderUseCase` |

### OrderCartService (customer per-meja)
| Method sekarang | Target | Kelas/method baru |
|---|---|---|
| `tableSelectionData($search)` | Q-UseCase → Repo | `GetTableSelectionQueryUseCase` (domain Table) |
| `catalog($search,$cat,$perPage)` | Q-UseCase → Repo | `GetMenuCatalogQueryUseCase` (domain Menu) |
| `findAvailableTable`/`findOrderableTable` | Repo | `TableRepository::findByStatuses([...])` |
| `setActiveTable`/`activeTableId`/`forgetActiveTable` | Service | `CartSessionService` (session state, no DB) |
| `addItem`/`setQty`/`setNotes`/`removeItem`/`emptyCart` | Service | `CartService` (state cart di session) |
| `cartItems`/`cartCount`/`cartSubtotal` | Service | `CartService` (kalkulasi murni) |
| `placeOrder($tableId,$notes)` | **W-UseCase** | `PlaceCartOrderUseCase` → `CalculateOrderTotalAction` (reuse!) |

### PublicCartService (guest QR)
| `quickAdd(Menu,$qty,$notes)` | Service | `GuestCartService::quickAdd()` (RestaurantCart single) |

### BillingService (kasir)
| Method sekarang | Target | Kelas/method baru |
|---|---|---|
| `openBills($search)` | Q-UseCase → Repo | `GetOpenBillsQueryUseCase` → `OrderRepository::openBills()` |
| `summarize`/`paidAmount`/`outstanding` | Service | `BillingService` (kalkulasi dari record Payment, murni) |
| `settle(Order,$method)` | **W-UseCase** | `SettleBillUseCase` (transaction: buat Payment + Order→paid + event) |
| `tableHasOpenBills(Table)` | Repo | `OrderRepository::tableHasOpenBills()` |

**Actions dipakai ≥2× (earn file):** `CalculateOrderTotalAction` (Create/Update/PlaceCart).
**Enum:** `OrderStatus` (`draft,pending,confirmed,preparing,ready,served,completed,paid,cancelled`) + `OrderStatusPolicy` (transition).
**Events:** `SettleBillUseCase`/`PlaceCartOrderUseCase` dispatch `OrderCreated`/`PaymentCompleted` → listener Kitchen & Table (turnover).

---

## 2. Domain Table

Service sumber: `Admin/TableService`, `Admin/TableCategoryService`, `Admin/TableStatusService`, `Tables/TableTurnoverService`, `Customer/CheckInService`.

| Method sekarang | Target | Kelas/method baru |
|---|---|---|
| `TableService::paginate` | Q-UseCase → Repo | `GetTableListQueryUseCase` |
| `TableService::statusOptions/boardStatuses` | **Enum** | `TableStatus::cases()` (metadata color/label ke method Enum) |
| `TableService::categoryOptions` | Q-UseCase → Repo | `TableCategoryRepository::options()` |
| `TableService::create/update/delete(Request)` | W-UseCase +DTO | `Create/Update/DeleteTableUseCase` |
| `TableService::updateStatus(Table,$statusId)` | **W-UseCase** | `ChangeTableStatusUseCase` (+`TablePolicy` transition rule) |
| `TableCategoryService::*` (paginate/CRUD) | Q/W-UseCase | mirror pola |
| **`TableStatusService::*` (CRUD status)** | **HAPUS** | status jadi Enum → fitur CRUD status usang (konfirmasi hapus `Admin/TableStatuses/*` + controller + route + nav) |
| `TableTurnoverService::release(Table)` | Service/W-UseCase | `TableTurnoverService::release()` tetap; query TableSession → `TableSessionRepository` |
| `CheckInService::checkIn(Table)` | **W-UseCase** | `CheckInTableUseCase` (buat TableSession, meja→occupied) |

**Enum:** `TableStatus` (`available,occupied,order_in,reserved,cleaning`) + `TablePolicy` transition (mis. `order_in→cleaning` saat lunas). **Metadata pindah:** `name`(label ID), `color`(token daisyUI), `sort_order` → method Enum.

---

## 3. Domain Menu

Service sumber: `Admin/MenuService`, `Admin/MenuCategoryService`, `Admin/MenuStatusService`(implisit), `Admin/MediaService`, `Customer/MenuCatalogService`, `Landing/PublicHomeService`.

| Method sekarang | Target | Kelas/method baru |
|---|---|---|
| `MenuService::paginate` | Q-UseCase → Repo | `GetMenuListQueryUseCase` |
| `MenuService::categories(?Menu)` | Q-UseCase → Repo | `MenuCategoryRepository::forSelect()` |
| `MenuService::create/update/delete(Request)` | W-UseCase +DTO | `Create/Update/DeleteMenuUseCase` |
| `MenuCategoryService::paginate/create/update/delete` | Q/W-UseCase | mirror |
| **`MenuStatusService::*` (CRUD status)** | **HAPUS** | → `MenuAvailability` Enum; hapus `Admin/MenuStatuses/*` |
| `MediaService::addImage/addVideo` | W-UseCase +Action | `AttachMenuMediaUseCase`; `StoreMediaAction` (reuse image+video) |
| `MediaService::setPrimaryImage` | W-UseCase | `SetPrimaryImageUseCase` |
| `MediaService::delete(Media)` | W-UseCase | `DeleteMediaUseCase` |
| `MenuCatalogService::paginateAvailable` | Q-UseCase → Repo | `GetMenuCatalogQueryUseCase` (dipakai customer+guest) |
| `PublicHomeService::featuredMenus` | Q-UseCase → Repo | `GetFeaturedMenusQueryUseCase` |
| `PublicHomeService::cartCount` | Service | `GuestCartService::count()` (domain Order) |

**Enum:** `MenuAvailability` (`available,unavailable,sold_out,seasonal`).
**Action ≥2×:** `StoreMediaAction`.

---

## 4. Domain Reservation

Service sumber: `Admin/ReservationService`, `Customer/BookingService`, `Reservations/ReservationDepositService`, `Reservations/ReservationReleaseService`.

| Method sekarang | Target | Kelas/method baru |
|---|---|---|
| `ReservationService::paginate` | Q-UseCase → Repo | `GetReservationListQueryUseCase` |
| `ReservationService::tables` | Q-UseCase → Repo | `TableRepository::selectable()` (via QueryUseCase) |
| `ReservationService::statusOptions` | **Enum** | `ReservationStatus::cases()` |
| `ReservationService::create/update/delete(Request)` | W-UseCase +DTO | `Create/Update/CancelReservationUseCase` |
| `BookingService::createFormData` | Q-UseCase | `GetBookingFormDataQueryUseCase` |
| `BookingService::place(array)` | **W-UseCase** | `PlaceReservationUseCase(PlaceReservationData)` |
| `ReservationDepositService::record(...)` | W-UseCase +Action | `RecordDepositAction` (mungkin reuse dgn Payment) |
| `ReservationReleaseService::releaseExpired()` | W-UseCase (job) | `ReleaseExpiredReservationsUseCase` (dipanggil scheduler) |

**Enum:** `ReservationStatus` (derive dari ReservationSeeder). **Event:** check-in → `Order` via listener, bukan call langsung (ARCH §Domain Dependencies).

---

## 5. Domain Payment

Service sumber: `Admin/PaymentService`, `Pos/BillingService` (settle bagian di domain Order).

| Method sekarang | Target | Kelas/method baru |
|---|---|---|
| `PaymentService::paginate` | Q-UseCase → Repo | `GetPaymentListQueryUseCase` |
| `PaymentService::orders` | Q-UseCase → Repo | `OrderRepository::payable()` (via QueryUseCase) |
| `PaymentService::methodOptions/statusOptions` | **Enum** | `PaymentMethod::cases()` / `PaymentStatus::cases()` |
| `PaymentService::create/update/delete(Request)` | W-UseCase +DTO | `Create/Update/DeletePaymentUseCase` |
| (Observer) `PaymentObserver` | tetap | audit/stock deduction via Observer (AGENTS §Audit Log) |

**Enum:** `PaymentStatus`(`pending,paid,cancelled`), `PaymentMethod` (cash/qris/transfer — verifikasi di kode).
**Catatan:** `InventoryService::deductFromPayment` dipicu observer → tetap, tapi query pindah ke `IngredientRepository`.

---

## 6. Domain Kitchen (KDS)

Sebagian besar **read + event** (tak ada service khusus; logika di `Kds/Board` + controller).

| Fungsi sekarang | Target | Kelas/method baru |
|---|---|---|
| Query antrian dapur (di Livewire `Kds/Board`) | Q-UseCase → Repo | `GetKitchenQueueQueryUseCase` → `OrderRepository::kitchenQueue()` |
| Update status masak (preparing/ready/served) | **W-UseCase** | `AdvanceKitchenTicketUseCase` (+dispatch `KitchenStatusUpdated`) |
| `Staff/Waiter/*` (serve, status) | W-UseCase | `MarkOrderServedUseCase` dst |

**Event:** listener `OrderCreated` → generate ticket; broadcast `KitchenStatusUpdated` (ARCH §Realtime).

---

## 7. Domain Inventory

Service sumber: `Admin/InventoryService`, `Admin/StockOpnameService`, `Admin/PurchaseService`, `Admin/SaleService`. (Domain terbesar.)

| Method sekarang | Target | Kelas/method baru |
|---|---|---|
| `InventoryService::addStock/reduceStock/adjustStock` | **Action** | `AddStockAction`/`ReduceStockAction`/`AdjustStockAction` (reuse byk UseCase) |
| `InventoryService::deductFromPayment(Payment)` | W-UseCase | `DeductStockOnPaymentUseCase` (dipanggil observer/listener) → pakai `ReduceStockAction` |
| `StockOpnameService::createDraft(...)` | W-UseCase | `CreateStockOpnameDraftUseCase` |
| `StockOpnameService::post(StockOpname)` | **W-UseCase** | `PostStockOpnameUseCase` → `AdjustStockAction` (reuse) |
| `PurchaseService::post(Purchase)` | **W-UseCase** | `PostPurchaseUseCase` → `AddStockAction` (reuse) |
| `SaleService::post(Sale)` | **W-UseCase** | `PostSaleUseCase` → `ReduceStockAction` (reuse) |
| CRUD Ingredient/Supplier/Purchase/StockOpname (di Livewire) | Q/W-UseCase | mirror pola paginate/create/update/delete + query→Repo |

**Actions ≥2× (earn file):** `AddStockAction`, `ReduceStockAction`, `AdjustStockAction` — inti reusable domain ini.
**Repo:** `IngredientRepository`, `StockMovementRepository`, `PurchaseRepository`, `SupplierRepository`, `SaleRepository`, `StockOpnameRepository`.
**Livewire yg query langsung** (`Admin/Ingredients/Table.php:36` dll) → pindah ke Repository.

---

## 8. Domain Customer

Service sumber: `Customer/DashboardService`, CRUD di `Admin/Customers/*`.

| Method sekarang | Target | Kelas/method baru |
|---|---|---|
| `DashboardService::data()` | Q-UseCase → Repo | `GetCustomerDashboardQueryUseCase` |
| `Admin/Customers/Table.php:36` `Customer::query()` | Q-UseCase → Repo | `GetCustomerListQueryUseCase` → `CustomerRepository` |
| `Admin/Customers/Form.php:58` `Customer::create()` | W-UseCase +DTO | `CreateCustomerUseCase` |
| update/delete customer | W-UseCase | `Update/DeleteCustomerUseCase` |

---

## 9. Domain Employee

Service sumber: `Manager/ShiftService`, `Manager/ManagerAnalyticsService`, + `Staff/Waiter/TipsServiceLog`.

| Method sekarang | Target | Kelas/method baru |
|---|---|---|
| `ShiftService::schedule(...)` | W-UseCase | `ScheduleShiftUseCase` |
| `ShiftService::setStatus(Shift,$status)` | W-UseCase | `SetShiftStatusUseCase` (+`ShiftStatus` Enum) |
| `ShiftService::delete(Shift)` | W-UseCase | `DeleteShiftUseCase` |
| `ShiftService::week(Carbon)` | Q-UseCase → Repo | `GetWeekScheduleQueryUseCase` |
| `ShiftService::schedulableStaff()` | Q-UseCase → Repo | `UserRepository::schedulable()` |
| `ManagerAnalyticsService::rangeStart($range)` | Service | helper murni (no DB) |
| `ManagerAnalyticsService::topStaff/topCustomers` | Q-UseCase → Repo | `GetTopStaffQueryUseCase`/`GetTopCustomersQueryUseCase` |
| Tips/ServiceLog (Waiter) | W-UseCase | `LogTipUseCase`/`LogServiceUseCase` |

**Enum:** `ShiftStatus` (derive).

---

## 10. Domain Reporting

Service sumber: `Admin/ReportService`, `Admin/DashboardService`. **Murni read** → semua QueryUseCase lintas-domain (boleh, karena read-only via Repository masing-masing domain).

| Method sekarang | Target | Kelas/method baru |
|---|---|---|
| `ReportService::getReportData($start,$end)` | Q-UseCase → Repo | `GetSalesReportQueryUseCase` (agregasi via Repo tiap domain) |
| `Admin/DashboardService::summary()` | Q-UseCase → Repo | `GetAdminDashboardQueryUseCase` |

---

## 11. Domain User/System + Social (lintas)

| Service | Target ringkas |
|---|---|
| `Settings/AppSettings` (all/get/set/setMany/flush) | Service konfigurasi tetap (bukan UseCase) — AGENTS §Configuration |
| `Settings/LicenseService` (current/isValid/summary) | Service + Q-UseCase untuk `summary()` |
| `Songs/SongRequestService` (request/advance/reject/queue) | W-UseCase per aksi + Q-UseCase `queue()`; `SongStatus` Enum |
| `SpecialRequests/SpecialRequestService` (submit/approve/reject/assign/complete/autoMatch) | W-UseCase per aksi; `autoMatch` = Service (rule); `SpecialRequestStatus` Enum |
| `Chat/ChatService` (messages/post/dm/flush) | Service (cache/redis-backed, bukan Eloquent) — tetap, no Repo |

---

## 12. Ringkasan Enum yang dibuat (G5)

| Enum | Domain | Values |
|---|---|---|
| `OrderStatus` | Order | draft, pending, confirmed, preparing, ready, served, completed, paid, cancelled |
| `TableStatus` | Table | available, occupied, order_in, reserved, cleaning |
| `MenuAvailability` | Menu | available, unavailable, sold_out, seasonal |
| `PaymentStatus` | Payment | pending, paid, cancelled |
| `PaymentMethod` | Payment | cash, qris, transfer *(verifikasi)* |
| `ReservationStatus` | Reservation | *(derive dari seeder)* |
| `ShiftStatus` | Employee | *(derive)* |
| `SongStatus` | Social | *(derive)* |
| `SpecialRequestStatus` | Social | *(derive)* |

Tiap Enum: backing string = `key` lama; method `label()`, `color()`, `sortOrder()` (khusus Table/Menu yg punya metadata UI). Transition rule → Policy per domain.

---

## 13. Ringkasan Action yang "earn a file" (≥2× reuse)

| Action | Domain | Dipakai oleh |
|---|---|---|
| `CalculateOrderTotalAction` | Order | CreateOrder, UpdateOrder, PlaceCartOrder |
| `StoreMediaAction` | Menu | AttachImage, AttachVideo |
| `AddStockAction` | Inventory | PostPurchase, (adjust) |
| `ReduceStockAction` | Inventory | PostSale, DeductStockOnPayment |
| `AdjustStockAction` | Inventory | PostStockOpname, adjust manual |
| `RecordDepositAction` | Reservation/Payment | PlaceReservation, (payment) — *verifikasi reuse* |

Selain ini, satu-flow-satu-Action **tidak** diekstrak (Merge Rule) — logika langsung di UseCase.

---

## 14. Yang DIHAPUS (konsekuensi keputusan)

- `app/Services/**/*Interface.php` (33 file) — kecuali Repository interfaces yg baru dibuat.
- `Admin/TableStatuses/*` + `Admin/MenuStatuses/*` (Livewire, controller, route, nav) — CRUD status usang karena Enum.
- Tabel `table_statuses`, `menu_statuses` + seeder — setelah migrasi data ke kolom string.
- `ServiceBindingsProvider.php` — dikosongkan/dihapus (binding service konkret otomatis).
