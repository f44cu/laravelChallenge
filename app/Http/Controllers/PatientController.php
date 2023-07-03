<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PatientRegistrationConfirmation;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    public function register(Request $request)
    {

        try {
        // Validate request data
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:patients',
            'phoneNumber' => 'required|string',
            'documentPhoto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Create a new patient
        $patient = Patient::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phoneNumber' => $validatedData['phoneNumber'],
            'documentPhoto' => $request->file('documentPhoto')->store('documentPhoto', 'public'),
        ]);

        // Send the confirmation email asynchronously
        Mail::to($patient->email)->queue(new PatientRegistrationConfirmation($patient));

        // Return a JSON response with the registered patient details, and a 201 status
        return response()->json([
            'message' => 'Patient registered successfully.',
            'patient' => $patient,
        ], 201);

        } catch (ValidationException $e) {
            // Handler of validation errors
            // Return a JSON response with the errors details, and a 400 status
            return response()->json([
                'message' => 'Error in the Validation Proccess',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            // Handler of other exceptions
            // Return a JSON response with some error detail, and a 500 status
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
?>
