<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardClockTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_a_live_local_clock_under_the_welcome_message(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('notes.index'))
            ->assertOk()
            ->assertSee('Simpan ide, cerita, dan kenanganmu dalam satu tempat.')
            ->assertSee('Waktu sekarang')
            ->assertSee('data-realtime-clock', false)
            ->assertSee('Intl.DateTimeFormat', false)
            ->assertSee('window.setInterval(updateClock, 1000)', false);
    }
}
