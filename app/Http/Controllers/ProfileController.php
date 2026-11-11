<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar_color' => ['required', 'regex:/\A#[0-9a-fA-F]{6}\z/'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['sometimes', 'boolean'],
        ]);

        $avatarPath = $user->avatar_path;
        $disk = Storage::disk('public');

        if ($request->hasFile('avatar')) {
            $avatarPath = $disk->putFile('avatars', $request->file('avatar'));

            if ($avatarPath === false) {
                throw new RuntimeException('Foto profil gagal disimpan.');
            }
        } elseif ($request->boolean('remove_avatar')) {
            $avatarPath = null;
        }

        $oldAvatarPath = $user->avatar_path;
        $user->fill([
            'name' => $validated['name'],
            'avatar_color' => strtoupper($validated['avatar_color']),
            'avatar_path' => $avatarPath,
        ])->save();

        if ($oldAvatarPath && $oldAvatarPath !== $avatarPath) {
            $disk->delete($oldAvatarPath);
        }

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        $avatarPath = $user->avatar_path;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        DB::transaction(function () use ($user): void {
            $user->notifications()->delete();
            $user->delete();
        });

        if ($avatarPath && ! Storage::disk('public')->delete($avatarPath)) {
            throw new RuntimeException('Akun telah dihapus, tetapi foto profil gagal dibersihkan.');
        }

        return redirect()
            ->route('public-notes.index')
            ->with('success', 'Akun dan semua catatanmu berhasil dihapus.');
    }
}
