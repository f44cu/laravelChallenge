<?php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Patient;
use PHPUnit\Framework\Assert;

class PatientRegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testPatientRegistrationWithImage()
    {
        Storage::fake('public');

        $patientData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phoneNumber' => $this->faker->phoneNumber,
            'documentPhoto' => UploadedFile::fake()->image('document.jpg')
        ];

        $response = $this->postJson('/api/register', $patientData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Patient registered successfully',
                'data' => $patientData
            ]);

        $this->assertDatabaseHas('patients', [
            'name' => $patientData['name'],
            'email' => $patientData['email'],
            'phoneNumber' => $patientData['phoneNumber'],
            'documentPhoto' => $patientData['documentPhoto']
        ]);

        $patient = Patient::first();
        Assert::assertTrue(Storage::disk('public')->exists($patient->documentPhoto));
    }
}
?>
