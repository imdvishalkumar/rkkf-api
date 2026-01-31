<?php

namespace App\Services;

use App\Repositories\Contracts\FeeRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\CouponRepositoryInterface;
use App\Models\Student;
use App\Models\Fee;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class FeeService
{
    protected $feeRepository;
    protected $studentRepository;
    protected $couponRepository;

    public function __construct(
        FeeRepositoryInterface $feeRepository,
        StudentRepositoryInterface $studentRepository,
        CouponRepositoryInterface $couponRepository
    ) {
        $this->feeRepository = $feeRepository;
        $this->studentRepository = $studentRepository;
        $this->couponRepository = $couponRepository;
    }

    public function getAllFees(array $filters = [])
    {
        return $this->feeRepository->all($filters);
    }

    public function getPaginatedFees(array $filters = [], int $perPage = 15)
    {
        return $this->feeRepository->paginate($filters, $perPage);
    }

    public function getFeeById(int $id)
    {
        $fee = $this->feeRepository->find($id);

        if (!$fee) {
            throw new Exception('Fee not found', 404);
        }

        return $fee;
    }

    public function getFeesByStudent(int $studentId, array $filters = [])
    {
        $student = $this->studentRepository->find($studentId);

        if (!$student) {
            throw new Exception('Student not found', 404);
        }

        return $this->feeRepository->getByStudent($studentId, $filters);
    }

    public function createFee(array $data): array
    {
        DB::beginTransaction();

        try {
            $student = $this->studentRepository->find($data['student_id']);

            if (!$student) {
                throw new Exception('Student not found', 404);
            }

            if (isset($data['coupon_id'])) {
                $coupon = $this->couponRepository->find($data['coupon_id']);
                if (!$coupon) {
                    throw new Exception('Invalid coupon', 422);
                }
            }

            $fee = $this->feeRepository->create($data);

            DB::commit();

            return [
                'fee' => $fee,
                'message' => 'Fee created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating fee: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateFee(int $id, array $data): array
    {
        $fee = $this->feeRepository->find($id);

        if (!$fee) {
            throw new Exception('Fee not found', 404);
        }

        $updated = $this->feeRepository->update($id, $data);

        if (!$updated) {
            throw new Exception('Failed to update fee', 500);
        }

        return [
            'fee' => $this->feeRepository->find($id),
            'message' => 'Fee updated successfully'
        ];
    }

    public function deleteFee(int $id): bool
    {
        $fee = $this->feeRepository->find($id);

        if (!$fee) {
            throw new Exception('Fee not found', 404);
        }

        return $this->feeRepository->delete($id);
    }

    public function getFeesByYear(int $year, array $filters = [])
    {
        return $this->feeRepository->getByYear($year, $filters);
    }

    public function getFeesByMonth(int $month, int $year, array $filters = [])
    {
        return $this->feeRepository->getByMonth($month, $year, $filters);
    }

    /**
     * Calculate due fees for a student.
     * Replicates logic from legacy get_fees_info.php
     *
     * @param int $studentId
     * @return array
     */
    public function calculateDueFees(int $studentId): array
    {
        $student = Student::with(['branch', 'belt'])->find($studentId);

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student not found',
            ];
        }

        // Get the last paid fee record
        $lastPaidFee = Fee::where('student_id', $studentId)
            ->orderBy('year', 'desc')
            ->orderBy('months', 'desc')
            ->first();

        if (!$lastPaidFee) {
            return [
                'success' => false,
                'message' => 'No fees found for this student',
            ];
        }

        // Check if student is a Black Belt
        $isBlackBelt = $this->isBlackBelt($studentId);

        if ($isBlackBelt) {
            return $this->calculateBlackBeltFees($lastPaidFee);
        }

        // Get branch details
        $branch = $student->branch;
        $branchId = $branch->branch_id;
        $monthlyFees = $branch->fees;
        $lateFees = $branch->late;
        $discountFees = $branch->discount;

        // Check if student is a fastrack student
        $fastrackData = $this->getFastrackInfo($studentId);
        $isFastrack = $fastrackData !== null;

        if ($isFastrack) {
            $monthlyFees = $fastrackData['total_fees'] / ($fastrackData['months_diff'] + 1);
        }

        // Calculate late fees based on months difference
        $monthCount = $this->calculateMonthsDifference($lastPaidFee->year, $lastPaidFee->months);
        $lateFee = $lateFees * max(0, $monthCount - 1);

        // Get 2024 branch IDs (branches with '2024' in name, excluding 86, 84, 85)
        $branch2024Ids = $this->getBranch2024Ids();

        // Calculate due fees based on branch logic
        $dueRow = $this->calculateDueByBranch(
            $branchId,
            $lastPaidFee,
            $monthlyFees,
            $lateFee,
            $discountFees,
            $monthCount,
            $isFastrack,
            $branch2024Ids
        );

        return [
            'success' => true,
            'data' => $lastPaidFee->toArray(),
            'due' => $dueRow,
        ];
    }

    /**
     * Check if student is a Black Belt
     */
    private function isBlackBelt(int $studentId): bool
    {
        return Student::where('student_id', $studentId)
            ->whereRaw('belt_id >= (SELECT belt_id FROM belt WHERE name = ?)', ['Black Belt'])
            ->exists();
    }

    /**
     * Calculate fees for Black Belt students (yearly)
     */
    private function calculateBlackBeltFees($lastPaidFee): array
    {
        $monthStr = '';
        $month = (int) date('m');
        $year = (int) date('Y');

        for ($i = 0; $i < 12; $i++) {
            $monthStr .= ($i === 11) ? $month : $month . ',';
            if ($month === 12) {
                $month = 0;
            }
            $month++;
        }

        $dueRow = [
            [
                'feeFor' => '12 Month',
                'monthPay' => $monthStr,
                'yearPay' => $year,
                'amountPay' => 7000,
                'lateFee' => 0,
                'discountedFee' => 0,
                'showSpinner' => 0,
            ],
        ];

        return [
            'success' => true,
            'data' => $lastPaidFee->toArray(),
            'due' => $dueRow,
        ];
    }

    /**
     * Get fastrack information for a student if applicable
     */
    private function getFastrackInfo(int $studentId): ?array
    {
        $fastrack = DB::table('fastrack')
            ->select([
                'to_belt_id',
                'from_belt_id',
                DB::raw('(to_belt_id - from_belt_id) as belt_up'),
                DB::raw('TIMESTAMPDIFF(MONTH, from_date, to_date) as months_diff'),
                'total_fees',
            ])
            ->where('student_id', $studentId)
            ->whereRaw('from_date <= CURDATE()')
            ->whereRaw('to_date >= CURDATE()')
            ->first();

        return $fastrack ? (array) $fastrack : null;
    }

    /**
     * Calculate months difference between last paid and current date
     */
    private function calculateMonthsDifference(int $year, int $month): int
    {
        $lastPaidDate = Carbon::createFromFormat('Y-m-d', "{$year}-{$month}-01");
        $currentDate = Carbon::createFromFormat('Y-m-d', date('Y-m-01'));

        return $lastPaidDate->diffInMonths($currentDate);
    }

    /**
     * Get branch IDs that have '2024' in name (excluding 86, 84, 85)
     */
    private function getBranch2024Ids(): array
    {
        return Branch::where('name', 'LIKE', '%2024%')
            ->whereNotIn('branch_id', [86, 84, 85])
            ->pluck('branch_id')
            ->toArray();
    }

    /**
     * Calculate due fees based on branch logic
     */
    private function calculateDueByBranch(
        int $branchId,
        $lastPaidFee,
        float $monthlyFees,
        float $lateFee,
        float $discountFees,
        int $monthCount,
        bool $isFastrack,
        array $branch2024Ids
    ): array {
        $months = $lastPaidFee->months;
        $yearPay = $lastPaidFee->year;

        // Handle December case
        if ($months == 12) {
            $months = 0;
            $yearPay++;
        }

        $spinnerValue = max(0, $monthCount - 1);

        // Calculate month strings for multi-month payments
        $monthStrings = $this->calculateMonthStrings($months, $yearPay);

        // Determine if discount applies
        $discountedFee = $this->calculateDiscount($months, $yearPay, $discountFees);

        if ($isFastrack) {
            return $this->getFastrackDueRow($months, $yearPay, $monthlyFees, $lateFee, $monthStrings, $spinnerValue);
        }

        if ($branchId === 66) {
            return $this->getBranch66DueRow($months, $yearPay, $monthlyFees, $lateFee, $discountedFee, $monthStrings, $spinnerValue);
        }

        if ($branchId === 53) {
            return $this->getBranch53DueRow($months, $yearPay, $monthlyFees, $lateFee, $discountedFee, $monthStrings, $spinnerValue);
        }

        if ($branchId === 86) {
            return $this->getBranch86DueRow($months, $yearPay, $monthlyFees, $lateFee, $discountedFee, $monthStrings, $spinnerValue);
        }

        if (in_array($branchId, $branch2024Ids)) {
            return $this->getBranch2024DueRow($months, $yearPay, $monthlyFees, $lateFee, $discountedFee, $monthStrings, $spinnerValue);
        }

        if ($branchId === 84 || $branchId === 85) {
            return $this->getBranch84_85DueRow($months, $yearPay, $monthlyFees, $lateFee, $discountedFee, $monthStrings, $spinnerValue);
        }

        // Default/Standard branch logic
        return $this->getStandardDueRow($months, $yearPay, $monthlyFees, $lateFee, $discountedFee, $monthStrings, $spinnerValue);
    }

    /**
     * Calculate month strings for 1, 2, and 3 month payment options
     */
    private function calculateMonthStrings(int $months, int $yearPay): array
    {
        $result = [
            'oneMonth' => strval($months + 1),
            'twoMonths' => '',
            'threeMonths' => '',
        ];

        // Two months
        if ($months == 11) {
            $result['twoMonths'] = ($months + 1) . ',1';
        } else {
            $result['twoMonths'] = ($months + 1) . ',' . ($months + 2);
        }

        // Three months
        if ($months == 10) {
            $result['threeMonths'] = ($months + 1) . ',' . ($months + 2) . ',1';
        } elseif ($months == 11) {
            $result['threeMonths'] = ($months + 1) . ',1,2';
        } else {
            $result['threeMonths'] = ($months + 1) . ',' . ($months + 2) . ',' . ($months + 3);
        }

        return $result;
    }

    /**
     * Calculate discount based on payment timing
     */
    private function calculateDiscount(int $months, int $yearPay, float $discountFees): float
    {
        $currentDay = (int) date('d');
        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');

        // If paying before 15th of the due month
        if ($currentDay <= 15 && ($months + 1) == $currentMonth) {
            return $discountFees;
        }

        // Special condition for Dec 2024 / Jan 2025
        if (
            ($currentMonth == 12 && $currentYear == 2024 && $yearPay == 2025) ||
            ($currentMonth == 1 && $currentDay <= 15 && $currentYear == 2025 && $yearPay == 2025)
        ) {
            return $discountFees;
        }

        // If paying for future months
        if ($months >= $currentMonth && $yearPay == $currentYear) {
            return $discountFees;
        }

        return 0;
    }

    /**
     * Get due row for fastrack students
     */
    private function getFastrackDueRow(int $months, int $yearPay, float $monthlyFees, float $lateFee, array $monthStrings, int $spinnerValue): array
    {
        return [
            [
                'feeFor' => '1 Month',
                'monthPay' => $monthStrings['oneMonth'],
                'yearPay' => $yearPay,
                'amountPay' => $monthlyFees,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '2 Months',
                'monthPay' => $monthStrings['twoMonths'],
                'yearPay' => $yearPay,
                'amountPay' => $monthlyFees * 2,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '3 Months',
                'monthPay' => $monthStrings['threeMonths'],
                'yearPay' => $yearPay,
                'amountPay' => $monthlyFees * 3,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
        ];
    }

    /**
     * Get due row for branch 66 (Special discount structure)
     */
    private function getBranch66DueRow(int $months, int $yearPay, float $monthlyFees, float $lateFee, float $discountedFee, array $monthStrings, int $spinnerValue): array
    {
        return [
            [
                'feeFor' => '1 Month',
                'monthPay' => $monthStrings['oneMonth'],
                'yearPay' => $yearPay,
                'amountPay' => $monthlyFees,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '2 Months',
                'monthPay' => $monthStrings['twoMonths'],
                'yearPay' => $yearPay,
                'amountPay' => ($monthlyFees * 2) - 200,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '3 Months',
                'monthPay' => $monthStrings['threeMonths'],
                'yearPay' => $yearPay,
                'amountPay' => ($monthlyFees * 3) - 600,
                'lateFee' => $lateFee,
                'discountedFee' => $discountedFee,
                'showSpinner' => $spinnerValue,
            ],
        ];
    }

    /**
     * Get due row for branch 53 (Transaction fee structure)
     */
    private function getBranch53DueRow(int $months, int $yearPay, float $monthlyFees, float $lateFee, float $discountedFee, array $monthStrings, int $spinnerValue): array
    {
        return [
            [
                'feeFor' => '1 Month',
                'monthPay' => $monthStrings['oneMonth'],
                'yearPay' => $yearPay,
                'amountPay' => $monthlyFees + 21,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '2 Months',
                'monthPay' => $monthStrings['twoMonths'],
                'yearPay' => $yearPay,
                'amountPay' => ($monthlyFees * 2 + 42) - 100,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '3 Months',
                'monthPay' => $monthStrings['threeMonths'],
                'yearPay' => $yearPay,
                'amountPay' => ($monthlyFees * 3 + 63) - 300,
                'lateFee' => $lateFee,
                'discountedFee' => $discountedFee,
                'showSpinner' => $spinnerValue,
            ],
        ];
    }

    /**
     * Get due row for branch 86 (Special discount structure)
     */
    private function getBranch86DueRow(int $months, int $yearPay, float $monthlyFees, float $lateFee, float $discountedFee, array $monthStrings, int $spinnerValue): array
    {
        return [
            [
                'feeFor' => '1 Month',
                'monthPay' => $monthStrings['oneMonth'],
                'yearPay' => $yearPay,
                'amountPay' => $monthlyFees,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '2 Months',
                'monthPay' => $monthStrings['twoMonths'],
                'yearPay' => $yearPay,
                'amountPay' => ($monthlyFees * 2) - 100,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '3 Months',
                'monthPay' => $monthStrings['threeMonths'],
                'yearPay' => $yearPay,
                'amountPay' => ($monthlyFees * 3) - 300,
                'lateFee' => $lateFee,
                'discountedFee' => $discountedFee,
                'showSpinner' => $spinnerValue,
            ],
        ];
    }

    /**
     * Get due row for 2024 branches (Bulk discount structure)
     */
    private function getBranch2024DueRow(int $months, int $yearPay, float $monthlyFees, float $lateFee, float $discountedFee, array $monthStrings, int $spinnerValue): array
    {
        return [
            [
                'feeFor' => '1 Month',
                'monthPay' => $monthStrings['oneMonth'],
                'yearPay' => $yearPay,
                'amountPay' => $monthlyFees,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '2 Months',
                'monthPay' => $monthStrings['twoMonths'],
                'yearPay' => $yearPay,
                'amountPay' => ($monthlyFees * 2) - 200,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '3 Months',
                'monthPay' => $monthStrings['threeMonths'],
                'yearPay' => $yearPay,
                'amountPay' => ($monthlyFees * 3) - 600,
                'lateFee' => $lateFee,
                'discountedFee' => $discountedFee,
                'showSpinner' => $spinnerValue,
            ],
        ];
    }

    /**
     * Get due row for branches 84 and 85 (Transaction fee + discount structure)
     */
    private function getBranch84_85DueRow(int $months, int $yearPay, float $monthlyFees, float $lateFee, float $discountedFee, array $monthStrings, int $spinnerValue): array
    {
        return [
            [
                'feeFor' => '1 Month',
                'monthPay' => $monthStrings['oneMonth'],
                'yearPay' => $yearPay,
                'amountPay' => $monthlyFees + 21,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '2 Months',
                'monthPay' => $monthStrings['twoMonths'],
                'yearPay' => $yearPay,
                'amountPay' => ($monthlyFees * 2 + 42) - 200,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '3 Months',
                'monthPay' => $monthStrings['threeMonths'],
                'yearPay' => $yearPay,
                'amountPay' => ($monthlyFees * 3 + 63) - 600,
                'lateFee' => $lateFee,
                'discountedFee' => $discountedFee,
                'showSpinner' => $spinnerValue,
            ],
        ];
    }

    /**
     * Get due row for standard branches (with platform fee)
     */
    private function getStandardDueRow(int $months, int $yearPay, float $monthlyFees, float $lateFee, float $discountedFee, array $monthStrings, int $spinnerValue): array
    {
        return [
            [
                'feeFor' => '1 Month',
                'monthPay' => $monthStrings['oneMonth'],
                'yearPay' => $yearPay,
                'amountPay' => $monthlyFees + 300,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '2 Months',
                'monthPay' => $monthStrings['twoMonths'],
                'yearPay' => $yearPay,
                'amountPay' => ($monthlyFees * 2) + 400,
                'lateFee' => $lateFee,
                'discountedFee' => 0,
                'showSpinner' => $spinnerValue,
            ],
            [
                'feeFor' => '3 Months',
                'monthPay' => $monthStrings['threeMonths'],
                'yearPay' => $yearPay,
                'amountPay' => $monthlyFees * 3,
                'lateFee' => $lateFee,
                'discountedFee' => $discountedFee,
                'showSpinner' => $spinnerValue,
            ],
        ];
    }

    /**
     * Month number to name mapping
     */
    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'JAN',
            2 => 'FEB',
            3 => 'MAR',
            4 => 'APR',
            5 => 'MAY',
            6 => 'JUN',
            7 => 'JUL',
            8 => 'AUG',
            9 => 'SEP',
            10 => 'OCT',
            11 => 'NOV',
            12 => 'DEC'
        ];
        return $months[$month] ?? '';
    }

    /**
     * Convert comma-separated month numbers to readable month names
     */
    private function formatMonthsDisplay(string $months, int $year): string
    {
        $monthNumbers = explode(',', $months);
        $monthNames = array_map(fn($m) => $this->getMonthName((int) $m), $monthNumbers);
        return implode(' ', $monthNames) . ' ' . $year;
    }

    /**
     * Get fees summary for a student (for Fees Summary screen).
     * Returns upcoming payment info and payment options for 3, 6, and 12 months.
     *
     * @param int $studentId
     * @return array
     */
    public function getFeesSummary(int $studentId): array
    {
        $student = Student::with(['branch', 'belt'])->find($studentId);

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student not found',
            ];
        }

        // Get the last paid fee record
        $lastPaidFee = Fee::where('student_id', $studentId)
            ->orderBy('year', 'desc')
            ->orderBy('months', 'desc')
            ->first();

        if (!$lastPaidFee) {
            return [
                'success' => false,
                'message' => 'No fees found for this student',
            ];
        }

        // Get branch details
        $branch = $student->branch;
        $branchId = $branch->branch_id;
        $monthlyFees = $branch->fees;
        $lateFees = $branch->late;
        $discountFees = $branch->discount;

        // Check fastrack
        $fastrackData = $this->getFastrackInfo($studentId);
        $isFastrack = $fastrackData !== null;
        if ($isFastrack) {
            $monthlyFees = $fastrackData['total_fees'] / ($fastrackData['months_diff'] + 1);
        }

        // Calculate months difference for late fees
        $monthCount = $this->calculateMonthsDifference($lastPaidFee->year, $lastPaidFee->months);
        $lateFee = $lateFees * max(0, $monthCount - 1);

        // Calculate next due months
        $lastMonth = $lastPaidFee->months;
        $year = $lastPaidFee->year;

        if ($lastMonth == 12) {
            $lastMonth = 0;
            $year++;
        }

        // Generate payment options for 3, 6, and 12 months
        $paymentOptions = [];
        $durations = [3, 6, 12];

        foreach ($durations as $numMonths) {
            $monthsArray = [];
            $currentMonth = $lastMonth;
            $currentYear = $year;

            for ($i = 0; $i < $numMonths; $i++) {
                $currentMonth++;
                if ($currentMonth > 12) {
                    $currentMonth = 1;
                    $currentYear++;
                }
                $monthsArray[] = $currentMonth;
            }

            $monthsStr = implode(',', $monthsArray);

            // Calculate amount based on duration and branch logic
            $baseAmount = $monthlyFees * $numMonths;
            $discount = 0;

            // Apply branch-specific discounts
            if ($branchId === 66 || in_array($branchId, $this->getBranch2024Ids())) {
                if ($numMonths >= 3)
                    $discount = 200 * ($numMonths / 3);
            } elseif ($branchId === 86) {
                if ($numMonths >= 3)
                    $discount = 100 * ($numMonths / 3);
            } elseif ($branchId === 53 || $branchId === 84 || $branchId === 85) {
                $baseAmount += 21 * $numMonths; // Transaction fees
                if ($numMonths >= 3)
                    $discount = 100 * ($numMonths / 3);
            } else {
                // Standard branches - platform fee reduction for bulk
                if ($numMonths == 3) {
                    $baseAmount = $monthlyFees * 3; // No platform fee for 3 months
                } elseif ($numMonths == 6) {
                    $discount = 500;
                } elseif ($numMonths == 12) {
                    $discount = 2000;
                }
            }

            $durationLabel = $numMonths == 12 ? '1 Year' : $numMonths . ' Months';
            $monthNames = array_map(fn($m) => $this->getMonthName($m), $monthsArray);

            $paymentOptions[] = [
                'duration' => $durationLabel,
                'months' => $monthsStr,
                'months_display' => implode(' ', $monthNames),
                'year' => $year,
                'amount' => round($baseAmount, 2),
                'late_fee' => round($lateFee, 2),
                'discount' => round($discount, 2),
                'total' => round($baseAmount + $lateFee - $discount, 2),
            ];
        }

        // Calculate upcoming payment display (next 3 months)
        $upcomingMonthsStr = $paymentOptions[0]['months_display'];

        return [
            'success' => true,
            'upcoming_payment' => [
                'due_months' => $upcomingMonthsStr,
                'due_year' => $year,
                'due_months_display' => $upcomingMonthsStr . ' ' . $year,
            ],
            'last_paid' => [
                'fee_id' => $lastPaidFee->fee_id,
                'amount' => $lastPaidFee->amount,
                'date' => $lastPaidFee->date ? $lastPaidFee->date->format('Y-m-d') : null,
                'months' => $lastPaidFee->months,
                'year' => $lastPaidFee->year,
                'mode' => $lastPaidFee->mode,
                'display_text' => $this->getMonthName((int) $lastPaidFee->months) . ' ' . $lastPaidFee->year
            ],
            'payment_options' => $paymentOptions,
            'student' => [
                'name' => $student->firstname . ' ' . $student->lastname,
                'branch' => $branch->name,
            ],
        ];
    }

    /**
     * Get payment history for a student (for Payment History screen).
     *
     * @param int $studentId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int $perPage
     * @return array
     */
    /**
     * Get payment history for a student (for Payment History screen).
     * Includes both online (Transaction) and manual (Fee) records.
     *
     * @param int $studentId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int $perPage
     * @return array
     */
    public function getPaymentHistory(int $studentId, ?string $startDate = null, ?string $endDate = null, int $perPage = 15): array
    {
        // 1. Fetch Online Transactions (Success, Failed, Pending)
        $transactionQuery = \App\Models\Transaction::where('student_id', $studentId)
            ->where('type', 'fees')
            ->orderBy('date', 'desc');

        if ($startDate) {
            $transactionQuery->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $transactionQuery->where('date', '<=', $endDate);
        }

        $transactions = $transactionQuery->get();

        // 2. Fetch Manual/Cash Fees (Exclude order_%)
        $feeQuery = Fee::where('student_id', $studentId)
            ->where(function ($q) {
                $q->whereNull('mode')
                    ->orWhere('mode', 'not like', 'order_%');
            })
            ->orderBy('date', 'desc');

        if ($startDate) {
            $feeQuery->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $feeQuery->where('date', '<=', $endDate);
        }

        $fees = $feeQuery->get();

        // 3. Process and Merge Data
        $mergedHistory = [];

        // Process Transactions
        foreach ($transactions as $txn) {
            $statusCode = $txn->status;
            $status = match ($statusCode) {
                1 => 'Success',
                -1 => 'Failed',
                default => 'Pending',
            };

            $monthNames = [];
            if ($txn->months) {
                $monthNums = explode(',', $txn->months);
                sort($monthNums);
                $monthNames = array_map(fn($m) => $this->getMonthName((int) $m), $monthNums);
            }

            $mergedHistory[] = [
                'id' => $txn->transcation_id,
                'type' => 'online',
                'status' => $status,
                'status_code' => $statusCode,
                'amount' => round((float) $txn->amount, 2),
                'billingMonth' => implode(' ', $monthNames) . ' ' . $txn->year,
                'billingDuration' => count($monthNames) . ' month' . (count($monthNames) !== 1 ? 's' : ''),
                'payment_mode' => 'Online',
                'reference_id' => $txn->order_id,
                'description' => 'Online • ' . ($txn->order_id ?: 'TXN' . str_pad($txn->transcation_id, 8, '0', STR_PAD_LEFT)),
                'date' => $txn->date ? $txn->date->format('Y-m-d') : null,
                'download_invoice_url' => route('api.fees.invoice.download', ['id' => $txn->transcation_id, 'type' => 'online']),
                'timestamp' => $txn->date ? $txn->date->timestamp : 0,
            ];
        }

        // Group Manual Fees by Date + Mode (to simulate transactions)
        $groupedFees = [];
        foreach ($fees as $fee) {
            // Group key: Date + Mode. If mode is empty, assume 'Cash'.
            $dateStr = $fee->date ? $fee->date->format('Y-m-d') : 'unknown';
            $modeStr = $fee->mode ?: 'Cash';
            $key = $dateStr . '_' . $modeStr;

            if (!isset($groupedFees[$key])) {
                $groupedFees[$key] = [
                    'id' => $fee->fee_id,
                    'amount' => 0,
                    'months' => [],
                    'year' => $fee->year,
                    'date' => $fee->date,
                    'mode' => $modeStr,
                ];
            }

            $groupedFees[$key]['amount'] += $fee->amount;
            $groupedFees[$key]['months'][] = $fee->months;
        }

        // Process Grouped Manual Fees
        foreach ($groupedFees as $group) {
            $monthNums = $group['months'];
            sort($monthNums);
            $monthNames = array_map(fn($m) => $this->getMonthName((int) $m), $monthNums);

            // Determine payment mode label and reference ID
            $modeLabel = match (strtolower($group['mode'])) {
                'cash' => 'Cash',
                'upi' => 'UPI',
                'cheque' => 'Cheque',
                'bank', 'neft', 'rtgs', 'imps' => 'Bank Transfer',
                default => 'Manual',
            };

            // Reference ID: use transaction reference or receipt number
            $receiptNumber = 'REC' . str_pad($group['id'], 8, '0', STR_PAD_LEFT);
            $referenceId = $group['mode'] && strtolower($group['mode']) !== 'cash'
                ? strtoupper($group['mode'])
                : $receiptNumber;

            $mergedHistory[] = [
                'id' => $group['id'],
                // 'type' => 'manual',
                'status' => 'Success',
                'status_code' => 1,
                'amount' => round((float) $group['amount'], 2),
                'billingMonth' => implode(' ', $monthNames) . ' ' . $group['year'],
                'billingDuration' => count($monthNums) . ' month' . (count($monthNums) !== 1 ? 's' : ''),
                'payment_mode' => $modeLabel,
                // 'reference_id' => $referenceId,
                'description' => $modeLabel . ' • ' . $referenceId,
                'date' => $group['date'] ? $group['date']->format('Y-m-d') : null,
                'download_invoice_url' => route('api.fees.invoice.download', ['id' => $group['id'], 'type' => 'manual']),
                'timestamp' => $group['date'] ? $group['date']->timestamp : 0,
            ];
        }

        // 4. Sort by Date Descending
        usort($mergedHistory, function ($a, $b) {
            // Sort by date (timestamp) desc
            if ($a['timestamp'] === $b['timestamp']) {
                return 0;
            }
            return ($a['timestamp'] > $b['timestamp']) ? -1 : 1;
        });

        // 5. Manual Pagination
        $total = count($mergedHistory);
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $offset = ($currentPage - 1) * $perPage;
        $items = array_slice($mergedHistory, $offset, $perPage);

        return [
            'success' => true,
            'payments' => $items,
            'pagination' => [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, ceil($total / $perPage)),
            ],
        ];
    }
}
