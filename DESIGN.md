# DESIGN.md

# Restaurant Management System Design Guidelines

## Purpose

This document defines UI and UX standards for the Restaurant Management System.

Goals:

* Consistent UI
* Predictable UX
* Reusable Components
* AI-Friendly Design System
* Easy Maintenance
* Fast Development

All generated UI must follow this document.

---

# Design Philosophy

## Use Existing System

Always prefer:

daisyUI Components

Before:

Custom Tailwind Classes

Reason:

Consistency is more important than uniqueness.

---

## One Theme Rule

Use one theme for the entire application.

Never create page-specific themes.

Never introduce random colors.

Use:

* primary
* secondary
* accent
* neutral
* success
* warning
* error
* info

Only.

---

## Design Priorities

Priority order:

1. Usability
2. Readability
3. Consistency
4. Performance
5. Visual Enhancement

Never sacrifice usability for aesthetics.

---

# Layout System

## Application Layout

Structure:

Navbar

↓

Sidebar

↓

Page Header

↓

Content

↓

Footer (Optional)

---

## Content Container

Use:

max-w-7xl mx-auto

For standard pages.

Avoid custom widths unless required.

---

## Page Spacing

Use consistent spacing.

Page:

py-6

Section:

mb-6

Card:

gap-4

Form Group:

gap-4

Avoid arbitrary spacing values.

Bad:

mt-[37px]

Good:

mt-4

mt-6

mt-8

---

# Typography

## Heading Hierarchy

Page Title

text-3xl font-bold

Section Title

text-xl font-semibold

Card Title

card-title

Description

text-sm opacity-70

---

## Text Rules

Avoid:

text-xs

for important information.

Use:

text-sm

as minimum readable size.

---

# Color Rules

## Semantic Colors Only

Success:

success

Warning:

warning

Danger:

error

Information:

info

Primary Action:

primary

Never use hardcoded colors.

Bad:

text-green-500

bg-red-500

border-blue-500

Good:

text-success

bg-error

border-primary

---

# Component Rules

## Button

Always use:

btn

Examples:

btn-primary

btn-secondary

btn-outline

btn-ghost

---

## Button Hierarchy

Primary Action

btn-primary

Secondary Action

btn-outline

Destructive Action

btn-error

Text Action

btn-ghost

Never use more than one primary button per section.

---

## Card

Use card for all content groups.

Pattern:

card
card-body

Avoid custom containers.

---

## Table

Use:

table

table-zebra

For listing data.

Always support:

* Search
* Sort
* Pagination

When applicable.

---

## Badge

Status must use badges.

Examples:

badge-success

badge-warning

badge-error

badge-info

Never display raw status text.

---

## Alert

Use alert component.

Examples:

alert-success

alert-error

alert-warning

alert-info

Never create custom alert designs.

---

## Modal

Use modal for:

* Confirmation
* Quick Form
* Detail Preview

Avoid modal for large workflows.

---

## Drawer

Use drawer for:

* Mobile Navigation
* Filter Panel

Avoid complex nested drawers.

---

# Data Table Standards

## Page Structure

Header

Filters

Table

Pagination

Actions

Always.

---

## Actions Column

Order:

View

Edit

Delete

Never change action order.

---

## Bulk Actions

Position:

Top Left

Examples:

Delete Selected

Export Selected

Assign Selected

---

# Form Standards

## Layout

Simple Form

Single Column

Complex Form

Two Columns

Maximum:

Two Columns

Avoid three-column forms.

---

## Field Order

Label

Input

Hint

Error

Always.

---

## Required Fields

Show:

*

After label.

Example:

Customer Name *

---

## Validation

Show validation below field.

Never show validation only in toast.

---

## Form Actions

Position:

Bottom Right

Order:

Cancel

Submit

Example:

[Cancel]

[Save]

---

# Navigation

## Sidebar

Order menu by business flow.

Good:

Dashboard

Orders

Reservations

Kitchen

Inventory

Reports

Settings

Bad:

Alphabetical order

---

## Active Menu

Always highlight active menu.

Use:

active

state.

---

# Dashboard Rules

## Dashboard Composition

Stats

↓

Recent Activities

↓

Operational Widgets

↓

Charts

---

## KPI Cards

Use stat component.

Examples:

Today's Sales

Active Orders

Reservations

Inventory Alerts

---

# Restaurant Specific UI

## Order Status

Pending

badge-warning

Preparing

badge-info

Ready

badge-success

Completed

badge-success

Cancelled

badge-error

---

## Reservation Status

Pending

badge-warning

Confirmed

badge-success

Checked In

badge-info

Cancelled

badge-error

No Show

badge-error

---

## Payment Status

Unpaid

badge-warning

Paid

badge-success

Refunded

badge-error

---

# Loading States

Never leave blank screen.

Use:

skeleton

loading

spinner

components.

---

# Empty States

Every table must have empty state.

Structure:

Icon

Message

Action

Example:

No Orders Found

Create Order

---

# Responsive Rules

## Mobile First

Design mobile first.

Breakpoints:

sm

md

lg

xl

2xl

Only.

Avoid arbitrary breakpoints.

---

## Table Responsiveness

Wrap table:

overflow-x-auto

Always.

---

# Icons

Use one icon set only.

Recommended:

Heroicons

Never mix icon libraries.

---

# Toast Notifications

Success

toast-success

Error

toast-error

Info

toast-info

Warning

toast-warning

Keep message concise.

---

# Accessibility

All buttons:

Must have label.

All inputs:

Must have label.

All icon buttons:

Must have tooltip.

Use semantic HTML whenever possible.

---

# Dark Mode

All pages must support dark mode.

Never hardcode:

white

black

Use semantic theme colors.

---

# AI Design Rules

When generating UI:

1. Reuse existing components first.
2. Reuse existing layouts first.
3. Follow established page patterns.
4. Never invent new visual styles.
5. Prefer daisyUI components.
6. Prefer semantic colors.
7. Maintain spacing consistency.
8. Maintain typography consistency.
9. Maintain action placement consistency.
10. Optimize for usability over visual creativity.

Consistency > Creativity

Predictability > Novelty

Maintainability > Decoration
