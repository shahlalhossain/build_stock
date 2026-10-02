<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Central Authority for "which Store(s) can this User See/Act on" — used by
 * every Store-Scoped Module's DataTable `query()` and Controller `formLookups()`
 * instead of hand-rolling the same `whereIn('store_id', ...)` Logic six times.
 *
 * Deliberately separate from the Spatie Permission Check (Phase 1): a User
 * must pass BOTH "has the Permission for this Action" AND "this Record's
 * Store is in their Visible Set" — Store Attachment Gates WHICH Records,
 * Permission Gates WHICH Actions. Super Admin bypasses both (see
 * AuthServiceProvider::boot()'s Gate::before(), and visibleStoreIds() below).
 */
class StoreAccessService
{
    /**
     * The Store ids this User may See/Act on, or null for "Unrestricted"
     * (Super Admin, or any future Role Explicitly Granted Cross-Store Access).
     *
     * @return array<int>|null
     */
    public function visibleStoreIds(User $user): ?array
    {
        if ($user->hasAllAccess()) {
            return null;
        }

        return $user->stores()->pluck('stores.id')->unique()->values()->all();
    }

    /**
     * The single Store to Auto-Assign (no visible Picker) when this User opens
     * a Create Form — only when they are Attached to exactly ONE Store. A User
     * attached to 0 or 2+ Stores (or Super Admin) instead sees a Picker scoped
     * to visibleStoreIds() (all active Stores for Super Admin).
     */
    public function defaultStoreIdForCreate(User $user): ?int
    {
        $storeIds = $this->visibleStoreIds($user);

        if (is_array($storeIds) && count($storeIds) === 1) {
            return $storeIds[0];
        }

        return null;
    }

    /**
     * Whether the given Store id is inside this User's Visible Set — the
     * single Check every show()/update()/updateStatus() Controller Action
     * should run (alongside the Phase-1 Permission Check) before Operating on
     * a specific Record.
     */
    public function canAccessStore(User $user, ?int $storeId): bool
    {
        if (! $storeId) {
            return false;
        }

        $visibleStoreIds = $this->visibleStoreIds($user);

        return $visibleStoreIds === null || in_array($storeId, $visibleStoreIds, true);
    }

    /**
     * Scope a Query's Store Column(s) to the User's Visible Set — a No-Op for
     * Super Admin (visibleStoreIds() returns null). Pass more than one Column
     * for Models with Dual Store References (e.g. Product Transfer's Source +
     * Destination Store) — the Constraint becomes "Column A OR Column B".
     *
     * @param  \Illuminate\Database\Eloquent\Builder<*>  $query
     * @param  array<string>  $storeColumns
     * @return \Illuminate\Database\Eloquent\Builder<*>
     */
    public function scopeQueryToVisibleStores($query, User $user, array $storeColumns = ['store_id'])
    {
        $visibleStoreIds = $this->visibleStoreIds($user);

        if ($visibleStoreIds === null) {
            return $query;
        }

        return $query->where(function ($builder) use ($storeColumns, $visibleStoreIds) {
            foreach ($storeColumns as $index => $column) {
                $method = $index === 0 ? 'whereIn' : 'orWhereIn';
                $builder->{$method}($column, $visibleStoreIds);
            }
        });
    }

    /**
     * Active Stores restricted to the User's Visible Set — the Store <select>
     * Options for every Create/Edit Form. Super Admin gets every active Store,
     * matching today's Behavior exactly.
     *
     * @return Collection<int, Store>
     */
    public function selectableStores(User $user)
    {
        $visibleStoreIds = $this->visibleStoreIds($user);

        return Store::query()
            ->where('is_active', true)
            ->when($visibleStoreIds !== null, fn ($query) => $query->whereIn('id', $visibleStoreIds))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Every active Store, Unrestricted — for Fields like Product Transfer's
     * Destination, which the User may deliberately send Stock to a Store they
     * are not themselves Attached to.
     *
     * @return Collection<int, Store>
     */
    public function allActiveStores()
    {
        return Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Scope a Query through a belongsTo Relation (e.g. Product Receive, which
     * has no Store column of its own and is only reachable via its Transfer's
     * Source/Destination Store) — a No-Op for Super Admin. Pass more than one
     * Column for Dual References; the Constraint becomes "Column A OR Column B"
     * within the related Model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<*>  $query
     * @param  array<string>  $storeColumns
     * @return \Illuminate\Database\Eloquent\Builder<*>
     */
    public function scopeQueryToVisibleStoresViaRelation($query, User $user, string $relation, array $storeColumns = ['store_id'])
    {
        $visibleStoreIds = $this->visibleStoreIds($user);

        if ($visibleStoreIds === null) {
            return $query;
        }

        return $query->whereHas($relation, function ($builder) use ($storeColumns, $visibleStoreIds) {
            $builder->where(function ($inner) use ($storeColumns, $visibleStoreIds) {
                foreach ($storeColumns as $index => $column) {
                    $method = $index === 0 ? 'whereIn' : 'orWhereIn';
                    $inner->{$method}($column, $visibleStoreIds);
                }
            });
        });
    }
}
