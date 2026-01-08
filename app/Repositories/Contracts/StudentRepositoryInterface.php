<?php

namespace App\Repositories\Contracts;

use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface StudentRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Student;

    public function findByEmail(string $email): ?Student;

    public function search(string $term, array $filters = []): Collection;

    public function create(array $data): Student;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function activate(int $id): bool;

    public function deactivate(int $id): bool;

    public function resetPassword(int $id, string $password): bool;

    public function getByBranch(int $branchId, array $filters = []): Collection;

    public function getByBelt(int $beltId, array $filters = []): Collection;

    public function getByDateRange(string $startDate, string $endDate, array $filters = []): Collection;

    public function checkEmailExists(string $email, ?int $excludeId = null): bool;
}


