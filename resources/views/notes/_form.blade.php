<div class="create-note-page">

    <div class="create-note-heading">
        <div class="create-note-heading-icon" aria-hidden="true">✎</div>
        <div>
            <span class="eyebrow">{{ $editing ? 'PERBARUI CERITAMU' : 'RUANG CERITAMU' }}</span>
            <h1>{{ $editing ? 'Edit Catatan' : 'Buat Catatan Baru' }}</h1>
            <p>{{ $editing ? 'Perbarui cerita dan pengaturan catatanmu.' : 'Tuangkan ide, cerita, atau momen kecil yang ingin kamu simpan.' }}</p>
        </div>
    </div>

    <form
        action="{{ $editing ? route('notes.update', $note) : route('notes.store') }}"
        method="POST"
        class="note-form create-note-form"
    >
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <div class="create-note-fields">
            <div class="form-group">
                <label for="title">Judul catatan</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="Contoh: Hari yang menyenangkan"
                    value="{{ old('title', $note?->title ?? '') }}"
                    required
                    maxlength="255"
                >
                @error('title') <small class="create-note-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="category">Kategori</label>
                <select id="category" name="category" required>
                    @foreach(['Pribadi', 'Pekerjaan', 'Sekolah', 'Ide', 'Lainnya'] as $category)
                        <option value="{{ $category }}" {{ old('category', $note?->category ?? 'Pribadi') === $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
                @error('category') <small class="create-note-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group create-note-content">
                <label for="content">Isi ceritamu</label>
                <textarea
                    id="content"
                    name="content"
                    rows="14"
                    placeholder="Mulai menulis di sini... apa yang sedang kamu pikirkan?"
                    required
                >{{ old('content', $note?->content ?? '') }}</textarea>
                @error('content') <small class="create-note-error">{{ $message }}</small> @enderror
            </div>
        </div>

        <aside class="create-note-settings">
            <section class="create-note-section">
                <div class="create-note-section-heading">
                    <span class="create-note-section-icon" aria-hidden="true">◉</span>
                    <div>
                        <h2>Warna catatan</h2>
                        <p>Pilih warna yang paling pas.</p>
                    </div>
                </div>
                @include('notes._color_picker', [
                    'selectedColor' => old('note_color', $note?->note_color ?? '#FFF2BF'),
                ])
            </section>

            <section class="create-note-section">
                <div class="create-note-section-heading">
                    <span class="create-note-section-icon" aria-hidden="true">◎</span>
                    <div>
                        <h2>Visibilitas</h2>
                        <p>Kamu bisa mengubahnya kapan saja.</p>
                    </div>
                </div>

                <label for="is_public" class="visibility-choice">
                    <input
                        type="checkbox"
                        id="is_public"
                        name="is_public"
                        value="1"
                        {{ old('is_public', $note?->is_public ?? false) ? 'checked' : '' }}
                    >
                    <span class="visibility-choice-copy">
                        <strong>Bagikan ke publik</strong>
                        <small>Catatan dapat dibaca pengguna lain.</small>
                    </span>
                    <span class="visibility-choice-icon" aria-hidden="true">↗</span>
                </label>
                @error('is_public') <small class="create-note-error">{{ $message }}</small> @enderror
            </section>

            <section class="create-note-preview-section" aria-live="polite">
                <div class="create-note-section-heading">
                    <span class="create-note-section-icon" aria-hidden="true">▤</span>
                    <div>
                        <h2>Pratinjau</h2>
                        <p>Begini tampilan catatanmu.</p>
                    </div>
                </div>

                <article class="create-note-preview" data-note-preview>
                    <span class="create-note-preview-category" data-preview-category></span>
                    <h3 data-preview-title></h3>
                    <p data-preview-content></p>
                </article>
            </section>
        </aside>

        <div class="form-actions create-note-actions">
            <a href="{{ route('notes.index') }}" class="button button-light">Batal</a>
            <button type="submit" class="button button-primary">
                {{ $editing ? 'Simpan Perubahan' : 'Simpan Catatan' }}
                <span aria-hidden="true">→</span>
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
    .create-note-page {
        max-width: 1080px;
        margin: 0 auto;
    }

    .create-note-heading {
        display: flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 24px;
    }

    .create-note-heading-icon {
        display: grid;
        width: 58px;
        height: 58px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid var(--border);
        border-radius: 18px;
        background: linear-gradient(145deg, var(--white), var(--primary-light));
        color: var(--primary-dark);
        font-size: 25px;
        box-shadow: var(--shadow);
    }

    .create-note-heading h1 {
        margin: 5px 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: clamp(24px, 3vw, 32px);
        letter-spacing: -.8px;
    }

    .create-note-heading p {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .create-note-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(260px, .75fr);
        gap: 30px;
        padding: 30px;
        border-radius: 20px;
    }

    .create-note-fields,
    .create-note-settings {
        min-width: 0;
    }

    .create-note-fields .form-group {
        margin-bottom: 22px;
    }

    .create-note-fields label,
    .create-note-section-heading h2 {
        color: var(--text);
    }

    .create-note-fields input,
    .create-note-fields select,
    .create-note-fields textarea {
        font-size: 13px;
    }

    .create-note-fields textarea {
        min-height: 300px;
        line-height: 1.8;
    }

    .create-note-content {
        margin-bottom: 0 !important;
    }

    .create-note-settings {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .create-note-section,
    .create-note-preview-section {
        padding: 18px;
        border: 1px solid var(--border);
        border-radius: 15px;
        background: color-mix(in srgb, var(--background) 55%, var(--white));
    }

    .create-note-section-heading {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }

    .create-note-section-heading h2 {
        font-size: 13px;
        font-weight: 800;
    }

    .create-note-section-heading p {
        margin-top: 3px;
        color: var(--muted);
        font-size: 11px;
        line-height: 1.5;
    }

    .create-note-section-icon {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 11px;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 17px;
    }

    .visibility-choice {
        display: flex !important;
        align-items: center;
        gap: 11px;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: var(--white);
        cursor: pointer;
    }

    .visibility-choice input {
        width: 17px !important;
        height: 17px;
        flex: 0 0 auto;
        margin: 0;
        accent-color: var(--primary);
    }

    .visibility-choice-copy {
        display: flex;
        min-width: 0;
        flex: 1;
        flex-direction: column;
        gap: 4px;
    }

    .visibility-choice-copy strong {
        font-size: 11px;
    }

    .visibility-choice-copy small {
        color: var(--muted);
        font-size: 10px;
        font-weight: 400;
        line-height: 1.5;
    }

    .visibility-choice-icon {
        color: var(--primary-dark);
        font-size: 16px;
    }

    .create-note-preview {
        min-height: 152px;
        padding: 16px;
        border: 1px solid color-mix(in srgb, var(--text) 10%, transparent);
        border-radius: 13px;
        background: var(--preview-background, #FFF2BF);
        color: var(--preview-foreground, #292B27);
        box-shadow: 0 8px 18px color-mix(in srgb, var(--text) 8%, transparent);
        overflow-wrap: anywhere;
    }

    .create-note-preview-category {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        background: color-mix(in srgb, var(--preview-foreground, #292B27) 10%, transparent);
        font-size: 9px;
        font-weight: 700;
    }

    .create-note-preview h3 {
        margin: 12px 0 7px;
        font-size: 15px;
        line-height: 1.4;
    }

    .create-note-preview p {
        font-size: 11px;
        line-height: 1.7;
        white-space: pre-wrap;
    }

    .create-note-actions {
        grid-column: 1 / -1;
        justify-content: space-between;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }

    .create-note-actions .button-primary {
        min-width: 170px;
    }

    .create-note-error {
        display: block;
        margin-top: 7px;
        color: var(--danger);
    }

    @media (max-width: 850px) {
        .create-note-form {
            grid-template-columns: minmax(0, 1fr) minmax(230px, .8fr);
            gap: 20px;
            padding: 22px;
        }

        .create-note-section,
        .create-note-preview-section {
            padding: 14px;
        }
    }

    @media (max-width: 700px) {
        .create-note-heading {
            align-items: flex-start;
            gap: 13px;
        }

        .create-note-heading-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
        }

        .create-note-form {
            grid-template-columns: 1fr;
            gap: 10px;
            padding: 18px;
        }

        .create-note-fields textarea {
            min-height: 220px;
        }

        .create-note-actions {
            margin-top: 10px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const titleInput = document.querySelector('#title');
        const contentInput = document.querySelector('#content');
        const categoryInput = document.querySelector('#category');
        const colorInput = document.querySelector('#note_color');
        const preview = document.querySelector('[data-note-preview]');
        const previewTitle = document.querySelector('[data-preview-title]');
        const previewContent = document.querySelector('[data-preview-content]');
        const previewCategory = document.querySelector('[data-preview-category]');

        if (!preview) return;

        const updatePreview = () => {
            previewTitle.textContent = titleInput.value.trim() || 'Judul catatanmu';
            previewContent.textContent = contentInput.value.trim() || 'Cerita yang kamu tulis akan muncul di sini.';
            previewCategory.textContent = categoryInput.value;
            preview.style.setProperty('--preview-background', colorInput.value);

            const hex = colorInput.value.slice(1);
            const red = Number.parseInt(hex.slice(0, 2), 16);
            const green = Number.parseInt(hex.slice(2, 4), 16);
            const blue = Number.parseInt(hex.slice(4, 6), 16);
            const brightness = ((red * 299) + (green * 587) + (blue * 114)) / 1000;
            preview.style.setProperty('--preview-foreground', brightness >= 150 ? '#292B27' : '#FFFFFF');
        };

        [titleInput, contentInput, categoryInput, colorInput].forEach((input) => {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        });

        updatePreview();
    })();
</script>
@endpush
