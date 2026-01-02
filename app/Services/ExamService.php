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

    public function getAllExams(array $filters = [])
    {
        return $this->examRepository->all($filters);
    }

    public function getExamById(int $id)
    {
        $exam = $this->examRepository->find($id);
        
        if (!$exam) {
            throw new Exception('Exam not found', 404);
        }

        return $exam;
    }

    public function getPublishedExams(array $filters = [])
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
        return $this->updateExam($id, ['isPublished' => 1]);
    }

    public function unpublishExam(int $id): bool
    {
        return $this->updateExam($id, ['isPublished' => 0]);
    }
}


