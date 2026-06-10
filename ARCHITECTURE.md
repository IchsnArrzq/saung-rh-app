# ARCHITECTURE.md

# Restaurant POS & Table Management System

## Overview

Restaurant Management System adalah aplikasi untuk mengelola operasional restoran dan kafe.

Sistem terdiri dari dua portal:

1. Customer Portal
2. Admin Portal

Sistem mendukung:

* Table Management
* Reservation
* Digital Ordering
* POS
* Kitchen Display System (KDS)
* Payment
* Reporting
* User & Permission Management

---

# Business Goals

Tujuan sistem:

* Mempermudah operasional restoran
* Mengurangi human error
* Mempercepat proses order
* Monitoring penjualan secara realtime
* Mendukung dine-in reservation
* Mendukung kitchen workflow secara realtime

---

# User Roles

## Super Admin

Responsibilities:

* User Management
* Role Management
* Permission Management
* System Configuration

---

## Admin

Responsibilities:

* Menu Management
* Table Management
* Reservation Management
* Order Monitoring
* Payment Monitoring
* Reporting
* KDS Monitoring

---

## Cashier

Responsibilities:

* Create Order
* Manage POS
* Process Payment
* Print Receipt

---

## Customer

Responsibilities:

* Reservation
* View Menu
* Create Reservation Order

---

# System Portals

## Customer Portal

Purpose:

Customer self-service.

Modules:

* Home
* Menu Catalog
* Reservation
* Reservation History

---

## Admin Portal

Purpose:

Restaurant operation management.

Modules:

* Dashboard
* Table Management
* Reservation
* POS
* Kitchen Display System
* Menu Management
* Reporting
* User Management

---

# Core Domains

## Table Domain

Manage restaurant tables.

Entities:

* Table
* TableCategory
* TableStatus

Statuses:

* Available
* Occupied
* Ordered
* Cleaning

Rules:

* Reservation may lock table.
* Active order occupies table.
* Completed order releases table.

---

## Menu Domain

Manage products.

Entities:

* Menu
* MenuCategory
* MenuStatus

Statuses:

* Available
* Unavailable

---

## Reservation Domain

Manage table reservations.

Entities:

* Reservation
* ReservationItem

Flow:

Customer

↓

Choose Date

↓

Choose Table

↓

Choose Menu

↓

Create Reservation

↓

Admin Approval

↓

Check In

↓

Auto Create Order

↓

Auto Create Order Item

---

## Order Domain

Manage customer orders.

Entities:

* Order
* OrderItem

Order Types:

* Offline
* Online
* Reservation

Order Sources:

* POS
* Reservation
* Future Online Ordering

---

## Payment Domain

Manage payment process.

Entities:

* Payment
* PaymentMethod

Payment Methods:

* Cash
* QRIS
* Transfer
* E-Wallet

Rules:

Successful payment may:

* create order
* complete order
* close transaction

depending on business flow.

---

## Kitchen Domain

Manage food preparation.

Entities:

* KitchenTicket
* KitchenQueue

Statuses:

* Pending
* Preparing
* Ready
* Served

---

## Reporting Domain

Generate business reports.

Reports:

* Daily Sales
* Monthly Sales
* Top Selling Menu
* Sales Per Cashier
* Reservation Report
* Payment Report

---

# High Level Business Flow

## Offline POS Flow

Cashier

↓

Select Table

↓

Create Order

↓

Add Menu

↓

Submit Order

↓

Send To Kitchen

↓

Kitchen Prepare

↓

Payment

↓

Order Completed

↓

Table Available

---

## Reservation Flow

Customer

↓

Create Reservation

↓

Choose Table

↓

Choose Menu

↓

Reservation Confirmed

↓

Table Locked

↓

Customer Check In

↓

Create Order

↓

Send To Kitchen

↓

Payment

↓

Order Completed

↓

Table Released

---

## Kitchen Flow

Order Created

↓

Kitchen Ticket Generated

↓

WebSocket Event

↓

KDS Receive Order

↓

Preparing

↓

Ready

↓

Served

---

# Realtime Architecture

## WebSocket

Used By:

* Kitchen Display System
* Table Status Updates
* Order Status Updates

Technology:

Laravel Reverb

or

Laravel WebSocket

Events:

OrderCreated

OrderUpdated

KitchenStatusUpdated

TableStatusUpdated

PaymentCompleted

---

# System Relationships

Reservation

↓

creates

↓

Order

↓

contains

↓

OrderItem

↓

generates

↓

KitchenTicket

↓

completed by

↓

Payment

---

# Domain Dependencies

Table

← Reservation

← Order

Order

← Payment

← Kitchen

Menu

← OrderItem

Reservation

→ Order

Payment

→ Order

Kitchen

← Order

---

# Folder Structure

app/

├── Domains/
│
├── Table/
│
├── Menu/
│
├── Reservation/
│
├── Order/
│
├── Payment/
│
├── Kitchen/
│
├── Reporting/
│
└── User/

---

# Request Flow

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

# Livewire Strategy

Index Page

Controller

↓

Table Component

Create Page

Controller

↓

Form Component

Edit Page

Controller

↓

Form Component

Controller only acts as wrapper.

---

# Route Strategy

routes/

├── admin/
│   ├── dashboard.php
│   ├── tables.php
│   ├── menus.php
│   ├── reservations.php
│   ├── orders.php
│   ├── payments.php
│   ├── reports.php
│   └── users.php
│
└── customer/
├── home.php
├── menu.php
└── reservations.php

web.php only loads route files.

---

# Architectural Decisions

## Why Livewire

* Fast CRUD development
* Server driven UI
* Minimal frontend complexity

---

## Why Repository Pattern

* Query separation
* Easier maintenance
* Consistent data access

---

## Why UseCase Pattern

* Business flow orchestration
* Prevent fat controllers

---

## Why Action Pattern

* Single responsibility
* Reusable business operations

---

# Non Functional Requirements

Performance

* Pagination required
* Eager loading required
* Prevent N+1

Security

* Policy based authorization
* Permission based access

Maintainability

* Feature First Structure
* Domain Driven Modules

Scalability

* Realtime events decoupled from business logic
* Queue for heavy jobs

Observability

* Audit logs
* Activity logs
* Error monitoring
