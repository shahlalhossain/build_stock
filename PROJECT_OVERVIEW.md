# Project Overview — Inventory Management System

**Client:** Single-organization Building Construction Developer Company
**Platform:** Web application only (no public/mobile API)
**Framework:** Laravel 12, MySQL, Blade + jQuery (Velzon admin theme), mobile-responsive
**Status:** Active development — core master-data and vendor-management modules built; stock-transaction functionality not yet started

---

## 1. Purpose

This system is being built to give a single building-construction developer company a
central place to manage the master data behind its supply chain and site operations:
what materials it deals in, who supplies them, and where they are stocked across its
projects and warehouses. It is scoped to one organization — there is no multi-tenant
or multi-company support, and none is planned.

## 2. Current Scope: Master Data & Vendor Management Foundation

As of this snapshot, the system covers **catalog setup, location setup, and vendor
management** — the foundation an inventory system is built on. It does **not yet**
track actual stock quantities, stock movement, purchase orders, goods receipts, or
warehouse transfers. No such tables or screens exist yet. Everything below is
master data: it defines *what things are* and *who's involved*, not *how many of
something exist right now* or *where a unit of stock moved to*.

This is a normal and deliberate build order — catalog and vendor data has to exist
before stock transactions can reference it — but it means "Inventory Management
System" today describes the setup layer, with stock tracking as the logical next
phase.

## 3. Core Modules

### 3.1 Product Catalog

| Module | Purpose |
|---|---|
| **Category / Sub-Category** | Two-level classification tree for construction materials (e.g. Bricks & Blocks → Concrete Block). |
| **Brand** | Manufacturer/brand master (e.g. Holcim, CRH, Hilti), with approval workflow. |
| **Product Unit** | Units of measure grouped by type — Quantity, Weight, Volume, Area, Length, Packaging, Structural, Logistics (e.g. Bag, Metric Ton, Cubic Foot, Sheet). |
| **Attribute / Attribute Value** | Defines product specifications (e.g. Color, Diameter, Grade) and their possible values (e.g. Red, 12mm, Grade 60), attached to products as multi-select specs. |
| **Product** | The catalog item itself — name, code (auto-generated), SKU, category/sub-category, brand, unit, description, and a set of attribute-value specs. Subject to an approval workflow (Pending → Approved/Rejected). |

### 3.2 Locations

| Module | Purpose |
|---|---|
| **Project** | A construction project/site, with a lifecycle state (Proposed → Planning → Developing → Operational/Completed/Postponed/Abandoned), a manager, budget, and dates. |
| **Store / Warehouse** | A physical stock location, optionally tied to a Project (unassigned = Head Office). Each has a manager and storekeeper, and is flagged as either a Store or a Warehouse. Subject to approval workflow. |

### 3.3 Vendor Management

| Module | Purpose |
|---|---|
| **Supplier** | Vendor master record — trade info (TIN/BIN), payment terms, credit limit, lead time — subject to approval workflow. |
| ↳ Contacts | Multiple named contacts per supplier, one marked primary. |
| ↳ Addresses | Multiple addresses per supplier (with Division/District/Thana breakdown and map coordinates), one marked primary. |
| ↳ Payment Accounts | Bank account details per supplier, resolved against a Bank/Branch lookup for routing numbers. |
| ↳ MFS Accounts | Mobile financial service accounts (e.g. bKash, Nagad) per supplier. |

### 3.4 Reference Data

- **Division / District / Thana** — Bangladesh administrative geography, used to build structured addresses with cascading dropdowns.
- **Address Type, Bank, Bank Branch, MFS Company** — supporting lookup lists used across the Supplier and Project address/payment forms.

## 4. Cross-Cutting Patterns

These conventions are applied consistently across every module above:

- **Soft Delete → Trash → Restore/Permanently Delete** — nothing is destroyed outright; records move to a trash view first, from which they can be restored or permanently removed. Every deletion records who performed it.
- **Approval Workflow** — Brand, Product, Project, Store, and Supplier each carry a Pending/Approved/Rejected status with a full audit trail of who actioned it, when, and any remarks.
- **Activity Logging** — most modules log create/update/delete/restore events with a record of exactly what changed.
- **Server-Side Data Tables** — every list view (search, sort, pagination) is handled server-side for performance as data grows.
- **Consistent CRUD Shape** — every module follows the same Create → List → View → Edit → Status/Trash pattern, so the system stays predictable to use as new modules are added.

## 5. Access Control

The system has a full Role and Permission management system built in (create/edit
roles, assign permissions to roles, assign roles to users), along with per-user
activity and login history tracking. **This is currently management UI only** — it
is not yet wired to actually restrict what a logged-in user can see or do. Today,
any authenticated user can access any module. Enforcing the built roles/permissions
against each module's actions is the natural next step once the business is ready
to define who should be allowed to do what.

## 6. Explicitly Out of Scope

The following exist in the underlying codebase/theme but are not part of this
system's purpose and are intentionally excluded from this overview and from active
development:

- OAuth/API access (Laravel Passport) — installed but unused; this system is web-only.
- FAQ module — generic scaffolding from the admin theme, unrelated to inventory management.
- Demo/template pages that ship with the underlying admin theme.

## 7. Technology Summary

| Layer | Choice |
|---|---|
| Backend | Laravel 12 (PHP), MySQL |
| Frontend | Blade templates, jQuery, Bootstrap 5 (Velzon admin theme) — fully mobile-responsive |
| Listings | Yajra DataTables (server-side) |
| Selects/Dropdowns | Select2 (searchable) |
| Alerts/Notifications | SweetAlert2 (confirmations), Toastify (status messages) |
| Maps | Leaflet / OpenStreetMap (for address coordinates) |
| Auth | Laravel session-based login (single organization, no multi-tenancy) |
| Roles/Permissions | Spatie Laravel-Permission (built, not yet enforced) |
| Activity Audit | Spatie Activity Log |

## 8. Known Gaps / Open Items

- **Stock tracking is not yet built** — no purchase orders, goods receipts, stock
  adjustments, or inter-warehouse transfers exist yet. This is the next major phase.
- **Role/permission enforcement** is not yet applied to routes or actions.
- **Supplier module** is flagged internally for a process/code review.
- A few lookup tables (Bank, Bank Branch) exist in the database without a tracked
  migration file — worth reconciling before the next environment setup.

---

*This document reflects the system as currently built. It should be revisited and
updated as new modules — particularly stock tracking — are added.*
