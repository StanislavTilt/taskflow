<?php

namespace App\Http\Controllers\Api;

use App\Enums\ImportReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\UploadCsvRequest;
use App\Jobs\ProcessCsvReportJob;
use App\Jobs\SendImportMailJob;
use App\Models\ImportReport;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;

class TaskController extends Controller
{
    use AuthorizesRequests;
    public function uploadTasksFromCsv(UploadCsvRequest $request, Project $project): JsonResponse
    {
        $this->authorize('show', $project);

        $path = $request->file('file')->store('imports');

        $report = ImportReport::create([
            'file_path' => $path,
            'user_id' => $request->user()->id,
            'status' => ImportReportStatus::Pending,
        ]);

        Bus::chain([
            new ProcessCsvReportJob($report, $project),
            new SendImportMailJob($report)
        ])->dispatch();

        return response()->json(['message' => 'Processing started']);
    }
}
