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

# UX Psychology Principles

## Purpose Of This Section

Every rule above answers "what does it look like".

This section answers "why does it work".

Use it when:

* Deciding layout, order, or emphasis
* Reviewing a screen before calling it done
* Justifying a design decision in a report or review

Source disciplines: cognitive psychology, perception, HCI, behavioral economics, emotional design.

No official complete list exists. This is the working set for this system.

---

## Tier 1 — Always Apply

These 25 cover most day-to-day decisions.

Check them on every screen:

| # | Principle | One-line test |
|---|---|---|
| 1 | Hick's Law | Can I reduce the number of visible choices? |
| 2 | Fitts's Law | Is the main target big enough and close enough? |
| 3 | Miller's Law | More than 7 items without grouping? |
| 4 | Jakob's Law | Does this behave like apps users already know? |
| 5 | Tesler's Law | Is the system absorbing the complexity, not the user? |
| 6 | Doherty Threshold | Does feedback appear under 400 ms? |
| 7 | Gestalt Principles | Does grouping match meaning? |
| 8 | Visual Hierarchy | Is the most important thing the most visible thing? |
| 9 | Cognitive Load Theory | What can I remove without losing meaning? |
| 10 | Chunking | Is long content broken into digestible blocks? |
| 11 | Recognition over Recall | Am I forcing the user to remember something? |
| 12 | Mental Models | Does this match how a restaurant actually works? |
| 13 | Progressive Disclosure | Is advanced detail hidden until needed? |
| 14 | Affordance | Does it look like what it does? |
| 15 | Signifier | Is there a visible cue for how to use it? |
| 16 | Feedback Principle | Does every action produce a visible response? |
| 17 | Error Prevention | Can the mistake be made impossible instead of reported? |
| 18 | Aesthetic-Usability Effect | Is it clean enough to feel easy? |
| 19 | Von Restorff Effect | Does the key action stand out from its neighbours? |
| 20 | Peak-End Rule | Is the ending of this flow memorable and positive? |
| 21 | Zeigarnik Effect | Is unfinished work visible to the user? |
| 22 | Goal Gradient Effect | Can the user see how close they are to done? |
| 23 | Color Psychology | Is color meaning consistent with the token system? |
| 24 | White Space Effect | Is there room to breathe? |
| 25 | Nielsen's 10 Heuristics | Run the checklist at the end of this section. |

---

# A. Perception

## 1. Gestalt Principles

How the eye groups things before the brain reads them.

| Law | Meaning | Rule in this system |
|---|---|---|
| Proximity | Close items are read as one group | Use `gap-4` inside a group, `mb-6` between groups. Never equal spacing for both. |
| Similarity | Similar-looking items are read as same type | Same element type = same class. Do not restyle one button in a row. |
| Continuity | The eye follows lines and alignment | Keep one alignment axis per column. Labels left, numbers right. |
| Closure | The mind completes incomplete shapes | Cards may drop borders; theme is `--border: 0`. Separate with `bg-base-200`. |
| Figure-Ground | Foreground must separate from background | Modal over backdrop, `base-100` over `base-200`. Never same surface on same surface. |
| Common Fate | Items moving together are read as related | Animate a group as one unit, never item by item. |
| Prägnanz | The simplest reading wins | If a layout needs explanation, it is wrong. |
| Symmetry | Symmetric shapes are read as one object | Keep card grids to equal heights. |
| Connectedness | Connected items beat merely close items | A shared container beats spacing for grouping. |
| Uniform Connectedness | Shared background = shared meaning | Group form fields in one `fieldset` surface, not floating. |

Gestalt outranks decoration. Fix grouping before adding borders.

---

## 2. Visual Hierarchy

Priority is expressed through:

* Size
* Color
* Contrast
* White space
* Position
* Typography

Rule:

Each screen has exactly one primary focus.

Follow the heading scale defined in Typography.

One `btn-primary` per section — already required in Button Hierarchy.

---

## 3. Von Restorff Effect

The different item is the remembered item.

Rule:

The main action must differ from its neighbours in shape or color.

Never place two `btn-primary` side by side. Difference stops working when repeated.

---

## 4. Aesthetic-Usability Effect

Attractive interfaces are perceived as easier to use.

Rule:

Never ship a card with a broken or missing image placeholder as the normal state.

Menu images are content, not decoration. Missing image = missing trust.

---

## 5. Processing Fluency

Easy to process = liked more.

Rule:

Prefer familiar words over internal terms.

Bad: `no_show`, `order_in`, `stock_opname_draft`

Good: use the Enum `label()` method. Never print a raw status value in customer-facing UI.

---

## 6. Signal-to-Noise Ratio

Remove what does not inform.

Rule:

Every element must answer a question the user is actually asking.

Internal-only data (technical status, null-state buckets, IDs) must not reach the customer portal.

---

## 7. Color Psychology

Color carries meaning before text is read.

| Color | Meaning | Token |
|---|---|---|
| Blue | Trust, information | `info` |
| Green | Safe, confirmed, success | `accent`, `success` |
| Red | Danger, stop | `error` |
| Yellow / Amber | Attention, waiting | `warning` |

Conflict warning for this theme:

`primary` is red (`#ff4f55`) and `error` is also red (`#ef4444`).

Rule:

Red alone never signals danger here.

Destructive actions require all three:

1. `btn-error`
2. An explicit verb in the label ("Hapus", "Batalkan")
3. A confirmation step

Never rely on color alone for any status. Always pair with text or icon — see Accessibility Psychology.

---

## 8. White Space Effect

Empty space increases focus and perceived quality.

Rule:

Do not fill space just because it exists.

Use the spacing scale in Page Spacing. Increase space before adding dividers.

---

# B. Cognitive Psychology

## 9. Hick's Law

More options, slower decision.

Rule:

Show what is actionable first. Collapse or de-emphasize what is not.

Applies to:

* Table selection — available tables first, unavailable collapsed
* Menu grid — category filter before the full list
* Sidebar — group by business flow, not alphabetically

---

## 10. Miller's Law

Working memory is limited.

Rule:

More than 7 items in a group means it needs grouping.

Long forms get sections. Long lists get categories.

---

## 11. Cognitive Load Theory

Three loads:

* Intrinsic — inherent difficulty of the task. Cannot be removed.
* Extraneous — difficulty added by bad design. Must be removed.
* Germane — effort that builds understanding. Keep this.

Rule:

Attack extraneous load first.

Every unexplained field, jargon label, and hidden dependency is extraneous load.

---

## 12. Chunking

Group information into small units.

Rule:

Format for the eye:

* Currency grouped by thousands
* Phone and code fields spaced
* Multi-part forms split into steps

---

## 13. Recognition over Recall

Recognizing beats remembering.

Rule:

Prefer a visual picker over a dropdown of codes.

Bad: `<select>` listing table codes.

Good: a card grid showing code, capacity, and status.

Show the current selection at all times. Never make the user scroll back to check.

---

## 14. Serial Position Effect

First and last items are remembered best.

Rule:

Put the most important item first and the call to action last.

In tables, primary identity is the first column and actions are the last.

---

## 15. Peak-End Rule

An experience is judged by its peak and its ending.

Rule:

Every completed flow needs a real ending, not just a flash message.

A submitted order or reservation must show:

* Confirmation number
* What happens next
* Estimated time when available

This is the highest-leverage screen in the entire customer flow.

---

## 16. Zeigarnik Effect

Unfinished tasks stay in mind.

Rule:

In-progress work must stay visible.

A non-empty cart is always visible on every breakpoint — sticky bar on mobile, sticky panel on desktop.

Never hide progress below the fold.

---

## 17. Goal Gradient Effect

Motivation increases near the goal.

Rule:

Show progress in any flow of two or more steps.

Use daisyUI `steps`. Show running totals in carts.

---

## 18. Cognitive Dissonance

Broken expectations cause discomfort.

Rule:

The result must match the promise of the control.

If a price is labeled "estimasi", the final bill must explain the difference.

Never change meaning between two screens of the same flow.

---

## 19. Mental Models

Users arrive with a model of how things work.

Rule:

Match the real restaurant workflow.

Table → order → kitchen → serve → pay.

The UI order must follow the physical order. Do not reorder for technical convenience.

---

## 20. Schema Theory

Prior knowledge shapes new understanding.

Rule:

Reuse conventions from apps the user already knows — cart, receipt, order tracking.

Do not invent new metaphors for solved problems.

---

# C. Interaction Laws

## 21. Fitts's Law

Time to hit a target depends on its size and distance.

Rule:

Minimum touch target 44×44 px in the customer portal.

Primary actions go where the thumb reaches — bottom of the screen on mobile.

`btn-sm btn-square` icon-only buttons are acceptable in admin tables, never as the sole primary action on mobile.

---

## 22. Steering Law

Wide paths are easier to follow than narrow ones.

Rule:

Avoid narrow hover corridors, nested submenus, and tight drag lanes.

---

## 23. Accot-Zhai Steering Law

Extends Steering Law to continuous navigation such as drag-and-drop.

Rule:

Any drag interaction needs a generous drop zone and a visible target state.

Always provide a non-drag fallback — drag is not accessible.

---

## 24. Doherty Threshold

Response under 400 ms keeps attention.

Rule:

Every `wire:click` that hits the server needs `wire:loading` state.

Every `wire:model.live` needs `.debounce.300ms`.

If the work is genuinely slow, show a skeleton — perceived speed counts.

---

## 25. Tesler's Law

Complexity cannot be removed, only moved.

Rule:

The system absorbs complexity, not the user.

Compute defaults, derive totals, prefill known data. Never ask for what can be inferred.

---

## 26. Pareto Principle

80% of users use 20% of features.

Rule:

The 20% goes on the main screen.

Everything else goes behind Progressive Disclosure.

---

## 27. Parkinson's Law

Work expands to fill available time.

Rule:

Do not add artificial delays or unnecessary steps.

Fewer steps and faster response produce faster completion.

---

# D. Behavioral Psychology

Read Ethical Guardrails before applying anything in this group.

## 28. Operant Conditioning

Rewards reinforce behavior.

Applies to staff tooling: completion counters, streaks on shift targets.

---

## 29. Classical Conditioning

Consistent pairing creates association.

Rule:

Always pair success with the same color, sound, and motion. Consistency is what builds the association.

---

## 30. Habit Loop

Cue → Routine → Reward.

Applies to repeated staff tasks: cue (new order appears), routine (process), reward (visible clearing of the queue).

---

## 31. Variable Reward

Unpredictable rewards drive compulsion.

Not used in this system. See Ethical Guardrails.

---

## 32. Reinforcement Theory

Rewarded behavior repeats.

Rule:

Reward the behavior you want operationally — accurate stock opname, complete order notes.

---

# E. Behavioral Economics

Read Ethical Guardrails before applying anything in this group.

## 33. Loss Aversion

Losing hurts more than gaining pleases.

Rule:

Warn before destructive loss — cart reset, cancelled booking, deleted draft.

Already implemented via `data-confirm`.

---

## 34. Scarcity Effect

Limited things feel more valuable.

Rule:

Only display scarcity that is real and read from live data.

Allowed: "Sisa 3 meja", "Stok habis" from actual stock.

Forbidden: countdown timers or "hampir habis" not backed by data.

---

## 35. Anchoring Bias

The first number seen becomes the reference.

Rule:

Show the total before asking for confirmation.

Package price before per-item price when a package exists.

---

## 36. Framing Effect

Presentation changes the decision.

Rule:

Frame honestly and positively.

Good: "Tersedia jam 19:00"

Bad: "Gagal, jam 18:00 penuh"

Never frame a fee as a discount.

---

## 37. Decoy Effect

A third option steers the choice.

Rule:

Only when all options are genuinely purchasable. No fake tiers.

---

## 38. Endowment Effect

People value what they already hold.

Rule:

Preserve the cart across sessions. Something built feels owned.

---

## 39. Commitment and Consistency

People stay consistent with earlier choices.

Rule:

Small step first. Ask for table and time before asking for full details.

---

## 40. Default Effect

Defaults are usually accepted.

Rule:

Defaults must serve the user, not revenue.

Good default: nearest available time, pax = 2.

Forbidden default: pre-ticked add-ons or pre-selected upsells.

---

## 41. Social Proof

People follow others.

Rule:

"Terlaris" and rating badges must come from real order counts. Never hardcoded.

---

## 42. Authority Bias

Expert recommendation carries weight.

Rule:

"Rekomendasi Chef" must be an actual flag set by the restaurant, stored in data.

---

## 43. Reciprocity

Given something, people give back.

Rule:

Free value first — free wifi info, welcome drink, loyalty points earned.

---

## 44. Bandwagon Effect

Popularity signals trust.

Rule:

Same as Social Proof. Real numbers only.

---

# F. Emotional Design

## 45. Emotional Design (Norman)

Three levels:

* Visceral — first impression, look and feel
* Behavioral — how it feels to use
* Reflective — the story the user tells afterward

Rule:

Behavioral wins when they conflict. See Design Priorities.

---

## 46. Delight Principle

Small pleasant surprises build affection.

Rule:

Delight only after the task succeeds. Never in the middle of a flow.

---

## 47. Microinteraction Psychology

Small animations increase satisfaction and communicate state.

Rule:

Motion must carry meaning — added, removed, loading, arrived.

Keep transitions 150–300 ms. Respect `prefers-reduced-motion`.

---

## 48. Emotional Contagion

The interface's mood transfers to the user.

Rule:

Error messages stay calm and helpful. Never blame the user.

Bad: "Input Anda salah"

Good: "Waktu reservasi harus setelah jam sekarang"

---

# G. Nielsen's 10 Usability Heuristics

Run this as a review pass.

| # | Heuristic | Rule here |
|---|---|---|
| 49 | Visibility of system status | Order status, table status, and loading state always visible |
| 50 | Match system and real world | Indonesian labels, restaurant vocabulary, no database terms |
| 51 | User control and freedom | Every flow has Cancel and Back. Nothing is a trap. |
| 52 | Consistency and standards | One pattern per task across the whole app |
| 53 | Error prevention | Constrain input before validating it |
| 54 | Recognition rather than recall | See principle 13 |
| 55 | Flexibility and efficiency | Search and shortcuts for staff, guided flow for customers |
| 56 | Aesthetic and minimalist design | See Signal-to-Noise Ratio |
| 57 | Help users recover from errors | Say what happened and what to do next |
| 58 | Help and documentation | Inline hints beat a separate manual |

---

# H. Modern UX Principles

## 59. Progressive Disclosure

Show the essentials, reveal detail on demand.

Rule:

Advanced options collapse by default. Details live in a modal or a second step.

---

## 60. Progressive Reduction

The interface simplifies as the user gains skill.

Rule:

Optional. Never hide something the user still needs.

---

## 61. Progressive Onboarding

Teach at the moment of need.

Rule:

No upfront tour. Explain a feature the first time it is used.

---

## 62. Forgiveness Principle

Mistakes must be recoverable.

Rule:

Prefer Undo over a confirmation dialog for reversible actions.

Confirmation is for the irreversible.

---

## 63. Affordance

An object suggests its own use.

Rule:

Clickable things look clickable. Disabled things look disabled and explain why.

Never render a dead control that looks like a live button.

---

## 64. Signifier

A visible cue that explains the function.

Rule:

Hover is not a signifier — touch screens have no hover.

Any action reachable only on hover is broken on mobile.

---

## 65. Feedback Principle

Every action gets a response.

Rule:

Within 400 ms, something must change on screen.

Add to cart: quantity updates on the card itself, not only in the panel.

---

## 66. Constraints

Prevent wrong actions.

Rule:

Use `min`, `max`, `maxlength`, date bounds, and disabled unavailable options.

Constrain first, validate second.

---

## 67. Mapping

Control layout should match the real-world arrangement.

Rule:

Table layout on screen should reflect the floor plan where possible.

Quantity minus on the left, plus on the right. Always.

---

## 68. Consistency Principle

Same thing, same look, same behavior.

Rule:

One task, one pattern. If two screens select a table, they use the same component.

---

## 69. Learnability

Easy to learn the first time.

Rule:

A new waiter should complete an order without training.

---

## 70. Discoverability

Features can be found.

Rule:

If it is only reachable by hover, long-press, or memorized URL, it is undiscoverable.

---

## 71. Memorability

Easy to return to after time away.

Rule:

Stable navigation. Do not move menu items between releases.

---

## 72. Error Tolerance

The system keeps helping after a mistake.

Rule:

Preserve form input on validation failure. Never clear the form.

---

## 73. Accessibility Psychology

Design for real limitations — color blindness, motor control, screen readers.

Rule:

* Status = color **and** text. Never color alone.
* Every input has a `label`. Every icon button has `aria-label`.
* Touch targets ≥ 44 px.
* Contrast checked in both themes.

---

## 74. Inclusive Design

Usable by as many people as possible.

Rule:

Assume a noisy restaurant, a cracked screen, one hand, bad lighting, slow network.

---

## 75. Trust Signals

Elements that create a feeling of safety.

Rule:

Payment screens show the total, the method, and what happens next before the confirm button.

Show restaurant contact info and a clear cancellation policy on reservations.

---

# I. Cialdini's Principles of Persuasion

* Reciprocity
* Commitment and Consistency
* Social Proof
* Authority
* Liking
* Scarcity
* Unity

Rule:

Persuasion is allowed only where the underlying claim is true.

See Ethical Guardrails.

---

# J. Additional Laws and Standards

* Jakob's Law — users expect this app to work like other apps
* Postel's Law — accept input liberally, output strictly. Trim spaces, accept `08xx` and `+62`, store one canonical format.
* Occam's Razor — choose the simplest solution that works
* KISS — keep it simple
* CRAP — Contrast, Repetition, Alignment, Proximity
* ISO 9241-210 — human-centred design process
* Shneiderman's Eight Golden Rules — consistency, shortcuts, feedback, closure, error handling, easy reversal, user control, reduced memory load

---

# Ethical Guardrails

Behavioral and persuasion principles can become dark patterns.

This system does not ship dark patterns.

Forbidden:

* Fake scarcity or countdowns not backed by data
* Fake social proof, fake ratings, fake "terlaris"
* Pre-ticked add-ons or opt-outs
* Hiding fees until the final step
* Confirmshaming ("Tidak, saya tidak mau hemat")
* Making cancellation harder than booking
* Variable reward loops designed to maximize time in app

Test:

If the principle only works because the user is misinformed, it is forbidden.

Persuasion is allowed when the claim is true and the user still wins.

---

# Psychology Review Checklist

Run before marking a screen done. Complements the daisyUI checklist in `docs/DAISYUI-BLUEPRINT.md`.

* [ ] One primary action, visually distinct — Visual Hierarchy, Von Restorff
* [ ] Grouping matches meaning — Gestalt
* [ ] Nothing important reachable only by hover — Signifier, mobile
* [ ] Every action gives feedback under 400 ms — Doherty, Feedback
* [ ] Loading, empty, and error states all present — Visibility of Status
* [ ] No raw status values or internal jargon in the UI — Processing Fluency
* [ ] Status shown as color **and** text — Accessibility
* [ ] Unfinished work stays visible — Zeigarnik
* [ ] Multi-step flows show progress — Goal Gradient
* [ ] The flow ends with a real confirmation — Peak-End
* [ ] Destructive actions are confirmable or undoable — Loss Aversion, Forgiveness
* [ ] Input preserved after validation failure — Error Tolerance
* [ ] Touch targets ≥ 44 px on customer screens — Fitts
* [ ] Same task uses the same component everywhere — Consistency
* [ ] No dark patterns — Ethical Guardrails

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
11. Run the Psychology Review Checklist before calling a screen done.
12. Never implement a pattern listed under Ethical Guardrails.

Consistency > Creativity

Predictability > Novelty

Maintainability > Decoration
