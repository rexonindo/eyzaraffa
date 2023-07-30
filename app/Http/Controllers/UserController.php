<?php

namespace App\Http\Controllers;

use App\Models\CompanyGroupAccess;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $RSRoles = [];
    protected $dedicatedConnection;

    public function __construct()
    {
        $this->RSRoles = Role::select('*')->where('name', '!=', 'root')->get();
        $this->dedicatedConnection = Crypt::decryptString($_COOKIE['CGID']);
    }

    public function index()
    {
        return view('user_registration', ['RSRoles' => $this->RSRoles]);
    }

    function formManagement()
    {
        return view('user_management', ['RSRoles' => $this->RSRoles]);
    }

    public function simpan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:App\Models\User|email:rfc,dns',
            'name' => 'required',
            'nick_name' => 'required|unique:App\Models\User',
            'password' => 'required|min:8',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 406);
        }

        # sekalian daftarkan ke Company Group
        $selectedConnection = Crypt::decryptString($_COOKIE['CGID']);
        $validator = Validator::make([
            'nick_name' => $request->nick_name,
            'connection' => $selectedConnection,
            'role_name' => $request->role,
        ], [
            'nick_name' => 'required',
            'connection' => 'required',
            'role_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 406);
        }

        User::create([
            'name' => $request->name,
            'nick_name' => $request->nick_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'active' => 1,
            'role' => $request->role,
        ]);

        $remarks = [];

        if (
            DB::table('COMPANY_GROUP_ACCESSES')
            ->where('nick_name', $request->nick_name)
            ->where('connection', $selectedConnection)
            ->whereNull('deleted_at')
            ->count() > 0
        ) {
            $remarks = ['Already registered'];
        } else {
            CompanyGroupAccess::create([
                'nick_name' => $request->nick_name,
                'connection' => $selectedConnection,
                'role_name' => $request->role,
                'created_by' => Auth::user()->nick_name,
            ]);
        }

        return ['msg' => 'OK', 'remarks' => $remarks];
    }

    function search(Request $request)
    {
        $columnMap = [
            'email',
            'name',
        ];
        $RS = User::select('*')
            ->where($columnMap[$request->searchBy], 'like', '%' . $request->searchValue . '%')->get();
        return ['data' => $RS];
    }

    function getPerCompanyGroup()
    {
        $RS = User::select('users.id', 'users.name', 'email', 'users.created_at', 'role_name', 'description', 'active', 'description', 'users.nick_name')
        ->leftJoin('COMPANY_GROUP_ACCESSES', 'users.nick_name', '=', 'COMPANY_GROUP_ACCESSES.nick_name')
            ->leftJoin('roles', 'role_name', '=', 'roles.name')
            ->where('connection', $this->dedicatedConnection)
            ->whereNull('deleted_at')
            ->get();
        return ['data' => $RS];
    }

    function update(Request $request)
    {
        if (User::where('id', '!=', $request->id)
            ->where('email', '=', $request->email)->count()
        ) {
            return response()->json(['message' => 'email already used'], 406);
        }
        $affectedRow = User::where('id', $request->id)
            ->update([
                'name' => $request->name, 'email' => $request->email, 'active' => $request->active, 'role' => $request->role
            ]);

        CompanyGroupAccess::where('connection', $this->dedicatedConnection)
            ->whereNull('deleted_at')->where('nick_name', $request->nick_name)
            ->update(['role_name' => $request->role]);
        return ['msg' => $affectedRow ? 'OK' : 'No changes'];
    }

    function resetPassword(Request $request)
    {
        $affectedRow = User::where('id', $request->id)
            ->update([
                'password' => Hash::make($request->newPassword),
            ]);
        return ['msg' => $affectedRow ? 'OK' : 'No changes'];
    }
}
