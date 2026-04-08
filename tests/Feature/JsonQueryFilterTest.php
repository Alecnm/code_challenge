<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\JsonQueryFilter;
use Illuminate\Database\Eloquent\Model;

class JsonQueryFilterTest extends TestCase
{
    /**
     * Behaviors for the JsonQueryFilter to be tested
     * It applies a plain where() when the dot-key relation matches the root model (e.g. appointment.status) DONE
     * It applies whereHas() when the dot-key relation is a related model (e.g. patient.name) DONE
     * It applies orWhereHas() for additional relation filters after the first (multiple different relations) DONE
     * It handles a payload with only root-model filters (no whereHas at all) - covered by the first behavior DONE
     * It asserts the correct SQL is generated for a combined payload (root filter + relation filters) DONE
     * It returns the unmodified query when the payload is empty DONE
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

    public function test_it_applies_where_has_when_key_refers_to_a_relation(): void
    {
        $patientModel = new class extends Model {
            protected $table = 'patients';
        };
        $patientClass = get_class($patientModel);

        $appointmentModel = new class extends Model {
            protected $table = 'appointments';
            public static string $patientClass;

            public function patient()
            {
                return $this->belongsTo(static::$patientClass);
            }
        };
        $appointmentModel::$patientClass = $patientClass;

        $query = $appointmentModel->newQuery();
        $result = JsonQueryFilter::apply($query, ['patient.name' => 'John']);

        $this->assertStringContainsString('exists', strtolower($result->toSql()));
        $this->assertStringContainsString('"name"', $result->toSql());
        $this->assertStringNotContainsString(' or ', strtolower($result->toSql()));
    }

    public function test_it_applies_or_where_has_for_multiple_different_relations(): void
    {
        $patientModel = new class extends Model {
            protected $table = 'patients';
        };
        $patientClass = get_class($patientModel);

        $locationModel = new class extends Model {
            protected $table = 'locations';
        };
        $locationClass = get_class($locationModel);

        $appointmentModel = new class extends Model {
            protected $table = 'appointments';
            public static string $patientClass;
            public static string $locationClass;

            public function patient()
            {
                return $this->belongsTo(static::$patientClass);
            }

            public function location()
            {
                return $this->belongsTo(static::$locationClass);
            }
        };
        $appointmentModel::$patientClass = $patientClass;
        $appointmentModel::$locationClass = $locationClass;

        $query = $appointmentModel->newQuery();
        $result = JsonQueryFilter::apply($query, [
            'patient.name' => 'John',
            'location.city' => 'Dallas',
        ]);

        $sql = strtolower($result->toSql());
        $this->assertStringContainsString('exists', $sql);
        $this->assertStringContainsString('or exists', $sql);
    }

    public function test_it_generates_correct_sql_for_combined_payload(): void
    {
        $patientModel = new class extends Model {
            protected $table = 'patients';
        };
        $patientClass = get_class($patientModel);

        $appointmentModel = new class extends Model {
            protected $table = 'appointments';
            public static string $patientClass;

            public function patient()
            {
                return $this->belongsTo(static::$patientClass);
            }
        };
        $appointmentModel::$patientClass = $patientClass;

        $query = $appointmentModel->newQuery();
        $result = JsonQueryFilter::apply($query, [
            'appointment.status' => 'confirmed',
            'patient.name'       => 'John',
        ]);

        $expectedSql = 'select * from "appointments" where "status" = ? and exists (select * from "patients" where "appointments"."patient_id" = "patients"."id" and "name" = ?)';

        $this->assertSame($expectedSql, $result->toSql());
        $this->assertSame(['confirmed', 'John'], $result->getBindings());
    }
}
