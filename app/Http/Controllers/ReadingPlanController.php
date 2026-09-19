<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Support\Facades\Auth;

class ReadingPlanController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $currentStatus = request('status');

        $readingPlans = ReadingPlan::with('book')
            ->where('user_id', $user->id)
            ->when($currentStatus, function ($query) use ($currentStatus) {
                $query->where('status', $currentStatus);
            })
            ->get();

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    public function create()
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    public function store(StoreReadingPlanRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を作成しました。');
    }

    public function edit(ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->load('book');

        return view('reading-plans.edit', compact('readingPlan'));
    }

    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $validated = $request->validated();

        if ($readingPlan->status === ReadingPlanStatus::Expired) {
            $validated['status'] = ReadingPlanStatus::InProgress;
        }

        $readingPlan->update($validated);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました。');
    }

    public function destroy(ReadingPlan $readingPlan)
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        // この読書計画に関係する通知を削除
        $readingPlan->user->notifications()
            ->where('data->reading_plan_id', $readingPlan->id)
            ->delete();

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました。');
    }

    public function complete(ReadingPlan $readingPlan)
    {
        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => today(),
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読了しました。');
    }
}
