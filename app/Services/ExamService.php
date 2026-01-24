<?php

namespace App\Services;

use App\Repositories\Contracts\ExamRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ExamService
{
    protected $examRepository;

    public function __construct(ExamRepositoryInterface $examRepository)
    {
        $this->examRepository = $examRepository;
    }

    /**
     * Get all exams with optional filters.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllExams(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->examRepository->all($filters);
    }

    /**
     * Get exam by ID.
     *
     * @throws Exception
     */
    public function getExamById(int $id): \App\Models\Exam
    {
        $exam = $this->examRepository->find($id);

        if (!$exam) {
            throw new Exception('Exam not found', 404);
        }

        return $exam;
    }

    /**
     * Get published exams.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPublishedExams(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->examRepository->getPublished($filters);
    }

    public function createExam(array $data): array
    {
        DB::beginTransaction();

        try {
            $exam = $this->examRepository->create($data);

            DB::commit();

            return [
                'exam' => $exam,
                'message' => 'Exam created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating exam: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateExam(int $id, array $data): array
    {
        $exam = $this->examRepository->find($id);

        if (!$exam) {
            throw new Exception('Exam not found', 404);
        }

        $updated = $this->examRepository->update($id, $data);

        if (!$updated) {
            throw new Exception('Failed to update exam', 500);
        }

        return [
            'exam' => $this->examRepository->find($id),
            'message' => 'Exam updated successfully'
        ];
    }

    public function deleteExam(int $id): bool
    {
        $exam = $this->examRepository->find($id);

        if (!$exam) {
            throw new Exception('Exam not found', 404);
        }

        return $this->examRepository->delete($id);
    }

    public function publishExam(int $id): bool
    {
        $this->updateExam($id, ['isPublished' => 1]);
        return true;
    }

    public function unpublishExam(int $id): bool
    {
        $this->updateExam($id, ['isPublished' => 0]);
        return true;
    }

    /**
     * Mark exam attendance and generate certificates
     */
    public function markAttendance(int $examId, array $attendanceArray, int $userId): array
    {
        DB::beginTransaction();

        try {
            // Check if attendance already exists using Model
            if (\App\Models\ExamAttendance::where('exam_id', $examId)->exists()) {
                return [
                    'saved' => 0,
                    'message' => 'Attendance already exists!'
                ];
            }

            $presentArr = [];
            $absentArr = [];
            $leaveArr = [];

            foreach ($attendanceArray as $value) {
                if (isset($value['present_student_id']))
                    $presentArr[] = $value['present_student_id'];
                if (isset($value['absent_student_id']))
                    $absentArr[] = $value['absent_student_id'];
                if (isset($value['leave_student_id']))
                    $leaveArr[] = $value['leave_student_id'];
            }

            // Process Present Students
            foreach ($presentArr as $studentId) {
                // Fetch required data for certificate
                // We need Belt Code, Exam Date, Exam Belt ID
                $examFee = DB::table('exam_fees as ef')
                    ->join('belt as b', 'ef.exam_belt_id', '=', 'b.belt_id')
                    ->join('exam as e', 'ef.exam_id', '=', 'e.exam_id')
                    ->where('ef.exam_id', $examId)
                    ->where('ef.student_id', $studentId)
                    ->where('ef.status', 1)
                    ->select('b.code', 'e.date', 'ef.exam_belt_id')
                    ->first();

                $certificateNo = '';
                if ($examFee) {
                    $date = str_replace('-', '', $examFee->date);
                    $certificateNo = $date . $examFee->code . $studentId . $examFee->exam_belt_id;

                    // Update Student Belt
                    \App\Models\Student::where('student_id', $studentId)->update(['belt_id' => $examFee->exam_belt_id]);
                }

                \App\Models\ExamAttendance::create([
                    'exam_id' => $examId,
                    'student_id' => $studentId,
                    'attend' => \App\Enums\AttendanceStatus::Present->value,
                    'user_id' => $userId,
                    'certificate_no' => $certificateNo,
                ]);
            }

            // Process Absent Students
            foreach ($absentArr as $studentId) {
                \App\Models\ExamAttendance::create([
                    'exam_id' => $examId,
                    'student_id' => $studentId,
                    'attend' => \App\Enums\AttendanceStatus::Absent->value,
                    'user_id' => $userId,
                    'certificate_no' => '',
                ]);
            }

            // Process Leave/Fail Students
            foreach ($leaveArr as $studentId) {
                \App\Models\ExamAttendance::create([
                    'exam_id' => $examId,
                    'student_id' => $studentId,
                    'attend' => \App\Enums\AttendanceStatus::Fail->value,
                    'user_id' => $userId,
                    'certificate_no' => '',
                ]);
            }

            DB::commit();

            return [
                'saved' => 1,
                'message' => 'Attendance Submitted.'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error marking exam attendance: ' . $e->getMessage());
            throw $e;
        }
    }
}


