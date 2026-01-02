<?php

namespace App\Services;

use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\FeeRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Enums\StudentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class StudentService
{
    protected $studentRepository;
    protected $feeRepository;
    protected $userRepository;

    public function __construct(
        StudentRepositoryInterface $studentRepository,
        FeeRepositoryInterface $feeRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->studentRepository = $studentRepository;
        $this->feeRepository = $feeRepository;
        $this->userRepository = $userRepository;
    }

    public function getAllStudents(array $filters = [])
    {
        return $this->studentRepository->all($filters);
    }

    public function getPaginatedStudents(array $filters = [], int $perPage = 15)
    {
        return $this->studentRepository->paginate($filters, $perPage);
    }

    public function getStudentById(int $id)
    {
        $student = $this->studentRepository->find($id);
        
        if (!$student) {
            throw new Exception('Student not found', 404);
        }

        return $student;
    }

    public function searchStudents(string $term, array $filters = [])
    {
        return $this->studentRepository->search($term, $filters);
    }

    public function createStudent(array $data): array
    {
        DB::beginTransaction();
        
        try {
            if ($this->studentRepository->checkEmailExists($data['email'])) {
                throw new Exception('Email already exists in students table', 422);
            }

            if ($this->userRepository->checkEmailExists($data['email'])) {
                throw new Exception('Email already exists in instructors table', 422);
            }

            $feesData = $data['fees'] ?? [];
            $months = $data['months'] ?? [];
            unset($data['fees'], $data['months']);

            $student = $this->studentRepository->create($data);

            if (!empty($months) && !empty($feesData)) {
                $this->createStudentFees($student->student_id, $months, $feesData);
            }

            DB::commit();

            return [
                'student' => $student,
                'message' => 'Student created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating student: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateStudent(int $id, array $data): array
    {
        $student = $this->studentRepository->find($id);
        
        if (!$student) {
            throw new Exception('Student not found', 404);
        }

        if (isset($data['email']) && $data['email'] !== $student->email) {
            if ($this->studentRepository->checkEmailExists($data['email'], $id)) {
                throw new Exception('Email already exists', 422);
            }

            if ($this->userRepository->checkEmailExists($data['email'])) {
                throw new Exception('Email already exists in instructors table', 422);
            }
        }

        $updated = $this->studentRepository->update($id, $data);

        if (!$updated) {
            throw new Exception('Failed to update student', 500);
        }

        return [
            'student' => $this->studentRepository->find($id),
            'message' => 'Student updated successfully'
        ];
    }

    public function deleteStudent(int $id): bool
    {
        $student = $this->studentRepository->find($id);
        
        if (!$student) {
            throw new Exception('Student not found', 404);
        }

        return $this->studentRepository->delete($id);
    }

    public function activateStudent(int $id): bool
    {
        $student = $this->studentRepository->find($id);
        
        if (!$student) {
            throw new Exception('Student not found', 404);
        }

        return $this->studentRepository->activate($id);
    }

    public function deactivateStudent(int $id): bool
    {
        $student = $this->studentRepository->find($id);
        
        if (!$student) {
            throw new Exception('Student not found', 404);
        }

        return $this->studentRepository->deactivate($id);
    }

    public function resetPassword(int $id): string
    {
        $student = $this->studentRepository->find($id);
        
        if (!$student) {
            throw new Exception('Student not found', 404);
        }

        $newPassword = $student->selfno;
        $this->studentRepository->resetPassword($id, $newPassword);

        return $newPassword;
    }

    protected function createStudentFees(int $studentId, array $months, array $feesData): void
    {
        $currentYear = date('Y');
        $currentDate = date('Y-m-d');
        $totalAmount = $feesData['amount'] ?? 0;
        $amountPerMonth = $totalAmount / count($months);
        $remainder = $totalAmount % count($months);

        foreach ($months as $index => $month) {
            $amount = $amountPerMonth;
            
            if ($index === 0) {
                $amount += $remainder;
            }

            $feeData = [
                'student_id' => $studentId,
                'months' => $month,
                'year' => $currentYear,
                'date' => $currentDate,
                'amount' => $amount,
                'coupon_id' => 1,
                'additional' => 0,
                'disabled' => 0,
                'mode' => 'cash',
            ];

            $this->feeRepository->create($feeData);
        }
    }

    public function getStudentsByBranch(int $branchId, array $filters = [])
    {
        return $this->studentRepository->getByBranch($branchId, $filters);
    }

    public function getStudentsByBelt(int $beltId, array $filters = [])
    {
        return $this->studentRepository->getByBelt($beltId, $filters);
    }

    public function getStudentsByDateRange(string $startDate, string $endDate, array $filters = [])
    {
        return $this->studentRepository->getByDateRange($startDate, $endDate, $filters);
    }
}


