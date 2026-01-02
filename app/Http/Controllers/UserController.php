<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Enums\UserRole;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [];
        
        if ($request->has('role')) {
            $filters['role'] = $request->role;
        }

        $users = $this->userService->getAllUsers($filters);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = UserRole::cases();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $result = $this->userService->createUser($request->validated());
            return redirect()->route('users.index')
                ->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error creating user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $user = $this->userService->getUserById($id);
            return view('users.show', compact('user'));
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        try {
            $user = $this->userService->getUserById($id);
            $roles = UserRole::cases();
            return view('users.edit', compact('user', 'roles'));
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        try {
            $data = $request->validated();
            
            // Remove password from update if empty
            if (isset($data['password']) && empty($data['password'])) {
                unset($data['password']);
            }

            $result = $this->userService->updateUser($id, $data);
            return redirect()->route('users.index')
                ->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $this->userService->deleteUser($id);
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }
}
