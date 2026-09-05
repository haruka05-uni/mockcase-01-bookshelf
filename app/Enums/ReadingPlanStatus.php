<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    // 未読
    case Notstarted = 'not_started';

    // 進行中
    case Inprogress = 'in_progress';

    // 読了
    case Completed = 'completed';
}