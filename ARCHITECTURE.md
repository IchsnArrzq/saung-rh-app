# ARCHITECTURE.md
# Restaurant POS & Table Management System

Explains the WHY behind AGENTS.md rules and the system-level shape.
Domain-level entity schemas and state diagrams live in `docs/design/{domain}.md`,
not here — see § Related Docs.

## Overview

Two portals:
1. Customer Portal — self-service (menu browsing, reservation)
2. Admin Portal — operations (table, order, POS, KDS, reporting, users)

Core capabilities: Table Management, Reservation, Digital Ordering, POS,
Kitchen Display System (KDS), Payment, Reporting, User & Permission Management.

## Business Goals

* Simplify restaurant operations, reduce human error
* Speed up order processing
* Realtime sales monitoring
* Support dine-in reservation and realtime kitchen workflow

## User Roles

| Role | Responsibilities |
|---|---|
| Super Admin | User/Role/Permission management, system configuration |
| Admin | Menu, Table, Reservation, Order monitoring, Payment monitoring, Reporting, KDS monitoring |
| Cashier | Create order, POS, process payment, print receipt |
| Customer | Reservation, view menu, create reservation order |

## System Portals

**Customer Portal:** Home, Menu Catalog, Reservation, Reservation History
**Admin Portal:** Dashboard, Table Management, Reservation, POS, KDS, Menu Management, Reporting, User Management

## Core Domains (summary — full schema/state machine in DESIGN.md per domain)

| Domain | Responsibility | Design doc |
|---|---|---|
| Table | Table availability & status | `docs/design/table.md` |
| Menu | Product catalog | `docs/design/menu.md` |
| Reservation | Table booking lifecycle | `docs/design/reservation.md` |
| Order | Order lifecycle (POS/online/reservation-originated) | `docs/design/order.md` |
| Payment | Payment processing across methods | `docs/design/payment.md` |
| Kitchen | Food preparation queue (KDS) | `docs/design/kitchen.md` |
| Reporting | Business reports | `docs/design/reporting.md` |

## High-Level Cross-Domain Flow

These flows cross domain boundaries — this is why they live here in
ARCHITECTURE.md rather than in a single domain's DESIGN.md.

### Offline POS Flow

Cashier → Select Table → Create Order → Add Menu → Submit Order
→ Send to Kitchen → Kitchen Prepare → Payment → Order Completed → Table Available


### Reservation Flow

Customer → Create Reservation → Choose Table → Choose Menu
→ Reservation Confirmed → Table Locked → Customer Check-In
→ Create Order → Send to Kitchen → Payment → Order Completed → Table Released


### Kitchen Flow

Order Created → Kitchen Ticket Generated → WebSocket Event
→ KDS Receive Order → Preparing → Ready → Served


## Realtime Architecture

Used by: Kitchen Display System, Table Status Updates, Order Status Updates.
Technology: Laravel Reverb (or Laravel WebSockets).

Events: `OrderCreated`, `OrderUpdated`, `KitchenStatusUpdated`,
`TableStatusUpdated`, `PaymentCompleted`.

Realtime events are decoupled from business logic — Actions dispatch
events, WebSocket layer only broadcasts, it never contains business rules.

## System Relationships

Reservation → creates → Order → contains → OrderItem
→ generates → KitchenTicket → completed by → Payment


## Domain Dependencies & Boundary Rule

Table ← Reservation ← Order
Order ← Payment, ← Kitchen
Menu ← OrderItem
Reservation → Order
Payment → Order
Kitchen ← Order


**The rule is about who may WRITE, not about imports in general** (settled in
Fase D, after the original blanket ban collided with Reporting — which by design
reads across domains):

| Imported across domains | Allowed? | Why |
|---|---|---|
| `Events`, `Listeners` | ✅ | This *is* the boundary |
| `Enums`, `DTO` | ✅ | Values, not behaviour |
| `Repositories`, `QueryUseCases` | ✅ | A domain's own read/persistence contract |
| `UseCases`, `Actions`, `Services` | ❌ | Writing into another domain must be a **reaction to an Event** |

Cross-domain effects (e.g. an order claiming its table) happen via Events
listened to by the target domain — not a direct `TableService` call from inside
the Order domain. Listeners are registered explicitly in
`AppServiceProvider::registerDomainListeners()`; Laravel only auto-discovers
`app/Listeners`, and the explicit list doubles as the map of which domain reacts
to which.

Exceptions are **recorded, not tolerated silently**: `scratchpad/audit_domain_deps.php`
classifies every cross-domain `use App\Domains\...` by layer, exits non-zero when a
domain writes into another, and also reports registered exceptions that are no
longer used so the list can't rot. One exception stands today —
`Order → Payment\RecordPaymentAction`, because settling a bill *is* the payment
(one transaction, and the caller needs the Payment row back; a listener can't
return a value).

## Folder Structure

app/Domains/{Table,Menu,Reservation,Order,Payment,Inventory,Customer,Employee,Reporting,Social,System}/

**There is no `Kitchen` domain.** A kitchen ticket is not a separate record — it is
an Order in `preparing`/`ready`, so KDS lives in the Order domain
(`AdvanceKitchenTicketUseCase`, `GetKitchenQueueQueryUseCase`). Splitting it out
would have meant two domains owning one row. Likewise there is no `User` domain:
users, settings, and licensing sit in `System`, and the guest-facing song/chat/
special-request features sit in `Social`.

## Route Strategy

routes/
├── web.php ....... loader only
├── admin.php, customer.php, staff.php, pos.php, kds.php, landing.php, auth.php
├── admin/{dashboard,tables,menus,orders,payments,reservations,reports,customers,users,inventory,system}.php
└── customer/{home,menu,reservations}.php

`web.php` only loads route files. Full layer/flow detail: see AGENTS.md.

## Architectural Decisions

**Why Livewire** — fast CRUD development, server-driven UI, minimal frontend complexity.

**Why Repository Pattern** — query separation, easier maintenance, consistent data access, and Repository is one of the few layers here where an interface earns its keep (swappable data source, easy mocking in tests).

**Why UseCase + QueryUseCase split** — write flows need transaction boundaries and business rule orchestration; read flows don't. Forcing every listing/filter page through the full UseCase→Action chain was pure boilerplate with no benefit — split avoids that.

**Why Action Pattern** — single responsibility, reusable business operations. Only extracted once genuinely reused (see AGENTS.md merge rule) — an Action used exactly once is not worth a separate file.

**Why NOT a DB-driven Workflow (unlike some other projects)** — this is single-tenant; Order/Reservation/Kitchen/Table state transitions don't need to change per client without a deploy. Hardcoded Enum is simpler and sufficient. Revisit only if multi-branch custom flow becomes a real requirement.

**Why transition rules live inside the Enum, not a Policy class** — the plan said "Enum + Policy"; in practice `canTransitionTo()` sits on the Enum itself. A Policy answers *may this user do it*; which status may follow which is a property of the status, and a separate class for it would have been a file whose only job is a `match` on the very Enum it takes as an argument.

**Why the Enum is the vocabulary but not always an Eloquent cast** — `Order`, `Shift`, `SongRequest`, `SpecialRequest`, and `Subscription` cast `status` to their Enum; `Table`, `Menu`, `Reservation`, and `Payment` keep a plain string column and use the Enum as the source of the allowed values (`TableStatus::Reserved->value`). The split is not principled, it is migration order: casting a column changes every Blade comparison that touches it, so it was done where the screens were already being rewritten. **When touching those screens, prefer moving them onto the cast** — the Enum is the authority either way, and mixed access styles are the thing to retire, not preserve.

## Non-Functional Requirements

**Performance:** pagination required, eager loading required, prevent N+1.
**Security:** policy-based authorization, permission-based access (`module.action` convention).
**Maintainability:** Feature-first structure, domain-driven modules, no cross-domain *writes* (see § Domain Dependencies & Boundary Rule for what may still be imported).
**Scalability:** realtime events decoupled from business logic, queue for heavy jobs.
**Observability:** audit logs (via Observer, see AGENTS.md), activity logs, error monitoring.

## Related Docs

- `AGENTS.md` — operational rules, layer responsibilities, decision table
- `docs/design/{domain}.md` — per-domain entity schema, state diagram, contracts