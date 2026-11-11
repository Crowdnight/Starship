<button
    type="button"
    class="theme-toggle {{ ($floating ?? false) ? 'theme-toggle-floating' : '' }}"
    data-theme-toggle
    aria-label="Ganti ke mode gelap"
    aria-pressed="false"
    title="Ganti ke mode gelap"
>
    <span data-theme-icon aria-hidden="true">☾</span>
    @if($showLabel ?? false)
        <span class="theme-toggle-label" data-theme-label>Mode gelap</span>
    @endif
</button>
