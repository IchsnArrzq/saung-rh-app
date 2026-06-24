<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('kds', function ($user) {
    return $user !== null && $user->hasAnyRole(['superadmin', 'admin', 'chef', 'receptionist']);
});