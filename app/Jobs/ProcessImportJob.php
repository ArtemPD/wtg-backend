<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ImportStatus;
use App\Models\Import;
use App\Services\ImportProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

final class ProcessImportJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [5, 15];

    /**
     * @param Import $import
     */
    public function __construct(private readonly Import $import) {}

    /**
     * @return string
     */
    public function uniqueId(): string
    {
        return 'import-' . $this->import->id;
    }

    /**
     * @param ImportProcessor $importProcessor
     *
     * @throws Throwable
     *
     * @return void
     */
    public function handle(ImportProcessor $importProcessor): void
    {
        if ($this->import->status !== ImportStatus::PENDING) {
            return;
        }

        $importProcessor->process($this->import);
    }

    /**
     * @param Throwable $exception
     *
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        $this->import->update([
            'status' => ImportStatus::FAILED,
            'error' => Str::limit($exception->getMessage(), 500),
        ]);
    }
}
