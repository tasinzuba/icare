<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\BranchActivityLog;
use App\Models\OfflineEnrollment;
use App\Models\OfflinePackage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class OfflineStudentImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected Branch $branch;
    protected OfflinePackage $package;
    protected string $password;
    protected int $enrolledBy;

    protected array $results = [
        'success' => 0,
        'skipped' => 0,
        'errors' => [],
        'imported' => [],
    ];

    public function __construct(Branch $branch, OfflinePackage $package, string $password, int $enrolledBy)
    {
        $this->branch = $branch;
        $this->package = $package;
        $this->password = $password;
        $this->enrolledBy = $enrolledBy;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because index starts at 0 and row 1 is header

            try {
                $this->processRow($row, $rowNumber);
            } catch (\Exception $e) {
                $this->results['errors'][] = [
                    'row' => $rowNumber,
                    'message' => $e->getMessage(),
                    'data' => $row->toArray(),
                ];
            }
        }
    }

    protected function processRow(Collection $row, int $rowNumber): void
    {
        // Normalize column names (handle different cases and variations)
        $name = $this->getColumnValue($row, ['name', 'Name', 'NAME', 'full_name', 'Full Name']);
        $email = $this->getColumnValue($row, ['email', 'Email', 'EMAIL', 'e-mail', 'E-mail']);
        $phone = $this->getColumnValue($row, ['number', 'Number', 'NUMBER', 'phone', 'Phone', 'phone_number', 'Phone Number', 'mobile', 'Mobile']);

        // Validate required fields
        if (empty($name)) {
            $this->results['errors'][] = [
                'row' => $rowNumber,
                'message' => 'Name is required',
                'data' => $row->toArray(),
            ];
            return;
        }

        if (empty($email)) {
            $this->results['errors'][] = [
                'row' => $rowNumber,
                'message' => 'Email is required',
                'data' => $row->toArray(),
            ];
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->results['errors'][] = [
                'row' => $rowNumber,
                'message' => 'Invalid email format: ' . $email,
                'data' => $row->toArray(),
            ];
            return;
        }

        // Check if email exists in offline students only
        $existingOffline = User::where('email', $email)
            ->where('student_type', 'offline')
            ->exists();

        if ($existingOffline) {
            $this->results['skipped']++;
            return;
        }

        // Create user and enrollment in transaction
        DB::transaction(function () use ($name, $email, $phone, $rowNumber) {
            // Create User
            $user = User::create([
                'name' => trim($name),
                'email' => strtolower(trim($email)),
                'phone_number' => $phone ? trim($phone) : null,
                'password' => Hash::make($this->password),
                'student_type' => 'offline',
                'branch_id' => $this->branch->id,
                'email_verified_at' => now(),
            ]);

            // Generate student ID
            $studentId = $this->branch->generateStudentId();

            // Create Enrollment with full payment
            OfflineEnrollment::create([
                'user_id' => $user->id,
                'branch_id' => $this->branch->id,
                'student_id' => $studentId,
                'full_tests_allowed' => $this->package->full_tests_allowed,
                'full_tests_taken' => 0,
                'section_tests_allowed' => $this->package->section_tests_allowed,
                'section_tests_taken' => 0,
                'total_amount' => $this->package->getPriceForBranch($this->branch->id),
                'paid_amount' => $this->package->getPriceForBranch($this->branch->id),
                'due_amount' => 0,
                'payment_status' => 'paid',
                'payment_method' => 'bulk_import',
                'valid_from' => now()->toDateString(),
                'valid_until' => now()->addDays($this->package->validity_days)->toDateString(),
                'enrolled_by' => $this->enrolledBy,
                'status' => 'active',
                'notes' => 'Imported via bulk import',
            ]);

            $this->results['success']++;
            $this->results['imported'][] = [
                'name' => $user->name,
                'email' => $user->email,
                'student_id' => $studentId,
            ];
        });
    }

    protected function getColumnValue(Collection $row, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            // Check exact key
            if (isset($row[$key]) && !empty($row[$key])) {
                return (string) $row[$key];
            }

            // Check lowercase key
            $lowerKey = strtolower($key);
            if (isset($row[$lowerKey]) && !empty($row[$lowerKey])) {
                return (string) $row[$lowerKey];
            }
        }

        return null;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function logActivity(): void
    {
        BranchActivityLog::log(
            $this->branch->id,
            BranchActivityLog::ACTION_CREATED,
            "Bulk imported {$this->results['success']} students (Skipped: {$this->results['skipped']}, Errors: " . count($this->results['errors']) . ")",
            User::class,
            null,
            null,
            [
                'success_count' => $this->results['success'],
                'skipped_count' => $this->results['skipped'],
                'error_count' => count($this->results['errors']),
                'package' => $this->package->name,
            ]
        );
    }
}
