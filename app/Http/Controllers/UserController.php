<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\UserTp;
use App\Models\OutletCall;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Services\DatabaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private UserRepository $repository;
    private RoleRepository $roleRepository;
    private DatabaseService $databaseService;

    public function __construct(UserRepository $userRepository, RoleRepository $roleRepository, DatabaseService $databaseService)
    {
        $this->repository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->databaseService = $databaseService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $title = 'User';
        $url = route('users.datatable');
        $roles = $this->roleRepository->findAll();
        $branch = self::branch();

        // Filter roles berdasarkan user yang login
        $currentUserRole = auth()->user()->role_id;

        // Jika Head Telemarketing, hanya tampilkan role CS dan Telemarketing
        if ($currentUserRole === '26be63b7-b410-46fc-a2f7-8dc3ed9e29ed') {
            $allowedRoleIds = ['a4e960e0-467f-4534-9555-f06ab8b901f7', '2629192e-1c3f-477e-a157-4def565dace3'];
            $roles = $roles->whereIn('id', $allowedRoleIds);
        }

        return view('users.user', compact([
            'title',
            'url',
            'roles',
            'branch'
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3',
                'username' => 'required|unique:users,username|min:5',
                'email' => 'nullable|unique:users,email|email',
                'residency' => 'required|array',
                'sbu_code' => 'required',
                'role_id' =>  'required',
                'tp' => 'nullable|array' // TP multiple
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
                ]);
            }

            $validated = $validator->validated();

            // Validasi role berdasarkan user yang login
            $currentUserRole = auth()->user()->role_id;
            if ($currentUserRole === '26be63b7-b410-46fc-a2f7-8dc3ed9e29ed') {
                $allowedRoleIds = ['a4e960e0-467f-4534-9555-f06ab8b901f7', '2629192e-1c3f-477e-a157-4def565dace3'];
                if (!in_array($validated['role_id'], $allowedRoleIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => ['role_id' => ['Role tidak diizinkan']]
                    ]);
                }
            }

            $validated['password'] = '12345678';

            // Extract TP data before creating user
            $tpData = $validated['tp'] ?? [];
            unset($validated['tp']); // Remove from user data

            $user = $this->repository->create($validated);

            // Save TP data if exists and role is specific
            if (!empty($tpData) && $validated['role_id'] === '2629192e-1c3f-477e-a157-4def565dace3') {
                foreach ($tpData as $tp) {
                    UserTp::create([
                        'user_id' => $user->id,
                        'tp' => $tp
                    ]);
                }
            }

            return response()->json([
                'success' => true
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $data = $this->repository->findById($id);

        if (is_null($data)) {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }

        // Include TP data
        $data->load('userTp');

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        try {
            $user = $this->repository->findById($id);
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3',
                'username' => 'required|unique:users,username,' . $user->id . '|min:5',
                'email' => 'nullable|unique:users,email,' . $user->id . '|email',
                'residency' => 'required|array',
                'sbu_code' => 'required',
                'role_id' =>  'required',
                'tp' => 'nullable|array' // TP multiple
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
                ]);
            }

            $validated = $validator->validated();

            // Validasi role berdasarkan user yang login
            $currentUserRole = auth()->user()->role_id;
            if ($currentUserRole === '26be63b7-b410-46fc-a2f7-8dc3ed9e29ed') {
                $allowedRoleIds = ['a4e960e0-467f-4534-9555-f06ab8b901f7', '2629192e-1c3f-477e-a157-4def565dace3'];
                if (!in_array($validated['role_id'], $allowedRoleIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => ['role_id' => ['Role tidak diizinkan']]
                    ]);
                }
            }

            // Extract TP data before updating user
            $tpData = $validated['tp'] ?? [];
            unset($validated['tp']); // Remove from user data

            $this->repository->update($validated, $id);

            // Update TP data if role is specific
            if ($validated['role_id'] === '2629192e-1c3f-477e-a157-4def565dace3' || $validated['role_id'] === 'a4e960e0-467f-4534-9555-f06ab8b901f7') {
                // Delete existing TP records
                UserTp::where('user_id', $id)->delete();

                // Create new TP records
                if (!empty($tpData)) {
                    foreach ($tpData as $tp) {
                        UserTp::create([
                            'user_id' => $id,
                            'tp' => $tp
                        ]);
                    }
                }
            } else {
                // If role changed and not TP role, remove all TP assignments
                UserTp::where('user_id', $id)->delete();
            }

            return response()->json([
                'success' => true
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->repository->destroy($id);

        return response()->json([
            'success' => true
        ]);
    }

    public function changePassword()
    {
        $title = 'Ganti Password';
        return view('users.change-password', compact([
            'title'
        ]));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed'
        ], [
            'old_password.required' => 'Password Lama wajib diisi',
            'new_password.required' => 'Password Baru wajib diisi',
            'new_password.confirmed' => 'Konfirmasi password baru salah'
        ]);

        if (!Hash::check($request->old_password, auth()->user()->password)) {
            return back()->with('error', 'Password Lama salah!');
        }

        $this->repository->updatePassword(['password' => Hash::make($request->new_password)], auth()->user()->id);

        return back()->with('success', 'Password berhasil diubah');
    }

    private function branch()
    {
        $apiURL = env('API_URL_DMLT');
        try {
            $response = Http::withHeaders([
                'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
            ])->withOptions(["verify" => false])->get($apiURL . 'branch');

            if ($response->successful()) {
                return $response->object();
            }
        } catch (ConnectException $e) {
            return $e->getMessage();
        }
    }

    public function getTpBySbu($sbu)
    {
        try {
            $tpData = $this->databaseService->callStoredProcedure('sp_master_tp', [], $sbu);

            return response()->json([
                'success' => true,
                'data' => $tpData['data']
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->repository->findAll();

            // Filter data berdasarkan role user yang login
            $currentUserRole = auth()->user()->role_id;

            // Jika Head Telemarketing, hanya tampilkan CS dan Telemarketing
            if ($currentUserRole === '26be63b7-b410-46fc-a2f7-8dc3ed9e29ed') {
                $allowedRoles = ['a4e960e0-467f-4534-9555-f06ab8b901f7', '2629192e-1c3f-477e-a157-4def565dace3'];
                $query = $query->whereIn('role_id', $allowedRoles);
            }

            return Datatables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $edit = '<a onclick="edit(this)" data-id="' . $row->id . '" class="btn btn-primary btn-warning">Edit</a>';
                    $delete = '<a onclick="destroy(this)" data-id="' . $row->id . '" class="btn btn-primary btn-danger">Delete</a>';
                    return $edit . ' ' . $delete;
                })
                ->addColumn('residency_raw', function ($row) {
                    if ($row->user_details_count > 0) {
                        return $row->userResidence;
                    }
                })
                ->rawColumns(['action', 'residency_raw'])
                // ->make(true);
                ->toJson();
        }
    }

    public function residency($sbu)
    {
        $apiURL = env('API_URL_DMLT');
        try {
            $response = Http::withHeaders([
                'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
            ])->withOptions(["verify" => false])->get($apiURL . 'karesidenan/' . $sbu);

            if ($response->successful()) {
                $response = $response->object();
                return $response->data;
            }
        } catch (ConnectException $e) {
            return $e->getMessage();
        }
    }
}
