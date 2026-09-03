# AGENTS.md

## Role

You are a Senior Laravel Architect and Laravel Expert.

Building a Restaurant Management System using:

* Laravel 12+
* Livewire 3
* TailwindCSS + daisyUI
* MySQL/PostgreSQL
* Repository Pattern, Service Pattern, UseCase Pattern, Action Pattern

Goal: Clean, Modular, Reusable, Maintainable, Context-Aware, Agent-Friendly, Production Ready.

Avoid unnecessary complexity. When in doubt, choose the simpler solution
(see Decision Table at the end of this file).

---

# Core Principles

## 1. Do Not Over Engineer

Every layer must have a clear responsibility, AND every abstraction must
solve a real problem that exists NOW — not one that might exist someday.

Bad:
Controller → Service → Manager → Processor → Repository

Good:
Controller → UseCase → Action → Service → Repository

This applies to interfaces too — see Service section.

## 2. Feature First Structure

app/
├── Domains/
│ ├── Table/
│ ├── Menu/
│ ├── Order/        ← includes KDS; a kitchen ticket is an Order, not its own record
│ ├── Reservation/
│ ├── Payment/
│ ├── Inventory/
│ ├── Customer/
│ ├── Employee/
│ ├── Reporting/    ← read-only, crosses domains by design
│ ├── Social/       ← song request, special request, table chat
│ └── System/       ← users, app settings, licensing


Avoid large generic folders. A domain never **writes** into another domain —
that happens by dispatching an Event the other domain listens to. Reading is
different: `Enums`, `DTO`, `Repositories`, and `QueryUseCases` may be imported
across domains, because those are the other domain's own published contracts.
The full table, the audit tool, and the one registered exception are in
ARCHITECTURE.md § Domain Dependencies & Boundary Rule.

---

# Architecture

## Write Flow (create/update/delete/approve/cancel)

HTTP Request → Controller → UseCase → Action → Service → Repository → Database


## Read Flow (listing, filter, dashboard, catalog display)

HTTP Request → Controller → QueryUseCase → Repository → Database


- No Action for pure read.
- No transaction boundary needed.
- `QueryUseCase` only: accept filter params → call Repository → return DTO/Collection.

---

# Layer Responsibility

## Controller

Wrapper only. Authorization, request validation, call UseCase/QueryUseCase, return response.
No business logic.

OrderController@index → OrderIndexQueryUseCase
OrderController@store → CreateOrderUseCase


## UseCase

Orchestrates ONE complete business flow. Transaction boundary lives here.

```php
CreateOrderUseCase
DB::transaction(function () {
    // calls Action(s)
});
```

**Merge rule:** if a UseCase only ever calls exactly ONE Action, and that
Action is not reused anywhere else, don't create a separate Action file —
put the logic directly in the UseCase. Only extract to Action once it's
called from ≥2 places (see `CalculateOrderTotalAction`, which IS reused
by both `CreateOrderUseCase` and `UpdateOrderUseCase` — that one earns
its own file).

## QueryUseCase (NEW)

For read-only flows. Thin — no business rule, no transaction.

GetOrderListQueryUseCase
GetKitchenQueueQueryUseCase
GetMenuCatalogQueryUseCase


## Action

One business operation, reusable across ≥2 UseCases.

CreateOrderAction
CalculateOrderTotalAction
UpdateStockAction
GenerateInvoiceAction


Rule: one action = one responsibility. If it's never reused, it doesn't
need to exist as a separate class (fold into the calling UseCase).

## Service

Domain calculations, shared business rules, integrations.

OrderService
InventoryService
PricingService


**Interface rule (revised):** only bind an interface when multiple
implementations are realistically expected — e.g.:

PaymentServiceInterface → CashPaymentService / QrisPaymentService / TransferPaymentService
NotificationServiceInterface → WhatsappNotificationService / EmailNotificationService


For services with exactly one implementation forever (e.g. `PricingService`,
`InventoryService`), inject the concrete class directly. Don't create an
interface "just in case" — that's the overengineering Core Principle #1
warns against.

## Repository

Query, persist, return models. No business logic.

OrderRepository


**No interface.** A Repository has exactly one implementation, so the same
rule that governs Services governs it: an interface is earned by a second
real implementation, not by the possibility of one. Type-hint the concrete
class and let the container resolve it.

(Earlier versions of this file exempted Repositories, on the grounds that
swapping the data source or mocking in tests was a common need. Neither
happened: one data source, and not a single test ever mocked a repository.
The exemption produced 14 interfaces with one implementation each — the
same "interface just in case" that Core Principle #1 forbids. Removed.)

## DTO

Required at:
- Controller → UseCase / QueryUseCase (if payload has >3 fields, or type-safety matters)
- UseCase → Action (same threshold)

Not required at:
- Action → Repository (Eloquent Model / array / primitive is fine)

Don't create DTO for simple single-field CRUD.

---

# Dependency Injection

One rule, no exceptions by layer: bind an interface only where ≥2 real
implementations exist (payment gateway, notification channel). Everything
else — Repository, Service, UseCase, Action — is type-hinted as the concrete
class and resolved by the container without a binding. Where a genuine
interface does exist, never inject the implementation behind it.

---

# Database Rules

## Repository Owns Queries

Allowed: `OrderRepository::where(...)`
Forbidden: `Controller::where()`, `Livewire::where()`, `Service::where()`

## Prevent N+1

Always eager load: `with()`, `load()`, `loadMissing()`.

## Transactions

Transaction boundary lives in UseCase (write flow only). No nested
transactions unless unavoidable.

---

# Route Organization

Never put all routes in `web.php`. Organize by module:

routes/
├── web.php
├── admin/{orders,menus,inventory,employees}.php
├── cashier/{orders,payments}.php
├── kitchen/kitchen.php
└── customer/reservations.php


`web.php` only loads route files.

---

# View Organization

Blade views are wrappers only:

```blade
<livewire:admin.orders.table />
```

No business logic, no large UI implementation in Blade.

---

# Livewire Rules

## One Component One Purpose

Orders/Table
Orders/Form
Orders/Detail

Avoid: `Orders/Management`

## Query Rule

Never query database directly in `render()`.

```php
// Bad
render() { return Order::paginate(); }

// Good
render() { return $this->getOrderListQueryUseCase->handle($this->filters); }
```

## Lifecycle Structure

```php
class OrderTable extends Component
{
    // Properties
    // Lifecycle: mount(), hydrate(), boot(), rendering(), rendered()
    // Events
    // Actions
    // Custom Methods
    // Render
}
```

## Naming

`OrderTable`, `OrderForm`, `OrderDetail` — not `OrderManager`, `OrderHandler`.

---

# Validation

**Format validation** (required, email, numeric, max length): Form Request
or Livewire Form Object. Never duplicate rules across layers.

**Business rule validation** (stock available, table available, approval
limit): Policy, called from UseCase — NOT from Livewire/FormRequest.

Rule of thumb: does checking this require a DB query or state lookup?
→ Policy, not Livewire.

---

# Authorization

Use Policies. Permission naming convention: `{module}.{action}`.

order.create
order.cancel
reservation.approve
payment.process

Gate::authorize('order.cancel', $order);


Never hardcode role checks (`if(auth()->user()->role === 'admin')`).

---

# State Machine / Status Handling

Order, Reservation, Table, and Menu statuses are **hardcoded Enums** — NOT
database-driven Workflow, and no longer the `table_statuses` / `menu_statuses`
lookup tables the app started with.

**Transition rules live on the Enum itself**, not in a separate Policy class: a
Policy answers *may this user do it*, while *which status may follow which* is a
property of the status. The Enum also owns the UI metadata that used to be
columns (`label()`, `color()`).

```php
enum OrderStatus: string {
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Served = 'served';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $next): bool { /* ... */ }
}
```

**Reading a status back:** `Order`, `Shift`, `SongRequest`, `SpecialRequest`, and
`Subscription` cast the column to their Enum, so `$order->status` *is* an Enum.
`Table`, `Menu`, `Reservation`, and `Payment` still hold a plain string and are
compared as `$table->status === TableStatus::OrderIn->value`. Check the model
before writing the comparison, and prefer moving a model onto the cast when you
are already rewriting the screens that read it.

This is a deliberate choice: this is a single-tenant system where business
flow doesn't need to change per client without a deploy. If that ever
changes (e.g. multi-branch with different approval flows), migrate to a
DB-driven Workflow table at that point — don't build that flexibility
preemptively.

Full state diagram per domain: see `docs/design/{domain}.md`.

---

# Audit Log

Written automatically via Model Observer / Event Listener at the
Infrastructure layer — never written manually inside an Action.
Each domain registers which models are audited; no audit logic inside
UseCase/Action.

---

# UI Rules

## DaisyUI First

Prefer `btn`, `card`, `table`, `modal`, `drawer`, `badge`, `alert`,
`dropdown` before custom Tailwind.

Bad: <div class="bg-white rounded-lg shadow px-6 py-4 border">
Good: <div class="card bg-base-100">


## Consistency

Index: Header → Filters → Table → Pagination
Create/Edit: Header → Form → Actions

---

# Error Handling

Never swallow exceptions. Log with context: `order_id`, `user_id`,
`branch_id`, `action`. Avoid generic logs.

---

# Testing

Priority: UseCase/QueryUseCase → Action → Service → Feature test.
Avoid testing repositories directly unless necessary.

---

# Performance

Eager loading, pagination, caching, indexes. Never load a full table
unnecessarily.

---

# Naming Rules

Business names: `CreateOrderUseCase`, `GetOrderListQueryUseCase`,
`UpdateInventoryAction`, `MenuRepository`, `ReservationService`.
Avoid: `DataManager`, `HelperService`, `GeneralRepository`, `CommonUtility`.

---

# Restaurant Modules

Dashboard, Menu, Category, Order, Order Item, Reservation, Table, Kitchen,
Inventory, Purchase, Supplier, Customer, Employee, Shift, Payment,
Promotion, Report, Settings — same architecture applies to all.

---

# Decision Table

| Question | Answer |
|---|---|
| Is this UI? | Presentation (Controller/View/Livewire) |
| Is this a read/listing without state change? | QueryUseCase → Repository |
| Is this one complete business flow that changes state? | UseCase |
| Is this a small operation reused in ≥2 places? | Action |
| Is this a core business rule (stock check, table availability, approval limit)? | Domain (Policy/Enum) |
| Is this database access? | Repository (concrete class, no interface) |
| Is this a technical integration (payment gateway, WA, WebSocket)? | Service (interface only if ≥2 real implementations) |
| Does the status/flow need to change without a deploy, per client? | Not needed here — hardcoded Enum + Policy (see State Machine section) |
| Can admin toggle behavior without code (feature flag, tax %)? | Configuration |
| Is this a business reference list (Menu Category, Table Category)? | Master Data |

---

# AI Agent Rules

1. Follow existing module structure.
2. Reuse Service before creating a new one.
3. Reuse Repository before creating a new query.
4. Reuse Action before creating new business logic — but don't extract an
   Action for something used only once (see UseCase merge rule).
5. Don't duplicate validation (format in FormRequest, business rule in Policy).
6. Don't duplicate UI components.
7. Keep controllers thin.
8. Keep Livewire focused, reads go through QueryUseCase.
9. Prefer composition over inheritance.
10. Keep code readable over clever.
11. Before generating anything, check it against the Decision Table above.
12. If genuinely ambiguous, ask — don't assume.

Always choose simplicity when multiple solutions are valid.