<?php

namespace Database\Seeders;

use App\Models\SongRequest;
use App\Models\Table;
use App\Models\TableSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class Fase5DemoSeeder extends Seeder
{
    /**
     * Seed a live table session with a couple of song requests so the staff
     * queue board has content right after seeding. (Chat is ephemeral/Redis
     * and is left empty — it fills up live.)
     */
    public function run(): void
    {
        if (SongRequest::query()->exists()) {
            return;
        }

        $table = Table::query()->where('code', 'like', 'T-%')->orderBy('code')->first()
            ?? Table::query()->orderBy('code')->first();

        if (! $table) {
            return;
        }

        $session = $table->tableSessions()->create([
            'token' => (string) Str::random(40),
            'status' => 'active',
            'visibility' => 'public',
            'is_anonymous' => false,
            'pax' => 3,
            'started_at' => now()->subMinutes(20),
        ]);

        $songs = [
            ['title' => 'Bohemian Rhapsody', 'artist' => 'Queen', 'status' => 'playing', 'requested_by' => 'Rangga'],
            ['title' => 'Mungkin Nanti', 'artist' => 'Peterpan', 'status' => 'queued', 'requested_by' => 'Sinta'],
        ];

        foreach ($songs as $song) {
            SongRequest::query()->create([
                'table_session_id' => $session->id,
                'table_id' => $table->id,
                'table_code' => $table->code,
                'title' => $song['title'],
                'artist' => $song['artist'],
                'requested_by' => $song['requested_by'],
                'status' => $song['status'],
                'played_at' => $song['status'] === 'playing' ? now()->subMinutes(5) : null,
            ]);
        }
    }
}
