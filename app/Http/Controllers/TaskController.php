<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->filter['status'] ?? null;
        $priority = $request->filter['priority'] ?? null;
        $dueDate = $request->filter['due_date'] ?? null;

        $formattedDueDate = null;
        if (isset($dueDate)) {
            //formate date 
            $formattedDueDate = Carbon::createFromFormat('d-m-Y', $dueDate)->format('Y-m-d');
        }

        $tasks = Task::with('notes')
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(isset($formattedDueDate), function ($query) use ($formattedDueDate) {
                $query->whereDate('due_date', $formattedDueDate);
            })
            ->when($priority, function ($query, $priority) {
                $query->where('priority', $priority);
            })
            ->whereHas('notes')
            ->withCount('notes')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('notes_count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'due_date' => 'required|date',
            'status' => 'required|in:new,incomplete,complete',
            'priority' => 'required|in:high,medium,low',
            'notes' => 'required|array',
            'notes.*.subject' => 'required|string|max:255',
            'notes.*.note' => 'required|string',
            'notes.*.attachments' => 'required|array', 
            'notes.*.attachments.*' => 'file|max:10240', // Allow multiple files, max size 10MB each
        ]);


        //formate date 
        $formattedStartDate = Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d');
        $formattedDueDate = Carbon::createFromFormat('d-m-Y', $request->due_date)->format('Y-m-d');


        // Start a transaction
        DB::beginTransaction();
        try {
            //create task
            $task = Task::create([
                'subject' => $request->subject,
                'description' => $request->description,
                'start_date' => $formattedStartDate,
                'due_date' => $formattedDueDate,
                'status' => $request->status,
                'priority' => $request->priority,
            ]);

            $notesData = []; // prepare bulk insert
            // Handle notes
            foreach ($request->notes as $noteData) {
                $note = new Note();
                $note->subject = $noteData['subject'];
                $note->note = $noteData['note'];
                // Handle file attachments
                $attachmentPaths = [];
                if (isset($noteData['attachments']) && is_array($noteData['attachments'])) {
                    foreach ($noteData['attachments'] as $attachment) {
                        $path = $attachment->store('attachments', 'public');
                        $attachmentPaths[] = $path;
                    }
                    $note->attachments = json_encode($attachmentPaths);
                }
                $task->notes()->save($note);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully with associated notes and attachments.',
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction for any other exceptions
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the task and notes.',
                // 'error' => $e->getMessage(),
            ], 500);
        }
    }
}
