<?php

namespace App\Services\Import;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Services\CsvParser;

class TaskImportService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly CsvParser $parser
    )
    {}

    public function buildRows(string $content, Project $project, int $user_id) : array
    {
        return collect($this->parser->parse($content))
            ->filter(fn (array $row) => $this->isValid($row))
            ->map(fn (array $row) => $this->toTaskRow($row, $project, $user_id))
            ->values()
            ->all();
    }

    public function toTaskRow(array $row, Project $project, int $user_id) : array
    {
        $status = ProjectStatus::tryFrom($row['status'] ?? '') ?? ProjectStatus::Active;
        return [
            'project_id' => $project->id,
            'name' => $row['name'] ?? null,
            'description' => $row['description'] ?? null,
            'status' => ProjectStatus::from($row['status']),
            'created_by_id' => $user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    private function isValid(array $row): bool
    {
        return ! empty($row['name'])
            && ProjectStatus::tryFrom($row['status'] ?? '') !== null;
    }

}
