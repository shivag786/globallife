<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DataResetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * Super Admin → Erase All Data.
 *
 * Two locks before anything happens: the DATA_RESET_PASSWORD from the
 * server's .env unlocks the page for a few minutes, then the exact phrase
 * "ERASE ALL DATA" must be typed. A backup is always taken first.
 */
class DataResetController extends Controller
{
    private const SESSION_KEY = 'data_reset_unlocked_until';

    public function __construct(private readonly DataResetService $reset) {}

    public function show(Request $request): View
    {
        $last = json_decode((string) app(\App\Services\SettingsService::class)->get('data_reset_last'), true);

        return view('admin.system.reset', [
            'enabled' => $this->reset->isEnabled(),
            'unlocked' => $this->isUnlocked($request),
            'unlockedUntil' => $request->session()->get(self::SESSION_KEY),
            'summary' => $this->reset->summary(),
            'backups' => $this->reset->backups(),
            'phrase' => config('data_reset.confirmation_phrase'),
            'last' => is_array($last) ? $last : null,
            'keep' => DataResetService::KEEP,
        ]);
    }

    public function unlock(Request $request): RedirectResponse
    {
        $request->validate(['reset_password' => ['required', 'string']]);

        if (! $this->reset->passwordMatches((string) $request->input('reset_password'))) {
            Log::warning('Wrong data-reset password', ['user_id' => $request->user()->id, 'ip' => $request->ip()]);

            return back()->withErrors(['reset_password' => 'Wrong reset password.']);
        }

        $request->session()->put(self::SESSION_KEY, now()->addMinutes((int) config('data_reset.unlock_minutes'))->timestamp);

        return back()->with('status', 'Unlocked for '.config('data_reset.unlock_minutes').' minutes.');
    }

    public function lock(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return back()->with('status', 'Locked again.');
    }

    public function erase(Request $request): RedirectResponse
    {
        if (! $this->isUnlocked($request)) {
            return back()->withErrors(['reset_password' => 'Unlock with the reset password first (the unlock lasts '.config('data_reset.unlock_minutes').' minutes).']);
        }

        $phrase = (string) config('data_reset.confirmation_phrase');
        $request->validate(
            ['confirmation' => ['required', 'string'], 'understood' => ['accepted']],
            ['understood.accepted' => 'Tick the box to confirm you understand the data will be deleted.'],
        );

        if ($request->input('confirmation') !== $phrase) {
            return back()->withErrors(['confirmation' => "Type exactly: {$phrase}"]);
        }

        $request->session()->forget(self::SESSION_KEY);

        try {
            $report = $this->reset->eraseAll($request->user(), $request->session()->getId());
        } catch (Throwable $e) {
            Log::error('Erase all data failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Nothing was erased: '.$e->getMessage());
        }

        return redirect()->route('admin.system.reset')->with('status',
            "All business data erased ({$report['rows']} records, {$report['files_moved']} files moved to the backup). "
            ."Backup: {$report['backup']}.");
    }

    public function download(string $backup): BinaryFileResponse
    {
        $path = $this->reset->backupPath($backup);
        abort_if($path === null, 404);

        return response()->download($path, 'globallife-'.$backup.'.sql');
    }

    public function destroyBackup(string $backup): RedirectResponse
    {
        abort_unless($this->reset->deleteBackup($backup), 404);

        return back()->with('status', "Backup {$backup} deleted.");
    }

    private function isUnlocked(Request $request): bool
    {
        return $this->reset->isEnabled()
            && (int) $request->session()->get(self::SESSION_KEY, 0) > now()->timestamp;
    }
}
