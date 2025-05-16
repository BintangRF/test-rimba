<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $this->checkOverdueTasks();

        if ($user->role === 'admin') {
            return Task::all();
        }

        if ($user->role === 'manager') {
            return Task::where('created_by', $user->id)
                ->orWhereHas('assignedUser', function ($query) {
                    $query->where('role', 'staff');
                })
                ->get();
        }

        return Task::where('assigned_to', $user->id)->get();
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'assigned_to' => 'required|uuid|exists:users,id',
            'status' => 'required|in:pending,in_progress,done',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        $assignedUser = User::findOrFail($request->assigned_to);

        if ($user->role === 'manager' && $assignedUser->role !== 'staff') {
            return response()->json(['message' => 'Manager hanya bisa assign ke staff'], 403);
        }

        $task = Task::create([
            'id' => (string) Str::uuid(),
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'status' => $request->status,
            'due_date' => $request->due_date,
            'created_by' => $user->id,
        ]);

        return response()->json($task, 201);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($id);

        if ($user->id !== $task->created_by && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'string',
            'description' => 'string',
            'status' => 'in:pending,in_progress,done',
            'due_date' => 'date|after_or_equal:today',
        ]);

        $task->update($request->all());

        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $user = auth()->user();
        \Log::info("Deleting task by: {$user->id} - {$user->role}, Task: {$task->id}, Created by: {$task->created_by}");

        $this->authorize('delete', $task); // Laravel akan otomatis cek policy

        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }

    private function checkOverdueTasks()
    {
        $tasks = Task::where('status', '!=', 'done')
            ->where('due_date', '<', now())
            ->get();

        foreach ($tasks as $task) {
            $alreadyLogged = ActivityLog::where('action', 'task_overdue')
                ->where('description', "Task overdue: {$task->id}")
                ->exists();

            if (!$alreadyLogged) {
                ActivityLog::create([
                    'id' => (string) Str::uuid(),
                    'user_id' => auth()->id(),
                    'action' => 'task_overdue',
                    'description' => "Task overdue: {$task->id}",
                    'logged_at' => now(),
                ]);
            }
        }
    }
}

