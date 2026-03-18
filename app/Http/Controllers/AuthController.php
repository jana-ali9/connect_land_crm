<?php

namespace App\Http\Controllers;

use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function gologin()
    {
        if (Auth::user())
            return to_route('any');
        return view('auth.signin');
    }
    public function login(Request $request)
    {
        $remember = $request->filled('remember') ? true : false;

        if (Auth::attempt($request->only('email', 'password'), $remember)) {
            return to_route('any');
        } else {
            return redirect()->back()->with('errors', 'Enter correct data');
        }
    }

    public function logout()
    {
        Auth::logout();
        return to_route('second', ['auth', 'signin']);
    }


    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%$search%")
                ->orWhere('email', 'LIKE', "%$search%");
        }
        $alladmins = $query->paginate(10)->appends(['search' => $request->search]);
        return view('admins.index', compact('alladmins'));
    }
    public function create()
    {
        $allroles = Roles::all();
        return view('admins.create', compact('allroles'));
    }
    public function store()
    {
        request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:6',
            'role_id' => 'required'
        ]);
        $data = request()->all();
        User::create([
            "name" => $data['name'],
            "email" => $data['email'],
            "password" => $data['password'],
            "role_id" => $data['role_id'],
        ]);
        session()->flash('success', "Role addes success");
        return to_route("admins.index");
    }
    public function edit(User $user)
    {
        $allroles = Roles::all();
        return view('admins.edit', compact('user', 'allroles'));
    }
    public function update(User $user)
    {
        request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role_id' => 'required'
        ]);
        $data = request()->all();
        if ($user->role_id == 1 && $data['role_id'] != 1) {
            $superAdminUsersCount = User::where('role_id', 1)->count();

            if ($user->role_id == 1 && $superAdminUsersCount <= 1) {
                return redirect()->back()->with('errors', "Can't edit the only Super Admin.");
            }
        }
        $user->update([
            "name" => $data['name'],
            "email" => $data['email'],
            "password" => $data['password'] ?? $user->password,
            "role_id" => $data['role_id'],
        ]);
        session()->flash('success', "we update role named $user->name");

        return to_route("admins.index");
    }
    public function destroy(User $user)
    {
        // ✅ جلب جميع المستخدمين الذين لديهم دور `Super Admin`
        $superAdminRole = Roles::where('name', 'Super Admin')->first();

        // ✅ التأكد من وجود الدور في النظام
        if (!$superAdminRole) {
            return redirect()->back()->with('errors', 'Super Admin role not found.');
        }

        // ✅ عدّ المستخدمين الذين لديهم دور `Super Admin`
        $superAdminUsersCount = User::where('role_id', $superAdminRole->id)->count();

        // ✅ إذا كان المستخدم المراد حذفه هو الوحيد الذي لديه `Super Admin` → لا يمكن حذفه
        if ($user->role_id == $superAdminRole->id && $superAdminUsersCount <= 1) {
            return redirect()->back()->with('errors', "Can't remove the only Super Admin.");
        }

        // ✅ حذف المستخدم إذا لم يكن الشرط السابق صحيحًا
        $user->delete();
        session()->flash('success', "User {$user->name} has been deleted.");

        return to_route("admins.index");
    }
}
