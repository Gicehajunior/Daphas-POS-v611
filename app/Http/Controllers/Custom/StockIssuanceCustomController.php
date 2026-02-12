<?php

namespace App\Http\Controllers\Custom;

use App\BusinessLocation;
use App\BusinessDepartment;
use App\PurchaseLine;
use App\Transaction;
use App\TransactionSellLinesPurchaseLines;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Datatables;
use DB;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Events\StockIssuanceCreatedOrModified;
use App\Http\Controllers\Controller;

class StockIssuanceCustomController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->status_colors = [
            'in_transit' => 'bg-yellow',
            'completed' => 'bg-green',
            'pending' => 'bg-red',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('purchase.view') && ! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $statuses = $this->stockIssuanceStatuses();

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $edit_days = request()->session()->get('business.transaction_edit_days');

            $stock_issuances = Transaction::join(
                        'business_locations AS l1',
                        'transactions.location_id',
                        '=',
                        'l1.id'
                    )
                    ->join(
                        'business_departments AS bdts1',
                        'transactions.department_id',
                        '=',
                        'bdts1.id'
                    )
                    ->join('transactions as t2', 't2.issuance_parent_id', '=', 'transactions.id')
                    ->join(
                        'business_locations AS l2',
                        't2.location_id',
                        '=',
                        'l2.id'
                    )
                    ->join(
                        'business_departments AS bdts2',
                        't2.department_id',
                        '=',
                        'bdts2.id'
                    )
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'sell_issuance')
                    ->select(
                        'transactions.id',
                        'transactions.transaction_date',
                        'transactions.ref_no',
                        'l1.name as issuance_location',
                        'bdts1.department_name as department_from',
                        'bdts2.department_name as department_to',
                        'transactions.final_total',
                        'transactions.shipping_charges',
                        'transactions.additional_notes',
                        'transactions.id as DT_RowId',
                        'transactions.status'
                    );

            return Datatables::of($stock_issuances)
                ->addColumn('action', function ($row) use ($edit_days) {
                    $html = '<button type="button" title="'.__('stock_adjustment.view_details').'" class="btn btn-primary btn-xs btn-modal" data-container=".view_modal" data-href="'.action([\App\Http\Controllers\Custom\StockIssuanceCustomController::class, 'show'], [$row->id]).'"><i class="fa fa-eye" aria-hidden="true"></i> '.__('messages.view').'</button>';

                    $html .= ' <a href="#" class="print-invoice btn btn-info btn-xs" data-href="'.action([\App\Http\Controllers\Custom\StockIssuanceCustomController::class, 'printInvoice'], [$row->id]).'"><i class="fa fa-print" aria-hidden="true"></i> '.__('messages.print').'</a>';

                    $date = \Carbon::parse($row->transaction_date)
                        ->addDays($edit_days);
                    $today = today();

                    if ($date->gte($today)) {
                        $html .= '&nbsp;
                        <button type="button" data-href="'.action([\App\Http\Controllers\Custom\StockIssuanceCustomController::class, 'destroy'], [$row->id]).'" class="btn btn-danger btn-xs delete_stock_issuance"><i class="fa fa-trash" aria-hidden="true"></i> '.__('messages.delete').'</button>';
                    }

                    if ($row->status != 'final') {
                        $html .= '&nbsp;
                        <a href="'.action([\App\Http\Controllers\Custom\StockIssuanceCustomController::class, 'edit'], [$row->id]).'" class="btn btn-primary btn-xs"><i class="fa fa-edit" aria-hidden="true"></i> '.__('messages.edit').'</a>';
                    }

                    return $html;
                })
                ->editColumn(
                    'final_total',
                    '<span class="display_currency" data-currency_symbol="true">{{$final_total}}</span>'
                )
                ->editColumn(
                    'shipping_charges',
                    '<span class="display_currency" data-currency_symbol="true">{{$shipping_charges}}</span>'
                )
                ->editColumn('status', function ($row) use ($statuses) {
                    $row->status = $row->status == 'final' ? 'completed' : $row->status;
                    $status = $statuses[$row->status];
                    $status_color = ! empty($this->status_colors[$row->status]) ? $this->status_colors[$row->status] : 'bg-gray';
                    $status = $row->status != 'completed' ? '<a href="#" class="stock_issuance_status" data-status="'.$row->status.'" data-href="'.action([\App\Http\Controllers\Custom\StockIssuanceCustomController::class, 'updateStatus'], [$row->id]).'"><span class="label '.$status_color.'">'.$statuses[$row->status].'</span></a>' : '<span class="label '.$status_color.'">'.$statuses[$row->status].'</span>';

                    return $status;
                })
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->rawColumns(['final_total', 'action', 'shipping_charges', 'status'])
                ->setRowAttr([
                    'data-href' => function ($row) {
                        return  action([\App\Http\Controllers\Custom\StockIssuanceCustomController::class, 'show'], [$row->id]);
                    }, ])
                ->make(true);
        }

        return view('stock_issuance.index')->with(compact('statuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\Custom\StockIssuanceCustomController::class, 'index']));
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $business_departments = BusinessDepartment::forDropdown($business_id);

        $statuses = $this->stockIssuanceStatuses();

        return view('stock_issuance.create')
                ->with(compact('business_locations', 'business_departments', 'statuses'));
    }

    private function stockIssuanceStatuses()
    {
        return [
            'pending' => __('lang_v1.pending'),
            'in_transit' => __('lang_v1.in_transit'),
            'completed' => __('restaurant.completed'),
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (! $this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\Custom\StockIssuanceCustomController::class, 'index']));
            }

            DB::beginTransaction();

            $input_data = $request->only(['location_id', 'department_id', 'ref_no', 'transaction_date', 'additional_notes', 'shipping_charges', 'final_total']);
            $status = $request->input('status');
            $user_id = $request->session()->get('user.id');

            $input_data['total_before_tax'] = $input_data['final_total'];

            $input_data['type'] = 'sell_issuance';
            $input_data['business_id'] = $business_id;
            $input_data['created_by'] = $user_id;
            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date'], true);
            $input_data['shipping_charges'] = $this->productUtil->num_uf($input_data['shipping_charges']);
            $input_data['payment_status'] = 'paid';
            $input_data['status'] = ($status == 'completed') ? 'final' : $status;

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount('stock_issuance');
            //Generate reference number
            if (empty($input_data['ref_no'])) {
                $input_data['ref_no'] = $this->productUtil->generateReferenceNumber('stock_issuance', $ref_count);
            }

            $products = $request->input('products');
            $sell_lines = [];
            $purchase_lines = [];

            if (! empty($products)) {
                foreach ($products as $product) {
                    $sell_line_arr = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => $this->productUtil->num_uf($product['quantity']),
                        'item_tax' => 0,
                        'tax_id' => null, ];

                    if (! empty($product['product_unit_id'])) {
                        $sell_line_arr['product_unit_id'] = $product['product_unit_id'];
                    }
                    if (! empty($product['sub_unit_id'])) {
                        $sell_line_arr['sub_unit_id'] = $product['sub_unit_id'];
                    }

                    $purchase_line_arr = $sell_line_arr;

                    if (! empty($product['base_unit_multiplier'])) {
                        $sell_line_arr['base_unit_multiplier'] = $product['base_unit_multiplier'];
                    }

                    $sell_line_arr['unit_price'] = $this->productUtil->num_uf($product['unit_price']);
                    $sell_line_arr['unit_price_inc_tax'] = $sell_line_arr['unit_price'];

                    $purchase_line_arr['purchase_price'] = $sell_line_arr['unit_price'];
                    $purchase_line_arr['purchase_price_inc_tax'] = $sell_line_arr['unit_price'];

                    if (! empty($product['lot_no_line_id'])) {
                        //Add lot_no_line_id to sell line
                        $sell_line_arr['lot_no_line_id'] = $product['lot_no_line_id'];

                        //Copy lot number and expiry date to purchase line
                        $lot_details = PurchaseLine::find($product['lot_no_line_id']);
                        $purchase_line_arr['lot_number'] = $lot_details->lot_number;
                        $purchase_line_arr['mfg_date'] = $lot_details->mfg_date;
                        $purchase_line_arr['exp_date'] = $lot_details->exp_date;
                    }

                    if (! empty($product['base_unit_multiplier'])) {
                        $purchase_line_arr['quantity'] = $purchase_line_arr['quantity'] * $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price'] = $purchase_line_arr['purchase_price'] / $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price_inc_tax'] = $purchase_line_arr['purchase_price_inc_tax'] / $product['base_unit_multiplier'];
                    }

                    if (isset($purchase_line_arr['sub_unit_id']) && $purchase_line_arr['sub_unit_id'] == $purchase_line_arr['product_unit_id']) {
                        unset($purchase_line_arr['sub_unit_id']);
                    }
                    unset($purchase_line_arr['product_unit_id']);

                    $sell_lines[] = $sell_line_arr;
                    $purchase_lines[] = $purchase_line_arr;
                }
            }

            //Create Sell Issuance transaction
            $sell_issuance = Transaction::create($input_data);

            //Create Purchase Issuance at issuance department
            $input_data['type'] = 'purchase_issuance';
            $input_data['department_id'] = $request->input('issuance_department_id');
            $input_data['issuance_parent_id'] = $sell_issuance->id;
            $input_data['status'] = ($status == 'completed') ? 'received' : $status;

            $purchase_issuance = Transaction::create($input_data);

            //Sell Product from first department
            if (! empty($sell_lines)) {
                $this->transactionUtil->createOrUpdateSellLines($sell_issuance, $sell_lines, $input_data['location_id'], $input_data['department_id'], false, null, [], false);
            }

            //Purchase product in second location and department
            if (! empty($purchase_lines)) {
                $purchase_issuance->purchase_lines()->createMany($purchase_lines);
            }

            //Decrease product stock from sell department
            //And increase product stock at purchase department
            if ($status == 'completed') {
                foreach ($products as $product) {
                    if ($product['enable_stock']) {
                        $decrease_qty = $this->productUtil->num_uf($product['quantity']);
                        
                        if (! empty($product['base_unit_multiplier'])) {
                            $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                        }

                        $this->productUtil->departmentalDecreaseProductQuantity(
                            $product['product_id'],
                            $product['variation_id'],
                            $sell_issuance->location_id,
                            $sell_issuance->department_id,
                            $decrease_qty
                        );

                        $this->productUtil->departmentalUpdateProductQuantity(
                            $purchase_issuance->location_id,
                            $purchase_issuance->department_id,
                            $product['product_id'],
                            $product['variation_id'],
                            $decrease_qty,
                            0,
                            null,
                            false
                        );
                    }
                }

                //Adjust stock over selling if found
                $this->productUtil->adjustStockOverSelling($purchase_issuance);

                //Map sell lines with purchase lines
                $business = [
                    'id' => $business_id,
                    'accounting_method' => $request->session()->get('business.accounting_method'), 
                    'location_id' => $sell_issuance->location_id,
                    'department_id' => $sell_issuance->department_id,
                ];
                $this->transactionUtil->mapPurchaseSell($business, $sell_issuance->sell_lines, 'purchase');
            }

            $this->transactionUtil->activityLog($sell_issuance, 'added');

            event( new StockIssuanceCreatedOrModified($sell_issuance, 'added'));

            $output = ['success' => 1,
                'msg' => __('custom.stock_issuance_added_successfully'),
            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect('stock-issuances')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! auth()->user()->can('purchase.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $sell_issuance = Transaction::where('business_id', $business_id)
                            ->where('id', $id)
                            ->where('type', 'sell_issuance')
                            ->with(
                                'contact',
                                'sell_lines',
                                'sell_lines.product',
                                'sell_lines.variations',
                                'sell_lines.variations.product_variation',
                                'sell_lines.lot_details',
                                'sell_lines.sub_unit',
                                'location',
                                'department',
                                'sell_lines.product.unit'
                            )
                            ->first();

        foreach ($sell_issuance->sell_lines as $key => $value) {
            if (! empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);

                $sell_issuance->sell_lines[$key] = $formated_sell_line;
            }
        }

        $purchase_issuance = Transaction::where('business_id', $business_id)
                    ->where('issuance_parent_id', $sell_issuance->id)
                    ->where('type', 'purchase_issuance')
                    ->first();

        $department_details = ['sell' => $sell_issuance->department, 'purchase' => $purchase_issuance->department];

        $lot_n_exp_enabled = false;
        if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
            $lot_n_exp_enabled = true;
        }

        $statuses = $this->stockIssuanceStatuses();

        $statuses['final'] = __('restaurant.completed');

        $activities = Activity::forSubject($sell_issuance)
           ->with(['causer', 'subject'])
           ->latest()
           ->get();

        return view('stock_issuance.show')
                ->with(compact('sell_issuance', 'department_details', 'lot_n_exp_enabled', 'statuses', 'activities'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('purchase.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            if (request()->ajax()) {
                $edit_days = request()->session()->get('business.transaction_edit_days');
                if (! $this->transactionUtil->canBeEdited($id, $edit_days)) {
                    return ['success' => 0,
                        'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days]), ];
                }

                //Get sell issuance transaction
                $sell_issuance = Transaction::where('id', $id)
                                    ->where('type', 'sell_issuance')
                                    ->with(['sell_lines'])
                                    ->first();

                //Get purchase issuance transaction
                $purchase_issuance = Transaction::where('issuance_parent_id', $sell_issuance->id)
                                    ->where('type', 'purchase_issuance')
                                    ->with(['purchase_lines'])
                                    ->first();

                //Check if any issuance stock is deleted and delete purchase lines
                $purchase_lines = $purchase_issuance->purchase_lines;
                foreach ($purchase_lines as $purchase_line) {
                    if ($purchase_line->quantity_sold > 0) {
                        return ['success' => 0,
                            'msg' => __('custom.stock_issuance_cannot_be_deleted'),
                        ];
                    }
                }

                event( new StockIssuanceCreatedOrModified($sell_issuance, 'deleted'));

                DB::beginTransaction();
                //Get purchase lines from transaction_sell_lines_purchase_lines and decrease quantity_sold
                $sell_lines = $sell_issuance->sell_lines;
                $deleted_sell_purchase_ids = [];
                $products = []; //variation_id as array

                foreach ($sell_lines as $sell_line) {
                    $purchase_sell_line = TransactionSellLinesPurchaseLines::where('sell_line_id', $sell_line->id)->first();

                    if (! empty($purchase_sell_line)) {
                        //Decrease quntity sold from purchase line
                        PurchaseLine::where('id', $purchase_sell_line->purchase_line_id)
                                ->decrement('quantity_sold', $sell_line->quantity);

                        $deleted_sell_purchase_ids[] = $purchase_sell_line->id;

                        //variation details
                        if (isset($products[$sell_line->variation_id])) {
                            $products[$sell_line->variation_id]['quantity'] += $sell_line->quantity;
                            $products[$sell_line->variation_id]['product_id'] = $sell_line->product_id;
                        } else {
                            $products[$sell_line->variation_id]['quantity'] = $sell_line->quantity;
                            $products[$sell_line->variation_id]['product_id'] = $sell_line->product_id;
                        }
                    }
                }

                //Update quantity available in both location
                if (! empty($products)) {
                    foreach ($products as $key => $value) {
                        //Decrease from location 2
                        $this->productUtil->decreaseProductQuantity(
                            $products[$key]['product_id'],
                            $key,
                            $purchase_issuance->location_id,
                            $products[$key]['quantity']
                        );

                        //Increase in location 1
                        $this->productUtil->updateProductQuantity(
                            $sell_issuance->location_id,
                            $products[$key]['product_id'],
                            $key,
                            $products[$key]['quantity']
                        );
                    }
                }

                //Delete sale line purchase line
                if (! empty($deleted_sell_purchase_ids)) {
                    TransactionSellLinesPurchaseLines::whereIn('id', $deleted_sell_purchase_ids)
                        ->delete();
                }

                //Delete both transactions
                $sell_issuance->delete();
                $purchase_issuance->delete();
                event( new StockIssuanceCreatedOrModified($sell_issuance, 'deleted'));
                $output = ['success' => 1,
                    'msg' => __('custom.stock_issuance_delete_success'),
                ];
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Checks if ref_number and supplier combination already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printInvoice($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $sell_issuance = Transaction::where('business_id', $business_id)
                                ->where('id', $id)
                                ->where('type', 'sell_issuance')
                                ->with(
                                    'contact',
                                    'sell_lines',
                                    'sell_lines.product',
                                    'sell_lines.variations',
                                    'sell_lines.variations.product_variation',
                                    'sell_lines.lot_details',
                                    'location',
                                    'sell_lines.product.unit'
                                )
                                ->first();

            $purchase_issuance = Transaction::where('business_id', $business_id)
                        ->where('issuance_parent_id', $sell_issuance->id)
                        ->where('type', 'purchase_issuance')
                        ->first();

            $location_details = ['sell' => $sell_issuance->location, 'purchase' => $purchase_issuance->location];

            $lot_n_exp_enabled = false;
            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                $lot_n_exp_enabled = true;
            }

            $output = ['success' => 1, 'receipt' => [], 'print_title' => $sell_issuance->ref_no];
            $output['receipt']['html_content'] = view('stock_issuance.print', compact('sell_issuance', 'location_details', 'lot_n_exp_enabled'))->render();
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id);

        $statuses = $this->stockIssuanceStatuses();

        $sell_issuance = Transaction::where('business_id', $business_id)
                ->where('type', 'sell_issuance')
                ->where('status', '!=', 'final')
                ->with(['sell_lines'])
                ->findOrFail($id);

        $purchase_issuance = Transaction::where('business_id',
                $business_id)
                ->where('issuance_parent_id', $id)
                ->where('status', '!=', 'received')
                ->where('type', 'purchase_issuance')
                ->first();

        $products = [];
        foreach ($sell_issuance->sell_lines as $sell_line) {
            $product = $this->productUtil->getDetailsFromVariation($sell_line->variation_id, $business_id, $sell_issuance->location_id);
            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);
            $product->sub_unit_id = $sell_line->sub_unit_id;
            $product->quantity_ordered = $sell_line->quantity;
            $product->transaction_sell_lines_id = $sell_line->id;
            $product->lot_no_line_id = $sell_line->lot_no_line_id;

            $product->unit_details = $this->productUtil->getSubUnits($business_id, $product->unit_id);

            //Get lot number dropdown if enabled
            $lot_numbers = [];
            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($sell_line->variation_id, $business_id, $sell_issuance->location_id, true);
                foreach ($lot_number_obj as $lot_number) {
                    $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                    $lot_numbers[] = $lot_number;
                }
            }
            $product->lot_numbers = $lot_numbers;

            $products[] = $product;
        }

        return view('stock_issuance.edit')
                ->with(compact('sell_issuance', 'purchase_issuance', 'business_locations', 'statuses', 'products'));
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
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (! $this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\Custom\StockIssuanceCustomController::class, 'index']));
            }

            $business_id = request()->session()->get('user.business_id');

            $sell_issuance = Transaction::where('business_id', $business_id)
                    ->where('type', 'sell_issuance')
                    ->findOrFail($id);

            $sell_issuance_before = $sell_issuance->replicate();

            $purchase_issuance = Transaction::where('business_id',
                    $business_id)
                    ->where('issuance_parent_id', $id)
                    ->where('type', 'purchase_issuance')
                    ->with(['purchase_lines'])
                    ->first();

            $status = $request->input('status');

            DB::beginTransaction();

            $input_data = $request->only(['transaction_date', 'additional_notes', 'shipping_charges', 'final_total']);
            $status = $request->input('status');

            $input_data['total_before_tax'] = $input_data['final_total'];

            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date'], true);
            $input_data['shipping_charges'] = $this->productUtil->num_uf($input_data['shipping_charges']);
            $input_data['status'] = $status == 'completed' ? 'final' : $status;

            $products = $request->input('products');
            $sell_lines = [];
            $purchase_lines = [];
            $edited_purchase_lines = [];
            if (! empty($products)) {
                foreach ($products as $product) {
                    $sell_line_arr = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => $this->productUtil->num_uf($product['quantity']),
                        'item_tax' => 0,
                        'tax_id' => null, ];

                    if (! empty($product['product_unit_id'])) {
                        $sell_line_arr['product_unit_id'] = $product['product_unit_id'];
                    }
                    if (! empty($product['sub_unit_id'])) {
                        $sell_line_arr['sub_unit_id'] = $product['sub_unit_id'];
                    }

                    $purchase_line_arr = $sell_line_arr;

                    if (! empty($product['base_unit_multiplier'])) {
                        $sell_line_arr['base_unit_multiplier'] = $product['base_unit_multiplier'];
                    }

                    $sell_line_arr['unit_price'] = $this->productUtil->num_uf($product['unit_price']);
                    $sell_line_arr['unit_price_inc_tax'] = $sell_line_arr['unit_price'];

                    $purchase_line_arr['purchase_price'] = $sell_line_arr['unit_price'];
                    $purchase_line_arr['purchase_price_inc_tax'] = $sell_line_arr['unit_price'];
                    if (isset($product['transaction_sell_lines_id'])) {
                        $sell_line_arr['transaction_sell_lines_id'] = $product['transaction_sell_lines_id'];
                    }

                    if (! empty($product['lot_no_line_id'])) {
                        //Add lot_no_line_id to sell line
                        $sell_line_arr['lot_no_line_id'] = $product['lot_no_line_id'];

                        //Copy lot number and expiry date to purchase line
                        $lot_details = PurchaseLine::find($product['lot_no_line_id']);
                        $purchase_line_arr['lot_number'] = $lot_details->lot_number;
                        $purchase_line_arr['mfg_date'] = $lot_details->mfg_date;
                        $purchase_line_arr['exp_date'] = $lot_details->exp_date;
                    }

                    if (! empty($product['base_unit_multiplier'])) {
                        $purchase_line_arr['quantity'] = $purchase_line_arr['quantity'] * $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price'] = $purchase_line_arr['purchase_price'] / $product['base_unit_multiplier'];
                        $purchase_line_arr['purchase_price_inc_tax'] = $purchase_line_arr['purchase_price_inc_tax'] / $product['base_unit_multiplier'];
                    }

                    if (isset($purchase_line_arr['sub_unit_id']) && $purchase_line_arr['sub_unit_id'] == $purchase_line_arr['product_unit_id']) {
                        unset($purchase_line_arr['sub_unit_id']);
                    }
                    unset($purchase_line_arr['product_unit_id']);

                    $sell_lines[] = $sell_line_arr;

                    $purchase_line = [];
                    //check if purchase_line for the variation exists else create new
                    foreach ($purchase_issuance->purchase_lines as $pl) {
                        if ($pl->variation_id == $purchase_line_arr['variation_id']) {
                            $pl->update($purchase_line_arr);
                            $edited_purchase_lines[] = $pl->id;
                            $purchase_line = $pl;
                            break;
                        }
                    }
                    if (empty($purchase_line)) {
                        $purchase_line = new PurchaseLine($purchase_line_arr);
                    }

                    $purchase_lines[] = $purchase_line;
                }
            }

            //Create Sell Issuance transaction
            $sell_issuance->update($input_data);
            $sell_issuance->save();

            event( new StockIssuanceCreatedOrModified($sell_issuance, 'updated'));

            //Create Purchase Issuance at issuance location
            $input_data['status'] = $status == 'completed' ? 'received' : $status;

            $purchase_issuance->update($input_data);
            $purchase_issuance->save();

            //Sell Product from first location
            if (! empty($sell_lines)) {
                $this->transactionUtil->createOrUpdateSellLines($sell_issuance, $sell_lines, $sell_issuance->location_id, $sell_issuance->department_id, false, 'draft', [], false);
            }

            //Purchase product in second location
            if (! empty($purchase_lines)) {
                if (! empty($edited_purchase_lines)) {
                    PurchaseLine::where('transaction_id', $purchase_issuance->id)
                    ->whereNotIn('id', $edited_purchase_lines)
                    ->delete();
                }
                $purchase_issuance->purchase_lines()->saveMany($purchase_lines);
            }

            //Decrease product stock from sell location
            //And increase product stock at purchase location
            if ($status == 'completed') {
                foreach ($products as $product) {
                    if ($product['enable_stock']) {
                        $decrease_qty = $this->productUtil
                                    ->num_uf($product['quantity']);
                        if (! empty($product['base_unit_multiplier'])) {
                            $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                        }

                        $this->productUtil->decreaseProductQuantity(
                            $product['product_id'],
                            $product['variation_id'],
                            $sell_issuance->location_id,
                            $decrease_qty
                        );

                        $this->productUtil->updateProductQuantity(
                            $purchase_issuance->location_id,
                            $product['product_id'],
                            $product['variation_id'],
                            $decrease_qty,
                            0,
                            null,
                            false
                        );
                    }
                }

                //Adjust stock over selling if found
                $this->productUtil->adjustStockOverSelling($purchase_issuance);

                //Map sell lines with purchase lines
                $business = ['id' => $business_id,
                    'accounting_method' => $request->session()->get('business.accounting_method'),
                    'location_id' => $sell_issuance->location_id,
                ];
                $this->transactionUtil->mapPurchaseSell($business, $sell_issuance->sell_lines, 'purchase');
            }

            $this->transactionUtil->activityLog($sell_issuance, 'edited', $sell_issuance_before);

            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_succesfully'),
            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect('stock-issuances')->with('status', $output);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        if (! auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $sell_issuance = Transaction::where('business_id', $business_id)
                    ->where('type', 'sell_issuance')
                    ->with(['sell_lines', 'sell_lines.product'])
                    ->findOrFail($id);

            $purchase_issuance = Transaction::where('business_id',
                    $business_id)
                    ->where('issuance_parent_id', $id)
                    ->where('type', 'purchase_issuance')
                    ->with(['purchase_lines'])
                    ->first();

            $status = $request->input('status');

            DB::beginTransaction();
            if ($status == 'completed' && $sell_issuance->status != 'completed') {
                foreach ($sell_issuance->sell_lines as $sell_line) {
                    if ($sell_line->product->enable_stock) {
                        $this->productUtil->decreaseProductQuantity(
                            $sell_line->product_id,
                            $sell_line->variation_id,
                            $sell_issuance->location_id,
                            $sell_line->quantity
                        );

                        $this->productUtil->updateProductQuantity(
                            $purchase_issuance->location_id,
                            $sell_line->product_id,
                            $sell_line->variation_id,
                            $sell_line->quantity,
                            0,
                            null,
                            false
                        );
                    }
                }

                //Adjust stock over selling if found
                $this->productUtil->adjustStockOverSelling($purchase_issuance);

                //Map sell lines with purchase lines
                $business = ['id' => $business_id,
                    'accounting_method' => $request->session()->get('business.accounting_method'),
                    'location_id' => $sell_issuance->location_id,
                ];
                $this->transactionUtil->mapPurchaseSell($business, $sell_issuance->sell_lines, 'purchase');
            }
            $purchase_issuance->status = $status == 'completed' ? 'received' : $status;
            $purchase_issuance->save();
            $sell_issuance->status = $status == 'completed' ? 'final' : $status;
            $sell_issuance->save();

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_succesfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => 'File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage(),
            ];
        }

        return $output;
    }
}
