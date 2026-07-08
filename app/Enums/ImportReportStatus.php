<?php

namespace App\Enums;

enum ImportReportStatus: string
{
    case Pending   = 'pending';
    case Success = 'success';
    case Failed = 'failed';
}
