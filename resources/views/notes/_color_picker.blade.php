@push('styles')
<style>
    .note-color-picker {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .note-color-picker input[type="color"] {
        width: 52px;
        height: 44px;
        padding: 4px;
        cursor: pointer;
    }

    .note-color-swatches {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .note-color-swatch {
        width: 30px;
        height: 30px;
        border: 2px solid color-mix(in srgb, var(--text) 18%, transparent);
        border-radius: 50%;
        box-shadow: inset 0 0 0 2px var(--white);
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .note-color-swatch[aria-pressed="true"] {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    .note-color-swatch:hover {
        transform: scale(1.12);
    }

    .note-color-help {
        display: block;
        margin-top: 9px;
        color: var(--muted);
        font-size: 11px;
    }
</style>
@endpush

@php
    $colorOptions = [
        ['#FFF2BF', 'Kuning lembut'],
        ['#E4E6DF', 'Abu-abu'],
        ['#F5E6A8', 'Gandum'],
        ['#D8DAD2', 'Batu'],
        ['#F4D66D', 'Kuning cerah'],
        ['#BFC3B8', 'Kabut'],
        ['#E8DDAF', 'Pasir'],
        ['#C8CBC2', 'Perak'],
        ['#F8EDC8', 'Vanila'],
        ['#AEB2A8', 'Arang muda'],
        ['#555950', 'Slate'],
        ['#292B27', 'Arang'],
    ];
@endphp

<div class="note-color-picker" data-note-color-picker>
    <input
        type="color"
        id="note_color"
        name="note_color"
        value="{{ $selectedColor }}"
        aria-label="Pilih warna khusus untuk catatan"
        required
    >

    <div class="note-color-swatches" aria-label="Pilihan warna">
        @foreach($colorOptions as [$color, $name])
            <button
                type="button"
                class="note-color-swatch"
                style="background-color: {{ $color }}"
                data-color="{{ $color }}"
                aria-label="{{ $name }}"
                aria-pressed="{{ strtoupper($selectedColor) === $color ? 'true' : 'false' }}"
                title="{{ $name }}"
            ></button>
        @endforeach
    </div>
</div>

<small class="note-color-help" id="note-color-help">
    Pilih salah satu warna atau klik kotak warna untuk menentukan warna sendiri.
</small>

@error('note_color')
    <small class="note-color-help" role="alert">{{ $message }}</small>
@enderror

<script>
    document.querySelectorAll('[data-note-color-picker]').forEach((picker) => {
        const colorInput = picker.querySelector('input[type="color"]');
        const swatches = picker.querySelectorAll('[data-color]');

        const updateSelectedSwatch = () => {
            swatches.forEach((swatch) => {
                swatch.setAttribute(
                    'aria-pressed',
                    swatch.dataset.color.toUpperCase() === colorInput.value.toUpperCase()
                        ? 'true'
                        : 'false'
                );
            });
        };

        swatches.forEach((swatch) => {
            swatch.addEventListener('click', () => {
                colorInput.value = swatch.dataset.color;
                updateSelectedSwatch();
                colorInput.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });

        colorInput.addEventListener('input', updateSelectedSwatch);
        updateSelectedSwatch();
    });
</script>
