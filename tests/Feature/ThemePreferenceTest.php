<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemePreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_toggle_is_available_on_public_and_authentication_pages(): void
    {
        $this->get(route('public-notes.index'))
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSee(asset('js/theme.js'), false)
            ->assertSee('Starship');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSee(asset('js/theme.js'), false)
            ->assertSee('Starship');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSee('Starship');
    }

    public function test_guest_can_open_public_notes_from_the_login_page_without_signing_in(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk sebagai tamu')
            ->assertSee('href="'.route('public-notes.index').'"', false);

        $this->get(route('public-notes.index'))
            ->assertOk();

        $this->get(route('notes.index'))
            ->assertRedirect(route('login'));
    }
}
