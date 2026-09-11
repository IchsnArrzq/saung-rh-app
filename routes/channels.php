<?php

use App\Models\SpecialRequest;
use App\Models\TableSession;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Sama dengan gerbang rute KDS (routes/kds.php).
Broadcast::channel('kds', function ($user) {
    return $user !== null
        && ($user->hasAnyRole(['superadmin', 'admin', 'chef', 'receptionist']) || $user->can('kitchen.view'));
});

// Panel meja staf. Siapa pun yang boleh melihat permintaan khusus boleh
// mendengar perubahannya — gerbang yang sama dengan halamannya.
Broadcast::channel('floor', function ($user) {
    return $user !== null && $user->can('viewAny', SpecialRequest::class);
});

// Baki persetujuan sesi meja. Siapa pun yang boleh melihat sesi meja boleh
// mendengar kapan daftar tunggunya berubah — gerbang yang sama dengan halamannya.
Broadcast::channel('table-sessions', function ($user) {
    return $user !== null && $user->can('viewAny', TableSession::class);
});
