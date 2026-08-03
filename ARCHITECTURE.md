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


Per AGENTS.md § Feature First Structure: domains do NOT import each
other's classes directly. Cross-domain effects (e.g. Reservation
check-in creating an Order) happen via Events, listened to by the
target domain — not a direct `OrderService` call from inside the
Reservation domain.

## Folder Structure

app/Domains/{Table,Menu,Reservation,Order,Payment,Kitchen,Reporting,User}/


## Route Strategy

routes/
├── admin/{dashboard,tables,menus,reservations,orders,payments,reports,users}.php
└── customer/{home,menu,reservations}.php

`web.php` only loads route files. Full layer/flow detail: see AGENTS.md.

## Architectural Decisions

**Why Livewire** — fast CRUD development, server-driven UI, minimal frontend complexity.

**Why Repository Pattern** — query separation, easier maintenance, consistent data access, and Repository is one of the few layers here where an interface earns its keep (swappable data source, easy mocking in tests).

**Why UseCase + QueryUseCase split** — write flows need transaction boundaries and business rule orchestration; read flows don't. Forcing every listing/filter page through the full UseCase→Action chain was pure boilerplate with no benefit — split avoids that.

**Why Action Pattern** — single responsibility, reusable business operations. Only extracted once genuinely reused (see AGENTS.md merge rule) — an Action used exactly once is not worth a separate file.

**Why NOT a DB-driven Workflow (unlike some other projects)** — this is single-tenant; Order/Reservation/Kitchen/Table state transitions don't need to change per client without a deploy. Hardcoded Enum + Policy is simpler and sufficient. Revisit only if multi-branch custom flow becomes a real requirement.

## Non-Functional Requirements

**Performance:** pagination required, eager loading required, prevent N+1.
**Security:** policy-based authorization, permission-based access (`module.action` convention).
**Maintainability:** Feature-first structure, domain-driven modules, no cross-domain class imports.
**Scalability:** realtime events decoupled from business logic, queue for heavy jobs.
**Observability:** audit logs (via Observer, see AGENTS.md), activity logs, error monitoring.

## Related Docs

- `AGENTS.md` — operational rules, layer responsibilities, decision table
- `docs/design/{domain}.md` — per-domain entity schema, state diagram, contracts