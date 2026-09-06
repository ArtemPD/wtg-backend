<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Smoke tests for the application skeleton set up in TASK.md, section 15, stage 1:
 * API routing registration, CarbonImmutable as the default date class, and the
 * dedicated `wtg_testing` database connection.
 */
final class ApplicationSkeletonTest extends TestCase
{
    #[Test]
    public function itRespondsToTheHealthCheckEndpoint(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }

    #[Test]
    public function itUsesCarbonImmutableForDates(): void
    {
        $this->assertInstanceOf(CarbonImmutable::class, Date::now());
    }

    #[Test]
    public function itRunsAgainstTheDedicatedTestingDatabase(): void
    {
        $this->assertSame('mysql-testing', config('database.connections.mysql.host'));
        $this->assertSame('wtg_testing', config('database.connections.mysql.database'));
    }
}
