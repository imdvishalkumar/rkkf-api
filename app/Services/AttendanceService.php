<?php

namespace App\Services;

use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\BranchRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AttendanceService
{
    protected $attendanceRepository;
    protected $studentRepository;
    protected $branchRepository;

    public function __construct(
        AttendanceRepositoryInterface $attendanceRepository,
        StudentRepositoryInterface $studentRepository,
        BranchRepositoryInterface $branchRepository
    ) {
        $this->attendanceRepository = $attendanceRepository;
        $this->studentRepository = $studentRepository;
        $this->branchRepository = $branchRepository;
    }

    public function getAllAttendance(array $filters = [])
    {
        return $this->attendanceRepository->all($filters);
    }

    public function getAttendanceById(int $id)
    {
        $attendance = $this->attendanceRepository->find($id);
        
        if (!$attendance) {
            throw new Exception('Attendance not found', 404);
        }

        return $attendance;
    }

    public function getAttendanceByStudent(int $studentId, array $filters = [])
    {
        $student = $this->studentRepository->find($studentId);
        
        if (!$student) {
            throw new Exception('Student not found', 404);
        }

        return $this->attendanceRepository->getByStudent($studentId, $filters);
    }

    public function getAttendanceByBranch(int $branchId, array $filters = [])
    {
        $branch = $this->branchRepository->find($branchId);
        
        if (!$branch) {
            throw new Exception('Branch not found', 404);
        }

        return $this->attendanceRepository->getByBranch($branchId, $filters);
    }

    public function getAttendanceByDate(string $date, array $filters = [])
    {
        return $this->attendanceRepository->getByDate($date, $filters);
    }

    public function markAttendance(array $attendanceData): array
    {
        DB::beginTransaction();
        
        try {
            $this->attendanceRepository->markAttendance($attendanceData);

            DB::commit();

            return [
                'message' => 'Attendance marked successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error marking attendance: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createAttendance(array $data): array
    {
        DB::beginTransaction();
        
        try {
            $student = $this->studentRepository->find($data['student_id']);
            
            if (!$student) {
                throw new Exception('Student not found', 404);
            }

            $branch = $this->branchRepository->find($data['branch_id']);
            
            if (!$branch) {
                throw new Exception('Branch not found', 404);
            }

            $attendance = $this->attendanceRepository->create($data);

            DB::commit();

            return [
                'attendance' => $attendance,
                'message' => 'Attendance created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating attendance: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateAttendance(int $id, array $data): array
    {
        $attendance = $this->attendanceRepository->find($id);
        
        if (!$attendance) {
            throw new Exception('Attendance not found', 404);
        }

        $updated = $this->attendanceRepository->update($id, $data);

        if (!$updated) {
            throw new Exception('Failed to update attendance', 500);
        }

        return [
            'attendance' => $this->attendanceRepository->find($id),
            'message' => 'Attendance updated successfully'
        ];
    }

    public function deleteAttendance(int $id): bool
    {
        $attendance = $this->attendanceRepository->find($id);
        
        if (!$attendance) {
            throw new Exception('Attendance not found', 404);
        }

        return $this->attendanceRepository->delete($id);
    }
}


