<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\JsonQueryFilter;
use Illuminate\Database\Eloquent\Model;

class JsonQueryFilterTest extends TestCase
{
    /**
     * Behaviors for the JsonQueryFilter to be tested
     * It applies a plain where() when the dot-key relation matches the root model (e.g. appointment.status)
     * It applies whereHas() when the dot-key relation is a related model (e.g. patient.name)
     * It applies orWhereHas() for additional relation filters after the first (multiple different relations)
     * It handles a payload with only root-model filters (no whereHas at all)
     * It asserts the correct SQL is generated for a combined payload (root filter + relation filters)
     * It returns the unmodified query when the payload is empty
     */

    public function test_it_returns_unmodified_query_when_payload_is_empty(): void
    {
        $fakeModel = new class extends Model {
            protected $table = 'appointments';
        };

        $query = $fakeModel->newQuery();
        $sqlBefore = $query->toSql();

        $result = JsonQueryFilter::apply($query, []);

        $this->assertSame($sqlBefore, $result->toSql());
    }

    public function test_it_applies_where_when_key_refers_to_root_model(): void
    {
        $fakeModel = new class extends Model {
            protected $table = 'appointments';
        };

        $query = $fakeModel->newQuery();
        $result = JsonQueryFilter::apply($query, ['appointment.status' => 'confirmed']);

        $this->assertStringContainsString('where', strtolower($result->toSql()));
        $this->assertStringContainsString('"status"', $result->toSql());
        $this->assertStringNotContainsString('exists', strtolower($result->toSql()));
    }
}
