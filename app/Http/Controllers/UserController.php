<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Danh sách tài khoản nhân viên.
     */
    public function index(Request $request): Response
    {
        $search = $request->search;
        $role   = $request->role;

        $users = User::with('roles')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->when($role, function ($query, $role) {
                $query->role($role);
            })
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::pluck('name');

        return Inertia::render('Users/Index', [
            'users'   => $users,
            'roles'   => $roles,
            'filters' => [
                'search' => $search,
                'role'   => $role ?? '',
            ],
        ]);
    }

    /**
     * Form tạo tài khoản nhân viên mới.
     */
    public function create(): Response
    {
        $roles = [
            'admin'        => 'Quản trị viên (Toàn quyền hệ thống)',
            'vet'          => 'Bác sĩ Thú y (Khám bệnh, kê đơn, tra cứu bệnh án)',
            'receptionist' => 'Lễ tân (Tiếp đón, đặt lịch, check-in, thu ngân)',
            'accountant'   => 'Kế toán (Quản lý hóa đơn, phiếu chi, xem báo cáo)',
        ];

        return Inertia::render('Users/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Lưu tài khoản mới.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'password'  => $validated['password'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')
            ->with('success', "Đã tạo tài khoản cho nhân viên {$user->name} thành công!");
    }

    /**
     * Form chỉnh sửa tài khoản.
     */
    public function edit(User $user): Response
    {
        $user->load('roles');

        $roles = [
            'admin'        => 'Quản trị viên (Toàn quyền hệ thống)',
            'vet'          => 'Bác sĩ Thú y (Khám bệnh, kê đơn, tra cứu bệnh án)',
            'receptionist' => 'Lễ tân (Tiếp đón, đặt lịch, check-in, thu ngân)',
            'accountant'   => 'Kế toán (Quản lý hóa đơn, phiếu chi, xem báo cáo)',
        ];

        return Inertia::render('Users/Edit', [
            'user'        => $user,
            'currentRole' => $user->roles->first()?->name ?? 'receptionist',
            'roles'       => $roles,
        ]);
    }

    /**
     * Cập nhật thông tin tài khoản.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $updateData = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')
            ->with('success', "Đã cập nhật thông tin tài khoản {$user->name} thành công!");
    }

    /**
     * Bật / Tắt trạng thái kích hoạt tài khoản.
     */
    public function toggleStatus(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản của chính mình!');
        }

        $user->update([
            'is_active' => !$user->is_active
        ]);

        $statusText = $user->is_active ? 'Kích hoạt' : 'Khóa';
        return back()->with('success', "Đã {$statusText} tài khoản {$user->name} thành công!");
    }

    /**
     * Xóa tài khoản nhân viên.
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Bạn không thể tự xóa tài khoản của chính mình!');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Đã xóa tài khoản nhân viên {$user->name} khỏi hệ thống!");
    }
}
