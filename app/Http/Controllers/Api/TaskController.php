<?php

namespace App\Http\Controllers\Api;

use App\Enums\ImportReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\UploadCsvRequest;
use App\Jobs\ProcessCsvReportJob;
use App\Models\ImportReport;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    use AuthorizesRequests;
    public function __construct(

    )
    {

    }

    public function index()
    {

    }

    public function uploadTasksFromCsv(UploadCsvRequest $request, Project $project): JsonResponse
    {
        $this->authorize('show', $project);

        $path = $request->file('file')->store('imports');

        $report = ImportReport::create([
            'file_path' => $path,
            'user_id' => $request->user()->id,
            'status' => ImportReportStatus::Pending,
        ]);

        ProcessCsvReportJob::dispatchSync($report, $project);

        return response()->json(['message' => 'Processing started']);
    }
}
