<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminder extends Notification
{
    use Queueable;

    public function __construct(
        public ReadingPlan $readingPlan,
        public string $type
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = match ($this->type) {
            'three_days_before' => '「'.$this->readingPlan->book->title.'」の読書期限まであと3日です。',

            'on_due_date' => '「'.$this->readingPlan->book->title.'」の読書期限は今日です。',

            'three_days_after' => '「'.$this->readingPlan->book->title.'」の読書期限から3日経過しています。',

            default => '「'.$this->readingPlan->book->title.'」の読書期限のお知らせです。',
        };

        return [
            'reading_plan_id' => $this->readingPlan->id,
            'timing' => $this->type,
            'title' => '読書期限のお知らせ',
            'body' => $message,
        ];
    }
}
