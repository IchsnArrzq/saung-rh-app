<?php

namespace App\Policies;

use App\Models\SongRequest;
use App\Models\User;

class SongRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('song_request.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SongRequest $songRequest): bool
    {
        return $user->hasPermissionTo('song_request.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('song_request.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SongRequest $songRequest): bool
    {
        return $user->hasPermissionTo('song_request.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SongRequest $songRequest): bool
    {
        return $user->hasPermissionTo('song_request.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SongRequest $songRequest): bool
    {
        return $user->hasPermissionTo('song_request.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SongRequest $songRequest): bool
    {
        return $user->hasPermissionTo('song_request.forceDelete');
    }
}
