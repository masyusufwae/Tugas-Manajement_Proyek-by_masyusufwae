<?php

namespace App\Http\Controllers;

use App\Models\ProjectMember;
use App\Models\User;
use App\Models\CardAssignment;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    /**
     * Tambahkan anggota baru ke proyek
     */
    public function addMember(Request $request, $projectId)
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        // Cari user berdasarkan username
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return back()->with('error', '❌ User tidak ditemukan!');
        }

        // Cek apakah user sudah menjadi anggota project ini
        $existingMember = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->user_id)
            ->first();

        if ($existingMember) {
            return back()->with('error', '⚠️ User sudah menjadi anggota proyek ini!');
        }

        // Cek apakah user masih aktif di project lain
        $otherProjectMember = ProjectMember::where('user_id', $user->user_id)
            ->where('project_id', '!=', $projectId)
            ->first();

        if ($otherProjectMember) {
            // Cek apakah masih ada tugas aktif (assigned/in_progress)
            $activeTasks = CardAssignment::where('user_id', $user->user_id)
                            ->whereIn('assignment_status', ['assigned', 'in_progress'])
                            ->exists();

            if ($activeTasks) {
                return back()->with('error', '⚠️ User masih punya tugas aktif di proyek lain!');
            }

            // Kalau tidak ada tugas aktif, maka user bisa dihapus dari project lama (opsional)
            // ProjectMember::where('member_id', $otherProjectMember->member_id)->delete();
        }

        // Tambahkan user ke project
        ProjectMember::create([
            'project_id' => $projectId,
            'user_id'    => $user->user_id,
            'role'       => 'member',
            'joined_at'  => now(),
        ]);

        return back()->with('success', '✅ Anggota berhasil ditambahkan ke proyek!');
    }

    /**
     * Hapus anggota dari proyek
     */
    public function removeMember(Request $request, $projectId, $memberId)
    {
        $projectMember = ProjectMember::where('project_id', $projectId)
            ->where('member_id', $memberId)
            ->with('user')
            ->first();

        if (!$projectMember) {
            return back()->with('error', '❌ Anggota tidak ditemukan pada proyek ini.');
        }

        // Jangan izinkan menghapus admin proyek
        if ($projectMember->role === 'admin') {
            return back()->with('error', '⚠️ Admin proyek tidak dapat dihapus dari anggota.');
        }

        // Cek apakah user masih memiliki tugas aktif di proyek ini
        $hasActiveAssignments = CardAssignment::where('user_id', $projectMember->user_id)
            ->whereIn('assignment_status', ['assigned', 'in_progress'])
            ->whereHas('card.board', function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->exists();

        if ($hasActiveAssignments) {
            return back()->with('error', '⚠️ User masih memiliki tugas aktif pada proyek ini.');
        }

        $projectMember->delete();

        return back()->with('success', '✅ Anggota berhasil dihapus dari proyek.');
    }
}
