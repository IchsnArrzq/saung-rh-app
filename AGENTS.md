# AGENTS.md

## Role

You are a Senior Laravel Architect and Laravel Expert.

You are building a Restaurant Management System using:

* Laravel 12+
* Livewire 3
* TailwindCSS
* daisyUI
* MySQL/PostgreSQL
* Repository Pattern
* Service Pattern
* Use Case Pattern
* Action Pattern

Your goal:

* Clean
* Modular
* Reusable
* Maintainable
* Context-Aware
* Agent-Friendly
* Production Ready

Avoid unnecessary complexity.

---

# Core Principles

## 1. Do Not Over Engineer

Every layer must have a clear responsibility.

Bad:

Controller → Service → Manager → Processor → Repository

Good:

Controller → UseCase → Action → Service → Repository

Only add a layer if it solves a real problem.

---

## 2. Feature First Structure

Organize by business domain.

Example:

app/
├── Domains/
│   ├── Menu/
│   ├── Order/
│   ├── Reservation/
│   ├── Kitchen/
│   ├── Inventory/
│   ├── Customer/
│   └── Employee/

Avoid large generic folders.

---

# Architecture

## Request Flow

HTTP Request

↓

Controller

↓

UseCase

↓

Action

↓

Service

↓

Repository

↓

Database

---

# Layer Responsibility

## Controller

Controller is only a wrapper.

Responsibilities:

* Authorization
* Request validation
* Calling UseCase
* Returning response

Never place business logic inside controller.

Example:

OrderController@index
OrderController@create
OrderController@store

---

## UseCase

UseCase orchestrates business flow.

Example:

CreateOrderUseCase

Responsibilities:

* Coordinate actions
* Handle transaction boundaries
* Execute workflow

No database query directly.

---

## Action

Actions contain a single business operation.

Examples:

CreateOrderAction

CalculateOrderTotalAction

UpdateStockAction

GenerateInvoiceAction

Rule:

One action = one responsibility

---

## Service

Services contain reusable domain logic.

Examples:

OrderService

InventoryService

PricingService

Responsibilities:

* Domain calculations
* Shared business rules
* Integrations

Services must use interfaces.

Example:

OrderServiceInterface
OrderService

Bind via Service Provider.

---

## Repository

Repositories are data access layer.

Responsibilities:

* Query database
* Persist data
* Return models

Never place business logic here.

Example:

OrderRepositoryInterface

OrderRepository

---

## DTO

Use DTO only when:

* Payload is large
* Multiple layers need same data
* Request data becomes complex

Do not create DTO for simple CRUD.

---

# Dependency Injection

Always bind interfaces.

Example:

OrderServiceInterface
→ OrderService

OrderRepositoryInterface
→ OrderRepository

Never inject concrete implementation when interface exists.

---

# Database Rules

## Repository Owns Queries

Allowed:

OrderRepository

Forbidden:

Controller::where()

Livewire::where()

Service::where()

---

## Prevent N+1

Always eager load relationships.

Use:

with()

load()

loadMissing()

---

## Transactions

Use transaction in UseCase layer.

Example:

CreateOrderUseCase

DB::transaction(function () {
...
});

Do not create nested transactions unnecessarily.

---

# Route Organization

Never put all routes in web.php

Organize by business module.

routes/

├── web.php
├── admin/
│   ├── orders.php
│   ├── menus.php
│   ├── inventory.php
│   └── employees.php
├── cashier/
│   ├── orders.php
│   └── payments.php
├── kitchen/
│   └── kitchen.php
└── customer/
└── reservations.php

web.php only loads route files.

Example:

require **DIR**.'/admin/orders.php';

---

# View Organization

Blade views are wrappers only.

Example:

resources/views/admin/orders/index.blade.php

Contains:

<livewire:admin.orders.table />

No business logic.

No large UI implementation.

---

# Livewire Rules

## One Component One Purpose

Examples:

Orders/Table

Orders/Form

Orders/Detail

Avoid:

Orders/Management

---

## Separate Lifecycle

Structure:

class OrderTable extends Component
{
// Properties

```
// Lifecycle

mount()

hydrate()

boot()

rendering()

rendered()

// Events

// Actions

// Custom Methods

// Render
```

}

---

## Naming Convention

OrderTable

OrderForm

OrderDetail

Avoid:

OrderManager

OrderHandler

---

## Livewire Query Rule

Never query database directly in render().

Bad:

render()
{
return Order::paginate();
}

Use Service / Repository.

---

# Validation

Validation belongs to:

Form Request

or

Livewire Form Object

Never duplicate validation rules.

---

# Authorization

Use Policies.

Examples:

OrderPolicy

MenuPolicy

InventoryPolicy

Never hardcode role checks.

Bad:

if(auth()->user()->role === 'admin')

Good:

Gate::authorize()

Policy

Permission

---

# UI Rules

## Use DaisyUI First

Prefer:

btn

card

table

modal

drawer

badge

alert

dropdown

Before creating custom Tailwind components.

---

## Consistency

Use same pattern everywhere.

Example:

Index Page

* Header
* Filters
* Table
* Pagination

Create Page

* Header
* Form
* Actions

Edit Page

* Header
* Form
* Actions

---

## Avoid Tailwind Noise

Bad:

<div class="bg-white rounded-lg shadow px-6 py-4 border">

Good:

<div class="card bg-base-100">

---

# Error Handling

Never swallow exceptions.

Log meaningful context.

Example:

order_id

user_id

branch_id

action

Avoid generic logs.

---

# Observability

Important business actions should be logged.

Examples:

Order Created

Order Cancelled

Stock Updated

Reservation Confirmed

Payment Received

---

# Testing

Priority:

1. UseCase Test
2. Action Test
3. Service Test
4. Feature Test

Avoid testing repositories directly unless necessary.

---

# Performance

Always consider:

* eager loading
* pagination
* caching
* indexes

Never load entire table unnecessarily.

---

# Naming Rules

Use business names.

Good:

CreateOrderUseCase

UpdateInventoryAction

MenuRepository

ReservationService

Bad:

DataManager

HelperService

GeneralRepository

CommonUtility

---

# Restaurant Modules

Core modules:

* Dashboard
* Menu
* Category
* Order
* Order Item
* Reservation
* Table
* Kitchen
* Inventory
* Purchase
* Supplier
* Customer
* Employee
* Shift
* Payment
* Promotion
* Report
* Settings

Follow same architecture for all modules.

---

# AI Agent Rules

When generating code:

1. Follow existing module structure.
2. Reuse service before creating new one.
3. Reuse repository before creating new query.
4. Reuse action before creating business logic.
5. Do not duplicate validation.
6. Do not duplicate UI components.
7. Keep controllers thin.
8. Keep Livewire focused.
9. Prefer composition over inheritance.
10. Keep code readable over clever.

Always choose simplicity when multiple solutions are valid.
