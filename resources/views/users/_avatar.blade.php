<span
    class="user-avatar {{ $class ?? '' }} {{ $user?->avatar_path ? 'user-avatar-with-photo' : '' }}"
    style="--avatar-color: {{ $user?->avatar_color ?? '#4775BF' }}"
>
    @if($user?->avatar_path)
        <img src="{{ $user->avatar_url }}" alt="Foto profil {{ $user->name }}">
    @else
        {{ strtoupper(substr($user?->name ?? 'U', 0, 2)) }}
    @endif
</span>
