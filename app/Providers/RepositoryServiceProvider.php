<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\StudentRepository;
use App\Repositories\Contracts\FeeRepositoryInterface;
use App\Repositories\FeeRepository;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\BranchRepository;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\AttendanceRepository;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\Contracts\ExamRepositoryInterface;
use App\Repositories\ExamRepository;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\EventRepository;
use App\Repositories\Contracts\CouponRepositoryInterface;
use App\Repositories\CouponRepository;
use App\Repositories\Contracts\BeltRepositoryInterface;
use App\Repositories\BeltRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     * 
     * Bind repository interfaces to their implementations.
     * This allows dependency injection to work properly.
     */
    public function register(): void
    {
        // Student Repository
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        
        // Fee Repository
        $this->app->bind(FeeRepositoryInterface::class, FeeRepository::class);
        
        // User Repository
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        
        // Branch Repository
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        
        // Attendance Repository
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        
        // Product Repository
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        
        // Order Repository
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        
        // Exam Repository
        $this->app->bind(ExamRepositoryInterface::class, ExamRepository::class);
        
        // Event Repository
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        
        // Coupon Repository
        $this->app->bind(CouponRepositoryInterface::class, CouponRepository::class);
        
        // Belt Repository
        $this->app->bind(BeltRepositoryInterface::class, BeltRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}


