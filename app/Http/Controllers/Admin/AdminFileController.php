<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminFile;
use App\Support\AdminAuth;
use App\Support\PhpUploadLimit;
use App\Support\UniqueDisplayName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminFileController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $request->query('sort') === 'name' ? 'name' : 'date';
        $search = trim((string) $request->query('q', ''));

        if (mb_strlen($search) > 200) {
            $search = mb_substr($search, 0, 200);
        }

        $query = AdminFile::query();

        if ($search !== '') {
            $this->applySearch($query, $search);
        }

        if ($sort === 'name') {
            $query->orderBy('name')->orderByDesc('id');
        } else {
            $query->orderByDesc('created_at')->orderByDesc('id');
        }

        $files = $query->paginate(30)->withQueryString();

        return view('admin.files.index', [
            'files' => $files,
            'sort' => $sort,
            'search' => $search,
            'uploadLimit' => PhpUploadLimit::formatted(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($message = $this->uploadFailureMessage($request)) {
            return back()
                ->withErrors(['file' => $message])
                ->withInput();
        }

        $validated = $request->validate([
            'file' => ['required', 'file'],
            'extra' => ['nullable', 'string', 'max:65535'],
        ]);

        /** @var UploadedFile $upload */
        $upload = $validated['file'];
        $name = UniqueDisplayName::make($upload->getClientOriginalName());
        $extension = $upload->getClientOriginalExtension();
        $storedName = Str::uuid()->toString().($extension !== '' ? '.'.$extension : '');
        $path = $upload->storeAs('admin-files', $storedName, 'local');

        if ($path === false) {
            return back()
                ->withErrors(['file' => 'Serverul nu a putut salva fișierul pe disc.'])
                ->withInput();
        }

        AdminFile::query()->create([
            'name' => $name,
            'stored_path' => $path,
            'extra' => $this->nullableExtra($validated['extra'] ?? null),
            'size' => $upload->getSize() ?: 0,
            'mime_type' => $upload->getClientMimeType() ?: null,
        ]);

        return redirect()
            ->route('admin.files.index')
            ->with('success', 'Fișier încărcat.');
    }

    public function download(AdminFile $adminFile): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($adminFile->stored_path), 404);

        return Storage::disk('local')->download($adminFile->stored_path, $adminFile->name);
    }

    public function update(Request $request, AdminFile $adminFile): RedirectResponse
    {
        $validator = validator($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('admin_files', 'name')->ignore($adminFile->id),
            ],
            'extra' => ['nullable', 'string', 'max:65535'],
        ], [
            'name.unique' => 'Un fișier cu acest nume există deja.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput()
                ->with('editing_file_id', $adminFile->id)
                ->withErrors($validator);
        }

        $validated = $validator->validated();
        $name = UniqueDisplayName::sanitize($validated['name']);

        if ($name !== $adminFile->name && AdminFile::query()->where('name', $name)->where('id', '!=', $adminFile->id)->exists()) {
            return back()
                ->withInput()
                ->with('editing_file_id', $adminFile->id)
                ->withErrors(['name' => 'Un fișier cu acest nume există deja.']);
        }

        $adminFile->update([
            'name' => $name,
            'extra' => $this->nullableExtra($validated['extra'] ?? null),
        ]);

        return back()->with('success', 'Fișier actualizat.');
    }

    public function destroy(Request $request, AdminFile $adminFile): JsonResponse|RedirectResponse
    {
        if (! AdminAuth::passwordMatches($request->input('password'))) {
            if ($request->expectsJson()) {
                return response()->json([], 403);
            }

            return back();
        }

        $adminFile->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()
            ->route('admin.files.index')
            ->with('success', 'Fișier șters.');
    }

    /**
     * @param  Builder<AdminFile>  $query
     */
    private function applySearch(Builder $query, string $term): void
    {
        $like = '%'.addcslashes($term, '%_\\').'%';

        $query->where(function ($q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('extra', 'like', $like);

            $driver = $q->getConnection()->getDriverName();

            if ($driver === 'sqlite') {
                $q->orWhereRaw("strftime('%d.%m.%Y', created_at) LIKE ?", [$like])
                    ->orWhereRaw("strftime('%d.%m.%Y %H:%M', created_at) LIKE ?", [$like])
                    ->orWhereRaw("strftime('%Y-%m-%d', created_at) LIKE ?", [$like]);
            } else {
                $q->orWhereRaw("DATE_FORMAT(created_at, '%d.%m.%Y') LIKE ?", [$like])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') LIKE ?", [$like])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", [$like]);
            }
        });
    }

    private function uploadFailureMessage(Request $request): ?string
    {
        $file = $request->file('file');
        $max = PhpUploadLimit::formatted();

        if ($file instanceof UploadedFile && ! $file->isValid()) {
            return match ($file->getError()) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "Fișierul depășește limita de încărcare ({$max}).",
                UPLOAD_ERR_PARTIAL => 'Încărcarea a eșuat: fișierul a fost trimis incomplet.',
                UPLOAD_ERR_NO_FILE => 'Nu a fost selectat niciun fișier.',
                UPLOAD_ERR_NO_TMP_DIR => 'Serverul nu are un director temporar pentru încărcări.',
                UPLOAD_ERR_CANT_WRITE => 'Serverul nu a putut salva fișierul pe disc.',
                UPLOAD_ERR_EXTENSION => 'O extensie PHP a oprit încărcarea.',
                default => 'Încărcarea a eșuat.',
            };
        }

        return null;
    }

    private function nullableExtra(mixed $extra): ?string
    {
        if (! is_string($extra)) {
            return null;
        }

        $extra = trim($extra);

        return $extra === '' ? null : $extra;
    }
}
