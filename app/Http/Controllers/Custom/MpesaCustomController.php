<?php

namespace App\Http\Controllers\Custom;

use App\MpesaSetting;
use App\Utils\Custom\CustomMpesaUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Controller;

class MpesaCustomController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $mpesaUtil;

    /**
     * Constructor
     *
     * @param  CustomMpesaUtil  $mpesaUtil
     * @return void
     */
    public function __construct(CustomMpesaUtil $mpesaUtil)
    {
        $this->mpesaUtil = $mpesaUtil;
    }

    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('business.gateways.mpesa_settings.view') && ! auth()->user()->can('business.gateways.mpesa_settings.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $mpesa_settings = $this->mpesaUtil->getMpesaSettings($business_id);
            
            $datatable = Datatables::of($mpesa_settings)
                ->addColumn(
                    'action',
                    '@can("business.gateways.mpesa_settings.update")
                        <button data-href="{{action([\App\Http\Controllers\Custom\MpesaCustomController::class, "edit"], [$id])}}" class="btn btn-xs btn-primary btn-modal edit_mpesa_setting_button" data-container=".mpesa_setting_edit_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                        <br></br>
                    @endcan
                    @can("business.gateways.mpesa_settings.delete")
                        <button data-href="{{action([\App\Http\Controllers\Custom\MpesaCustomController::class, "_destroy"], [$id])}}" class="btn btn-xs btn-danger btn-modal delete_mpesa_setting_button" data-container=".mpesa_setting_delete_modal"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                        &nbsp;
                        <br></br>
                    @endcan
                    @can("business.gateways.mpesa_settings.update")
                        <button data-href="{{action([\App\Http\Controllers\Custom\MpesaCustomController::class, "_register_enpoints"], [$id])}}" class="btn btn-xs btn-primary btn-modal register_mpesa_setting_button" data-id="{{$id}}" data-container=".mpesa_setting_edit_modal"><i class="glyphicon glyphicon-edit"></i> @lang("mpesa.mpesa_endpoints")</button>
                        &nbsp;
                        <br></br>
                    @endcan'
                )  
                ->removeColumn('id')
                ->removeColumn('business_id')
                ->removeColumn('location_id')
                ->removeColumn('mpesa_c2b_endpoint')
                ->removeColumn('mpesa_b2c_endpoint')
                ->removeColumn('mpesa_transaction_status_endpoint') 
                ->editColumn('mpesa_api_environment', function ($row) {
                        $mpesa_api_environ = $row->mpesa_api_environment == 1 ? 'Live' : 'Sandbox';
                        return '<span class="service-type-label" data-orig-value="{{$mpesa_api_environment}}" data-status-name="{{$mpesa_api_environment}}">'.$mpesa_api_environ.'</span>';
                    }
                )
                ->editColumn(
                    'mpesa_consumer_key',
                    '<span class="service-type-label" data-orig-value="{{$mpesa_consumer_key}}" data-status-name="{{$mpesa_consumer_key}}">{{$mpesa_consumer_key}}</span>'
                )
                ->editColumn(
                    'mpesa_consumer_secret',
                    '<span class="service-type-label" data-orig-value="{{$mpesa_consumer_secret}}" data-status-name="{{$mpesa_consumer_secret}}">{{$mpesa_consumer_secret}}</span>'
                )
                ->editColumn(
                    'mpesa_pass_key',
                    '<span class="service-type-label" data-orig-value="{{$mpesa_pass_key}}" data-status-name="{{$mpesa_pass_key}}">{{$mpesa_pass_key}}</span>'
                )
                ->editColumn(
                    'mpesa_business_short_code',
                    '<span class="service-type-label" data-orig-value="{{$mpesa_business_short_code}}" data-status-name="{{$mpesa_business_short_code}}">{{$mpesa_business_short_code}}</span>'
                )
                ->editColumn(
                    'mpesa_business_msisdn',
                    '<span class="service-type-label" data-orig-value="{{$mpesa_business_msisdn}}" data-status-name="{{$mpesa_business_msisdn}}">{{$mpesa_business_msisdn}}</span>'
                )
                ->editColumn(
                    'mpesa_party_A',
                    '<span class="service-type-label" data-orig-value="{{$mpesa_party_A}}" data-status-name="{{$mpesa_party_A}}">{{$mpesa_party_A}}</span>'
                )
                ->editColumn(
                    'mpesa_party_B',
                    '<span class="service-type-label" data-orig-value="{{$mpesa_party_B}}" data-status-name="{{$mpesa_party_B}}">{{$mpesa_party_B}}</span>'
                )
                ->editColumn(
                    'mpesa_initiator_name',
                    '<span class="service-type-label" data-orig-value="{{$mpesa_initiator_name}}" data-status-name="{{$mpesa_initiator_name}}">{{$mpesa_initiator_name}}</span>'
                )
                ->editColumn(
                    'mpesa_confirmation_endpoint',
                    '<span class="service-type-label" data-orig-value="{{$mpesa_confirmation_endpoint}}" data-status-name="{{$mpesa_confirmation_endpoint}}">{{$mpesa_confirmation_endpoint}}</span>'
                )
                ->editColumn(
                    'mpesa_validation_endpoint',
                    '<span class="service-type-label" data-orig-value="{{$mpesa_validation_endpoint}}" data-status-name="{{$mpesa_validation_endpoint}}">{{$mpesa_validation_endpoint}}</span>'
                )
                ->editColumn('shortcode_type', '@if($shortcode_type == 1) Paybill @else Till Number @endif') 
                ->editColumn('status', function ($row) {
                        $status_txt = ($row->status == 1) ? 'Active' : 'Inactive';
                        $badge = ($row->status == 1) ? 'success' : 'danger';
                        return '<span class="service-type-label badge badge-'.$badge.'" data-orig-value="{{$status}}" data-status-name="{{$status}}">'.$status_txt.'</span>';
                    } 
                ) 
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->removeColumn('updated_at');  

                $rawColumns = [
                    'action',
                    'mpesa_api_environment', 
                    'mpesa_consumer_key', 
                    'mpesa_consumer_secret', 
                    'mpesa_pass_key', 
                    'mpesa_business_short_code', 
                    'mpesa_business_msisdn', 
                    'mpesa_party_A', 
                    'mpesa_party_B', 
                    'mpesa_initiator_name', 
                    'mpesa_confirmation_endpoint', 
                    'mpesa_validation_endpoint', 
                    'shortcode_type',
                    'status',
                    'created_at',
                ];
            
            return $datatable->rawColumns($rawColumns)->make(true); 
        }

        return view('business.gateways.mpesa_settings.index'); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('business.gateways.mpesa_settings.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('business.gateways.mpesa_settings.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('business.gateways.mpesa_settings.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only([
                'shortcode_type',
                'mpesa_api_environment',
                'mpesa_consumer_key',
                'mpesa_consumer_secret',
                'mpesa_pass_key',
                'mpesa_business_short_code',
                'mpesa_business_msisdn',
                'mpesa_party_A',
                'mpesa_party_B',
                'mpesa_initiator_name',
                'mpesa_confirmation_endpoint',
                'mpesa_validation_endpoint',
                'mpesa_transaction_validation_param',
                'mpesa_transaction_result_code',
                'status'
            ]);
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['created_by'] = $request->session()->get('user.id');
            $input['shortcode_type'] = $input['shortcode_type'];
            $input['mpesa_api_environment'] = $input['mpesa_api_environment']; 
            $input['mpesa_consumer_key'] = $input['mpesa_consumer_key'];
            $input['mpesa_consumer_secret'] = $input['mpesa_consumer_secret'];
            $input['mpesa_pass_key'] = $input['mpesa_pass_key'];
            $input['mpesa_business_short_code'] = $input['mpesa_business_short_code'];
            $input['mpesa_business_msisdn'] = $input['mpesa_business_msisdn'];
            $input['mpesa_party_A'] = $input['mpesa_party_A'];
            $input['mpesa_party_B'] = $input['mpesa_party_B'];
            $input['mpesa_initiator_name'] = $input['mpesa_initiator_name'];
            $input['mpesa_confirmation_endpoint'] = $input['mpesa_confirmation_endpoint'];
            $input['mpesa_validation_endpoint'] = $input['mpesa_validation_endpoint'];
            $input['mpesa_transaction_validation_param'] = $input['mpesa_transaction_validation_param'];
            $input['mpesa_transaction_result_code'] = $input['mpesa_transaction_result_code'];
            $input['status'] = isset($input['status']) ? 1 : 0; 

            $Shortcode = $this->mpesaUtil->getMpesaSettingsByShortcode(
                $input['business_id'], 
                $input['mpesa_business_short_code']
            );
            
            if (count($Shortcode) > 0) {
                $output = [
                    'success' => false,
                    'msg' => __('mpesa.shortcode_already_exists'),
                ]; 
                
                return view('business.gateways.mpesa_settings.index');
            }

            $mpesa_setting = MpesaSetting::create($input);

            $output = [
                'success' => true,
                'data' => $mpesa_setting,
                'msg' => __('mpesa.added_success'),
            ];

            $request->session()->flash('status', $output);
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return view('business.gateways.mpesa_settings.index');
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
    public function edit($id)
    {
        if (! auth()->user()->can('business.gateways.mpesa_settings.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $mpesa_setting = MpesaSetting::where('business_id', $business_id)->find($id);

            return view('business.gateways.mpesa_settings.edit')
                ->with(compact('mpesa_setting'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('business.gateways.mpesa_settings.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only([
                'shortcode_type',
                'mpesa_api_environment',
                'mpesa_consumer_key',
                'mpesa_consumer_secret',
                'mpesa_pass_key',
                'mpesa_business_short_code',
                'mpesa_business_msisdn',
                'mpesa_party_A',
                'mpesa_party_B',
                'mpesa_initiator_name',
                'mpesa_confirmation_endpoint',
                'mpesa_validation_endpoint',
                'mpesa_transaction_validation_param',
                'mpesa_transaction_result_code',
                'status'
            ]);
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['created_by'] = $request->session()->get('user.id');
            $input['shortcode_type'] = $input['shortcode_type'];
            $input['mpesa_api_environment'] = $input['mpesa_api_environment']; 
            $input['mpesa_consumer_key'] = $input['mpesa_consumer_key'];
            $input['mpesa_consumer_secret'] = $input['mpesa_consumer_secret'];
            $input['mpesa_pass_key'] = $input['mpesa_pass_key'];
            $input['mpesa_business_short_code'] = $input['mpesa_business_short_code'];
            $input['mpesa_business_msisdn'] = $input['mpesa_business_msisdn'];
            $input['mpesa_party_A'] = $input['mpesa_party_A'];
            $input['mpesa_party_B'] = $input['mpesa_party_B'];
            $input['mpesa_initiator_name'] = $input['mpesa_initiator_name'];
            $input['mpesa_confirmation_endpoint'] = $input['mpesa_confirmation_endpoint'];
            $input['mpesa_validation_endpoint'] = $input['mpesa_validation_endpoint'];
            $input['mpesa_transaction_validation_param'] = $input['mpesa_transaction_validation_param'];
            $input['mpesa_transaction_result_code'] = $input['mpesa_transaction_result_code'];
            $input['status'] = isset($input['status']) ? 1 : 0; 

            $Shortcode = $this->mpesaUtil->getMpesaSettingsByShortcode(
                $input['business_id'], 
                $input['mpesa_business_short_code']
            ); 

            $mpesa_setting = MpesaSetting::where('business_id', $input['business_id'])->find($id);
            $mpesa_setting = $mpesa_setting->update($input);

            $output = [
                'success' => true,
                'data' => $mpesa_setting,
                'msg' => __('mpesa.updated_success'),
            ];
            session()->flash('status', $output);
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        
        return view('business.gateways.mpesa_settings.index'); 
    
    }

    /**
     * Show the form for destroy warning alert for the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function _destroy($id)
    {
        if (! auth()->user()->can('business.gateways.mpesa_settings.destroy')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $mpesa_setting = MpesaSetting::where('business_id', $business_id)->find($id);

            return view('business.gateways.mpesa_settings.destroy')
                ->with(compact('mpesa_setting'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('business.gateways.mpesa_settings.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {  
            $business_id = request()->user()->business_id;

            $mpesa_setting = MpesaSetting::where('business_id', $business_id)->findOrFail($id);
            $mpesa_setting->delete();

            $output = ['success' => true,
                'msg' => __('mpesa.deleted_success'),
            ]; 
            session()->flash('status', $output);
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        } 

        return view('business.gateways.mpesa_settings.index');  
    }

    public function _register_enpoints($id)
    {
        if (! auth()->user()->can('business.gateways.mpesa_settings.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $mpesa_setting = MpesaSetting::where('business_id', $business_id)->find($id);

            return view('business.gateways.mpesa_settings.register_endpoints')
                ->with(compact('mpesa_setting'));
        }
    }

    public function register_enpoints($id) {
        
    }
}
