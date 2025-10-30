<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Repositories\TransactionRepository;

class ComplainController extends Controller
{
    private TransactionRepository $repository;

    public function __construct(TransactionRepository $transactionRepository) {
        $this->repository = $transactionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $residency = self::residency();
        $title = 'Complaint';
        $url = route('complaints.datatable');
        
        return view('complaints.complaint', compact([
            'title', 'url', 'residency'
        ]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $residency = self::residency();
        $title = 'Tambah Complaint';

        return view('complaints.create', compact([
            'title', 'residency'
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'residency' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'outlet_code' => 'required_if:type,pelanggan',
            'outlet_name' => 'required_if:type,non',
            'description' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ]);
        }
        $validated = $validator->validated();

        if($validated['type'] == 'pelanggan') {
            $outlet = self::outletById($validated['residency'], $validated['city'], $validated['district'], $validated['outlet_code']);
            $validated['outlet_name'] = $outlet->outlet_name;
            $validated['outlet_address'] = $outlet->outlet_alamat;
            $validated['outlet_owner'] = $outlet->outlet_pemilik;
            $validated['outlet_phone'] = $outlet->no_telp;
            $validated['outlet_longitude'] = $outlet->outlet_longitude;
            $validated['outlet_latitude'] = $outlet->outlet_latitude;
            $validated['residency_name'] = $outlet->nama_karesidenan;
            $validated['city_name'] = $outlet->nama_kabupaten;
            $validated['district_name'] = $outlet->nama_kecamatan;
        }
        elseif($validated['type'] == 'non') {
            $validated['residency_name'] = $request->residency_name;
            $validated['city_name'] = $request->city_name;
            $validated['district_name'] = $request->district_name;
        }

        $validated['type_id'] = '47888059-07ce-433f-993a-1a6a7b112d48';

        $validated = Arr::except($validated, ['type']);

        $this->repository->create($validated);

        $request->session()->flash('success', 'Berhasil menambah complaint');
        
        return response()->json([
            'success' => true,
            'redirect' => route('complaints.index')
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($complaints)
    {
        $residency = self::residency();
        $title = 'Edit Complaint';
        $data = $this->repository->findById($complaints);
        return view('complaints.edit', compact([
            'title', 'residency', 'data', 'complaints'
        ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $complaints)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'residency' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'outlet_code' => 'required_if:type,pelanggan',
            'outlet_name' => 'required_if:type,non',
            'description' => 'nullable|string',
            'additional_description' => 'nullable|string',
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ]);
        }

        $validated = $validator->validated();

        if($validated['type'] == 'pelanggan') {
            $outlet = self::outletById($validated['residency'], $validated['city'], $validated['district'], $validated['outlet_code']);
            $validated['outlet_name'] = $outlet->outlet_name;
            $validated['outlet_address'] = $outlet->outlet_alamat;
            $validated['outlet_owner'] = $outlet->outlet_pemilik;
            $validated['outlet_phone'] = $outlet->no_telp;
            $validated['outlet_longitude'] = $outlet->outlet_longitude;
            $validated['outlet_latitude'] = $outlet->outlet_latitude;
            $validated['residency_name'] = $outlet->nama_karesidenan;
            $validated['city_name'] = $outlet->nama_kabupaten;
            $validated['district_name'] = $outlet->nama_kecamatan;
        }
        
        elseif($validated['type'] == 'non') {
            $validated['residency_name'] = $request->residency_name;
            $validated['city_name'] = $request->city_name;
            $validated['district_name'] = $request->district_name;
        }

        $validated = Arr::except($validated, ['type']);

        $this->repository->update($validated, $complaints);

        $request->session()->flash('success', 'Berhasil mengubah complaint');
        
        return response()->json([
            'success' => true,
            'redirect' => route('complaints.index')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($complaints)
    {
        $this->repository->destroy($complaints);

        return response()->json([
            'success' => true
        ]);
    }

    public function updateStatus($complaints, Request $request) {
        
        $this->repository->update(['status' => $request->status], $complaints);

        return response()->json([
            'success' => true
        ]);
    }

    public function cancel($complaint, Request $request) {
        $this->repository->update(['status' => 4], $complaint);

        return response()->json([
            'success' => true
        ]);
    }

    private function residency() {
        $apiURL = env('API_URL_DMLT');
        try {
            $response = Http::withHeaders([
                'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
            ])->withOptions(["verify"=>false])->get($apiURL.'karesidenan/'.auth()->user()->sbu_code);
            
            if($response->successful()) {
                $response = $response->object();
                return $response->data;
            }
        } catch (ConnectException $e) {
            return $e->getMessage();
        }
    }

    public function outletById($residency, $city, $district, $outlet) {
        $apiURL = env('API_URL_DMLT');
        try {
            $response = Http::withHeaders([
                'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
            ])->withOptions(["verify"=>false])->get($apiURL.'outlet/'.auth()->user()->sbu_code.'/'.$residency.'/'.$city.'/'.$district.'?outlet_code='.$outlet);
            
            if($response->successful()) {
                $response = $response->object();
                return $response->data[0];
                // return response()->json([
                //     'success' => true,
                //     'data' => $response->data[0]
                // ]);
            }
        } catch (ConnectException $e) {
            return $e->getMessage();
        }
    }

    public function datatable(Request $request) {
        if ($request->ajax()) {
            $data = $this->repository->findAll('CP');
            return Datatables::of($data)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if($request->get('residency')) {
                        $residency = $request->get('residency');
                        if($residency != 'All') {
                            $query->where('residency', $residency);
                        }
                    }
                    if($request->get('city')) {
                        $city = $request->get('city');
                        if($city != 'All') {
                            $query->where('city', $city);
                        }
                    }
                    if($request->get('district')) {
                        $district = $request->get('district');
                        if($district != 'All') {
                            $query->where('district', $district);
                        }
                    }
                    if(!empty($request->get('date_from')) && !empty($request->get('date_to'))) {
                        $query->whereBetween('created_at', array($request->get('date_from'), $request->get('date_to')));
                    }
                    if($request->get('type')) {
                        $type = $request->get('type');
                        if($type != 'All') {
                            if($type == 'pelanggan') {
                                $query->whereNotNull('outlet_code');
                            }
                            elseif($type == 'non') {
                                $query->whereNull('outlet_code');
                            }
                        }
                    }
                    if (!empty($request->get('search'))) {
                        $query->where(function($w) use($request){
                           $search = $request->get('search');
                           $w->orWhere('outlet_name', 'LIKE', "%$search%");
                           $w->orWhere('residency_name', 'LIKE', "%$search%");
                       });
                    }
                })
                ->addColumn('status_txt', function ($row) {
                    $activeOpen = '';
                    $activeClose = '';
                    $activeHold = '';
                    $status = '';
                    $color = '';

                    if($row->status == 1) {
                        $activeOpen = 'active';
                        $activeClose = '';
                        $activeHold = '';
                        $status = 'Open';
                        $color = 'success';
                    }
                    elseif($row->status == 2) {
                        $activeOpen = '';
                        $activeClose = '';
                        $activeHold = 'active';
                        $status = 'Hold';
                        $color = 'primary';
                    }
                    elseif($row->status == 3) {
                        $activeOpen = '';
                        $activeClose = 'active';
                        $activeHold = '';
                        $status = 'Close';
                        $color = 'danger';
                    }

                    $btn = '<div class="btn-group">
                        <button type="button" class="btn btn-'. $color .' dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            '. $status .' <i class="mdi mdi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu dropdownmenu-primary">
                            <a class="dropdown-item '.$activeOpen.' " href="#">Open</a>
                            <a class="dropdown-item '.$activeClose.' " href="#" onclick="status(this, 3)" data-id="'.$row->id.'">Close</a>
                            <a class="dropdown-item '.$activeHold.' " href="#" onclick="status(this, 2)" data-id="'.$row->id.'">Hold</a>
                        </div>
                    </div>';
                    
                    return $btn;
                })
                ->addColumn('action', function($row){
                    $edit = '<a href="'.route('complaints.edit', $row->id).'" class="btn btn-primary btn-warning">Edit</a>';
                    $cancel = '<a onclick="cancel(this)" data-id="'.$row->id.'" class="btn btn-primary btn-danger">Cancel</a>';

                    return $edit . ' ' . $cancel;
                
                })
                ->addColumn('detail', function ($row) {
                    $detailBtn = '<a onclick="detail(this)" class="btn btn-info" data-id="'.$row->id.'">Detail</a>';
                    return $detailBtn;
                })
                ->addColumn('created_txt', function ($row) {
                    return CustomHelper::parseDate($row->created_at, true);
                })
                ->addColumn('is_customer', function ($row) {
                    if($row->outlet_code != null) {
                        return 'Pelanggan';
                    }
                    return 'Non Pelanggan';
                })
                ->rawColumns(['action', 'status_txt', 'detail', 'created_txt', 'is_customer'])
                ->make(true);
                // ->toJson();
        }
    }
}
