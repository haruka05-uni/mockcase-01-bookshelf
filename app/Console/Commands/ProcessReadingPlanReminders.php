<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReadingPlan;
use App\Enums\ReadingPlanStatus;
use App\Notifications\ReadingPlanReminder;

class ProcessReadingPlanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-reading-plan-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 期限3日前の読書計画を取得
        $threeDaysBeforePlans = ReadingPlan::whereDate(
            'target_date',
            today()->addDays(3)
        )
            ->where('status', ReadingPlanStatus::InProgress->value)
            ->with(['user', 'book'])
            ->get();

        foreach ($threeDaysBeforePlans as $threeDaysBeforePlan) {
            $threeDaysBeforePlan->user->notify(
                new ReadingPlanReminder($threeDaysBeforePlan, 'three_days_before')
            );
        }

        // 期限当日の読書計画を取得
        $todayPlans = ReadingPlan::whereDate(
            'target_date',
            today()
        )
            ->where('status', ReadingPlanStatus::InProgress->value)
            ->with(['user', 'book'])
            ->get();

        foreach ($todayPlans as $todayPlan) {
            $todayPlan->user->notify(
                new ReadingPlanReminder($todayPlan, 'on_due_date')
            );
        }

        // 期限切れの読書計画を取得
        $expiredPlans = ReadingPlan::where('target_date', '<', today())
            ->where('status', '!=', ReadingPlanStatus::Completed->value)
            ->where('status', '!=', ReadingPlanStatus::Expired->value)
            ->get();

        foreach ($expiredPlans as $expiredPlan) {
            $expiredPlan->update([
                'status' => ReadingPlanStatus::Expired,
            ]);
        }

        // 期限切れ後3日の読書計画を取得
        $threeDaysAfterPlans = ReadingPlan::whereDate(
            'target_date',
            today()->subDays(3)
        )
            ->where('status', ReadingPlanStatus::Expired->value)
            ->with(['user', 'book'])
            ->get();

        foreach ($threeDaysAfterPlans as $threeDaysAfterPlan) {
            $threeDaysAfterPlan->user->notify(
                new ReadingPlanReminder($threeDaysAfterPlan, 'three_days_after')
            );
        }

        return Command::SUCCESS;
    }
}
