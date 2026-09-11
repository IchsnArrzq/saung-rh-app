<?php

use App\Models\TableSession;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('kds', function ($user) {
    return $user !== null && $user->hasAnyRole(['superadmin', 'admin', 'chef', 'receptionist']);
});

// Baki persetujuan sesi meja. Siapa pun yang boleh melihat sesi meja boleh
// mendengar kapan daftar tunggunya berubah — gerbang yang sama dengan halamannya.
Broadcast::channel('table-sessions', function ($user) {
    return $user !== null && $user->can('viewAny', TableSession::class);
});