<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\OfflineEnrollment;
use App\Models\OfflinePackage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserImportController extends Controller
{
    /**
     * Show bulk import form
     */
    public function importForm()
    {
        $branches = Branch::active()->ordered()->get();
        $packages = OfflinePackage::active()->get();

        return view('admin.users.import', compact('branches', 'packages'));
    }

    /**
     * Get packages for a specific branch (AJAX)
     */
    public function getPackages(Request $request): JsonResponse
    {
        $request->validate(['branch_id' => 'required|exists:branches,id']);

        $packages = OfflinePackage::getPackagesForBranch($request->branch_id);

        return response()->json([
            'success' => true,
            'packages' => $packages->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'full_tests_allowed' => $p->full_tests_allowed,
                'section_tests_allowed' => $p->section_tests_allowed,
                'validity_days' => $p->validity_days,
            ]),
        ]);
    }

    /**
     * Preview uploaded file
     */
    public function importPreview(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $importId = Str::uuid()->toString();

            $importDir = storage_path('app/imports');
            if (!is_dir($importDir)) {
                mkdir($importDir, 0775, true);
            }

            $filename = $importId . '.' . $file->getClientOriginalExtension();
            $fullPath = $importDir . '/' . $filename;
            $file->move($importDir, $filename);

            if (!file_exists($fullPath)) {
                throw new \Exception('Failed to save uploaded file');
            }

            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = [];
            $hasPasswordColumn = false;

            foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }

                // Check header row for password column
                if ($rowIndex === 1) {
                    $headers = array_map(fn($v) => strtolower(trim($v ?? '')), $rowData);
                    $hasPasswordColumn = in_array('password', $headers);
                    continue;
                }

                if (empty(array_filter($rowData))) continue;

                $rows[] = $rowData;
            }

            $totalRows = count($rows);

            Cache::put("admin_import_{$importId}", [
                'path' => $fullPath,
                'rows' => $rows,
                'total' => $totalRows,
                'has_password_column' => $hasPasswordColumn,
                'processed' => 0,
                'success' => 0,
                'skipped' => 0,
                'errors' => [],
                'imported' => [],
            ], now()->addHours(1));

            return response()->json([
                'success' => true,
                'import_id' => $importId,
                'total_rows' => $totalRows,
                'has_password_column' => $hasPasswordColumn,
                'preview' => array_slice($rows, 0, 5),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to read file: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Process import in batches
     */
    public function importProcess(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'import_id' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'package_id' => 'required|exists:offline_packages,id',
            'password_mode' => 'required|in:custom,auto,column',
            'password' => 'required_if:password_mode,custom|nullable|string|min:6',
            'evaluation_type' => 'required|in:ai,human,both',
            'batch_size' => 'integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $validated = $validator->validated();
        $branch = Branch::findOrFail($validated['branch_id']);
        $importId = $validated['import_id'];
        $batchSize = $validated['batch_size'] ?? 10;

        $importData = Cache::get("admin_import_{$importId}");
        if (!$importData) {
            return response()->json([
                'success' => false,
                'message' => 'Import session expired. Please upload the file again.',
            ], 400);
        }

        $package = OfflinePackage::find($validated['package_id']);
        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid package selected.',
            ], 400);
        }

        $rows = $importData['rows'];
        $processed = $importData['processed'];
        $total = $importData['total'];
        $hasPasswordColumn = $importData['has_password_column'] ?? false;

        $batch = array_slice($rows, $processed, $batchSize);

        if (empty($batch)) {
            // All done
            session()->put('admin_import_results', [
                'success' => $importData['success'],
                'skipped' => $importData['skipped'],
                'errors' => $importData['errors'],
                'imported' => $importData['imported'],
                'password_mode' => $validated['password_mode'],
                'password' => $validated['password'] ?? null,
            ]);

            if (isset($importData['path']) && file_exists($importData['path'])) {
                @unlink($importData['path']);
            }
            Cache::forget("admin_import_{$importId}");

            return response()->json([
                'success' => true,
                'completed' => true,
                'processed' => $total,
                'total' => $total,
                'results' => [
                    'success' => $importData['success'],
                    'skipped' => $importData['skipped'],
                    'errors' => count($importData['errors']),
                ],
            ]);
        }

        foreach ($batch as $index => $rowData) {
            $currentRow = $processed + $index + 2;

            try {
                $password = $this->resolvePassword($validated, $rowData, $hasPasswordColumn);

                $result = $this->processImportRow(
                    $rowData, $branch, $package, $password,
                    $validated['evaluation_type'], $currentRow
                );

                if ($result['status'] === 'success') {
                    $importData['success']++;
                    $importData['imported'][] = array_merge($result['data'], ['password' => $password]);
                } elseif ($result['status'] === 'skipped') {
                    $importData['skipped']++;
                } else {
                    $importData['errors'][] = [
                        'row' => $currentRow,
                        'message' => $result['message'],
                    ];
                }
            } catch (\Exception $e) {
                $importData['errors'][] = [
                    'row' => $currentRow,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $importData['processed'] = $processed + count($batch);
        Cache::put("admin_import_{$importId}", $importData, now()->addHours(1));

        return response()->json([
            'success' => true,
            'completed' => false,
            'processed' => $importData['processed'],
            'total' => $total,
            'current_success' => $importData['success'],
            'current_skipped' => $importData['skipped'],
            'current_errors' => count($importData['errors']),
        ]);
    }

    /**
     * Resolve password based on mode
     */
    private function resolvePassword(array $validated, array $rowData, bool $hasPasswordColumn): string
    {
        return match ($validated['password_mode']) {
            'custom' => $validated['password'],
            'column' => $hasPasswordColumn ? trim($rowData[3] ?? '') ?: $this->generatePassword() : $this->generatePassword(),
            'auto' => $this->generatePassword(),
        };
    }

    /**
     * Generate a secure random password
     */
    private function generatePassword(): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $password = '';
        for ($i = 0; $i < 10; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

    /**
     * Process a single import row
     */
    protected function processImportRow(array $rowData, Branch $branch, OfflinePackage $package, string $password, string $evaluationType, int $rowNumber): array
    {
        $name = trim($rowData[0] ?? '');
        $email = strtolower(trim($rowData[1] ?? ''));
        $phone = trim($rowData[2] ?? '');

        if (empty($name)) {
            return ['status' => 'error', 'message' => 'Name is required'];
        }
        if (empty($email)) {
            return ['status' => 'error', 'message' => 'Email is required'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Invalid email format'];
        }

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // SECURITY (H7): never overwrite/password-reset an account we do not own via an
            // email-matched import row (admin/teacher/branch-staff/other-branch offline student).
            $isPrivileged = $existingUser->is_admin
                || $existingUser->role_id
                || $existingUser->isBranchStaff()
                || $existingUser->teacher()->exists();
            $belongsToAnotherBranch = $existingUser->student_type === 'offline'
                && (int) $existingUser->branch_id !== (int) $branch->id;
            if ($isPrivileged || $belongsToAnotherBranch) {
                return ['status' => 'error', 'message' => 'Email already registered to another account'];
            }

            if ($existingUser->student_type === 'offline') {
                $hasActiveEnrollment = OfflineEnrollment::where('user_id', $existingUser->id)
                    ->whereIn('status', ['active', 'completed'])
                    ->exists();
                if ($hasActiveEnrollment) {
                    return ['status' => 'skipped', 'message' => 'Already has active enrollment'];
                }
            }
        }

        if (!$existingUser && !empty($phone)) {
            if (User::where('phone_number', $phone)->exists()) {
                return ['status' => 'skipped', 'message' => 'Phone number already in use'];
            }
        }

        return DB::transaction(function () use ($name, $email, $phone, $branch, $package, $password, $evaluationType, $existingUser) {
            if ($existingUser) {
                $user = $existingUser;
                $user->update([
                    'name' => $name,
                    'phone_number' => $phone ?: $user->phone_number,
                    'password' => Hash::make($password),
                    'student_type' => 'offline',
                    'branch_id' => $branch->id,
                    'tests_taken_this_month' => 0,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            } else {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone_number' => $phone ?: null,
                    'password' => Hash::make($password),
                    'student_type' => 'offline',
                    'branch_id' => $branch->id,
                    'email_verified_at' => now(),
                ]);
            }

            $studentId = $branch->generateStudentId();

            $enrollment = OfflineEnrollment::createFromImport(
                userId: $user->id,
                branchId: $branch->id,
                studentId: $studentId,
                package: $package,
                evaluationType: $evaluationType,
            );

            $enrollment->update(['initial_password' => $password]);

            // Skip email notification for admin bulk import (can be noisy)

            return [
                'status' => 'success',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'student_id' => $studentId,
                ],
            ];
        });
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate(Request $request): StreamedResponse
    {
        $includePassword = $request->query('with_password');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ];

        $callback = function () use ($includePassword) {
            $file = fopen('php://output', 'w');

            if ($includePassword) {
                fputcsv($file, ['Name', 'Email', 'Number', 'Password']);
                fputcsv($file, ['John Doe', 'john@example.com', '01700000001', 'mypass123']);
                fputcsv($file, ['Jane Smith', 'jane@example.com', '01700000002', 'herpass456']);
            } else {
                fputcsv($file, ['Name', 'Email', 'Number']);
                fputcsv($file, ['John Doe', 'john@example.com', '01700000001']);
                fputcsv($file, ['Jane Smith', 'jane@example.com', '01700000002']);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export import results as CSV
     */
    public function exportImportResults(): StreamedResponse
    {
        $results = session('admin_import_results', []);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="imported_students_credentials.csv"',
        ];

        $callback = function () use ($results) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Name', 'Email', 'Student ID', 'Password']);

            foreach ($results['imported'] ?? [] as $student) {
                fputcsv($file, [
                    $student['name'],
                    $student['email'],
                    $student['student_id'],
                    $student['password'] ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
