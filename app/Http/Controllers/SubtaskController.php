<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Card;
use App\Models\TimeLog;
use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubtaskController extends Controller
{
    private $validSubtaskStatus = ['todo', 'in_progress', 'review', 'done'];
    private $validCardStatus = ['todo', 'in_progress', 'review', 'done'];

    /**
     * Show form create subtask
     */
    public function create(Card $card)
    {
        $role = Auth::user()->role;

        if ($role === 'developer') {
            return view('developer.subtasks.create', compact('card'));
        }

        if ($role === 'designer') {
            return view('designer.subtasks.create', compact('card'));
        }

        abort(403, 'Role tidak diizinkan');
    }

    /**
     * Store new subtask
     */
    public function store(Request $request, Card $card)
    {
        $request->validate([
            'subtask_title'   => 'required|string|max:150',
            'description'     => 'nullable|string',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        // Gunakan DB transaction untuk handle error
        DB::beginTransaction();
        
        try {
            Subtask::create([
                'card_id'        => $card->card_id,
                'subtask_title'  => $request->subtask_title,
                'description'    => $request->description,
                'estimated_hours'=> $request->estimated_hours,
                'actual_hours'   => 0,
                'status'         => 'todo', // Default status
                'position'       => 1,
                'created_at'     => Carbon::now('Asia/Jakarta'),
            ]);

            $this->syncCardStatus($card->fresh(['subtasks', 'board']));
            
            DB::commit();
            
            return back()->with('success', '✅ Subtask berhasil dibuat');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating subtask: ' . $e->getMessage());
            return back()->with('error', '❌ Gagal membuat subtask: ' . $e->getMessage());
        }
    }

    /**
     * Start subtask
     */
    public function start(Subtask $subtask)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['developer', 'designer'])) {
            abort(403, 'Hanya developer/designer yang boleh mulai subtask');
        }

        DB::beginTransaction();
        
        try {
            $subtask->update(['status' => 'in_progress']);

            TimeLog::create([
                'card_id'    => $subtask->card_id,
                'subtask_id' => $subtask->subtask_id,
                'user_id'    => $user->user_id,
                'start_time' => Carbon::now('Asia/Jakarta'),
                'end_time'   => null,
                'duration_minutes' => null,
                'description'=> '🚀 Mulai subtask'
            ]);

            $this->syncCardStatus($subtask->card);
            
            DB::commit();
            
            return back()->with('success', '🚀 Subtask dimulai');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting subtask: ' . $e->getMessage());
            return back()->with('error', '❌ Gagal memulai subtask: ' . $e->getMessage());
        }
    }

    /**
     * Complete subtask -> status review
     */
    public function complete(Subtask $subtask)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['developer', 'designer'])) {
            abort(403, 'Hanya developer/designer yang boleh menyelesaikan subtask');
        }

        DB::beginTransaction();
        
        try {
            // Tutup log aktif
            $log = TimeLog::where('subtask_id', $subtask->subtask_id)
                ->where('user_id', $user->user_id)
                ->whereNull('end_time')
                ->latest('start_time')
                ->first();

            if ($log) {
                $end = Carbon::now('Asia/Jakarta');
                $start = $log->start_time instanceof Carbon 
                    ? $log->start_time 
                    : Carbon::parse($log->start_time, 'Asia/Jakarta');
                $minutes = $end->diffInMinutes($start);

                $log->update([
                    'end_time' => $end,
                    'duration_minutes' => $minutes,
                ]);
            }

            // Hitung ulang actual_hours dari semua log
            $totalMinutes = TimeLog::where('subtask_id', $subtask->subtask_id)->sum('duration_minutes');

            $subtask->update([
                'status' => 'review',
                'actual_hours' => round($totalMinutes / 60, 2),
            ]);

            $this->syncCardStatus($subtask->card);
            
            DB::commit();
            
            return back()->with('success', '✅ Subtask selesai. Menunggu approval Team Lead');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing subtask: ' . $e->getMessage());
            return back()->with('error', '❌ Gagal menyelesaikan subtask: ' . $e->getMessage());
        }
    }

    /**
     * Approve subtask -> hanya Team Lead
     */
    public function approve(Subtask $subtask)
    {
        $user = Auth::user();
        if ($user->role !== 'team_lead') {
            abort(403, 'Hanya Team Lead yang boleh approve subtask');
        }

        DB::beginTransaction();
        
        try {
            $subtask->update([
                'status' => 'done',
                'reject_reason' => null,
            ]);

            $this->syncCardStatus($subtask->card);
            
            DB::commit();
            
            return back()->with('success', '☑️ Subtask disetujui & card selesai');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving subtask: ' . $e->getMessage());
            return back()->with('error', '❌ Gagal menyetujui subtask: ' . $e->getMessage());
        }
    }

    /**
     * Reject subtask -> hanya Team Lead
     */
    public function reject(Request $request, Subtask $subtask)
    {
        $user = Auth::user();
        if ($user->role !== 'team_lead') {
            abort(403, 'Hanya Team Lead yang boleh reject subtask');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        
        try {
            $subtask->update([
                'status' => 'in_progress',
                'reject_reason' => $request->reason,
            ]);

            $log = TimeLog::where('subtask_id', $subtask->subtask_id)
                ->whereNull('end_time')
                ->latest('start_time')
                ->first();

            if ($log) {
                $end = Carbon::now('Asia/Jakarta');
                $start = $log->start_time instanceof Carbon 
                    ? $log->start_time 
                    : Carbon::parse($log->start_time, 'Asia/Jakarta');
                $minutes = $end->diffInMinutes($start);

                $log->update([
                    'end_time' => $end,
                    'duration_minutes' => $minutes,
                    'description' => "❌ Rejected by Team Lead: {$request->reason}",
                ]);
            } else {
                TimeLog::create([
                    'card_id'    => $subtask->card_id,
                    'subtask_id' => $subtask->subtask_id,
                    'user_id'    => $subtask->card->assignments->first()->user_id ?? null,
                    'start_time' => Carbon::now('Asia/Jakarta'),
                    'end_time'   => Carbon::now('Asia/Jakarta'),
                    'duration_minutes' => 0,
                    'description' => "❌ Rejected by Team Lead: {$request->reason}",
                ]);
            }

            TimeLog::create([
                'card_id'    => $subtask->card_id,
                'subtask_id' => $subtask->subtask_id,
                'user_id'    => $subtask->card->assignments->first()->user_id ?? null,
                'start_time' => Carbon::now('Asia/Jakarta'),
                'end_time'   => null,
                'duration_minutes' => null,
                'description' => "🔄 Rework setelah reject",
            ]);

            $totalMinutes = TimeLog::where('subtask_id', $subtask->subtask_id)->sum('duration_minutes');

            $subtask->update([
                'actual_hours' => round($totalMinutes / 60, 2),
            ]);

            $this->syncCardStatus($subtask->card);
            
            DB::commit();
            
            return back()->with('success', '❌ Subtask direject & otomatis masuk ke sesi Rework');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting subtask: ' . $e->getMessage());
            return back()->with('error', '❌ Gagal mereject subtask: ' . $e->getMessage());
        }
    }

    /**
     * Update card status & lane berdasarkan status subtasks.
     */
/**
 * Update card status & lane berdasarkan status subtasks.
 */
private function syncCardStatus(?Card $card): void
{
    if (!$card) {
        return;
    }

    $card->loadMissing(['subtasks', 'board']);
    $subtasks = $card->subtasks;

    if ($subtasks->isEmpty()) {
        return;
    }

    $status = 'todo';
    $targetBoard = 'To Do';

    if ($subtasks->contains(fn ($subtask) => $subtask->status === 'review')) {
        $status = 'review';
        $targetBoard = 'Review';
    } elseif ($subtasks->every(fn ($subtask) => $subtask->status === 'done')) {
        $status = 'done';
        $targetBoard = 'Done';
    } elseif ($subtasks->contains(fn ($subtask) => $subtask->status === 'in_progress')) {
        $status = 'in_progress';
        $targetBoard = 'In Progress';
    }

    // Pastikan status valid untuk card
    $validCardStatus = ['todo', 'in_progress', 'review', 'done'];
    if (!in_array($status, $validCardStatus)) {
        Log::error("Invalid card status: {$status}");
        $status = 'todo'; // Fallback ke default
    }

    // Update hanya jika ada perubahan
    if ($card->status !== $status) {
        $card->status = $status;
    }

    // Cari board target berdasarkan nama
    $projectId = optional($card->board)->project_id;
    if ($projectId) {
        $lane = Board::where('project_id', $projectId)
            ->where('board_name', $targetBoard)
            ->first();

        if ($lane && $card->board_id !== $lane->board_id) {
            $card->board_id = $lane->board_id;
        }
    }

    // Simpan hanya jika ada perubahan
    if ($card->isDirty(['status', 'board_id'])) {
        $card->save(['status', 'board_id']);
    }
}
}