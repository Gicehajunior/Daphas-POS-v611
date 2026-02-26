<?php
/* LICENSE: This source file belongs to The Web Fosters. The customer
 * is provided a licence to use it.
 * Permission is hereby granted, to any person obtaining the licence of this
 * software and associated documentation files (the "Software"), to use the
 * Software for personal or business purpose ONLY. The Software cannot be
 * copied, published, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. THE AUTHOR CAN FIX
 * ISSUES ON INTIMATION. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH
 * THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author     The Web Fosters <thewebfosters@gmail.com>
 * @owner      The Web Fosters <thewebfosters@gmail.com>
 * @copyright  2018 The Web Fosters
 * @license    As attached in zip file.
 */

namespace App\Http\Controllers\Custom;

use App\Account;
use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\CustomerGroup;
use App\InvoiceLayout;
use App\InvoiceScheme;
use App\Media;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\TransactionPayment; 
use App\TransactionSellLine;
use App\TypesOfService;
use App\User;
use App\Utils\Util;
use App\Utils\BusinessUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\Custom\RoleUtil;
use App\Utils\Custom\CustomMpesaUtil;
use App\Variation;
use App\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Stripe\Charge;
use Stripe\Stripe;
use Yajra\DataTables\Facades\DataTables;
use App\Events\SellCreatedOrModified;
use App\Http\Controllers\Controller;
use Exception;
use App\Exceptions\Custom\ApplicationException;
use App\Exceptions\Custom\CustomHandler;

class SellPosCustomController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $contactUtil;

    protected $productUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $mpesaUtil;

    protected $roleUtil;

    protected $cashRegisterUtil;

    protected $moduleUtil;

    protected $notificationUtil;

    protected $commonUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(
        Util $commonUtil,
        ContactUtil $contactUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        CustomMpesaUtil $mpesaUtil,
        RoleUtil $roleUtil,
        CashRegisterUtil $cashRegisterUtil,
        ModuleUtil $moduleUtil,
        NotificationUtil $notificationUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->mpesaUtil = $mpesaUtil;
        $this->roleUtil = $roleUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
        $this->notificationUtil = $notificationUtil;

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => '', ];
    }

    private function authorizePosAccess($business_id): void
    {
        if (!(
            auth()->user()->can('superadmin') ||
            auth()->user()->can('sell.create') ||
            (
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') &&
                auth()->user()->can('repair.create')
            )
        )) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                403,
                'Unauthorized action.'
            );
        }
    }

    public function checkPinIfEnabled()
    {
        try {

            $business_id = session('user.business_id');

            $this->authorizePosAccess($business_id);

            $business_details = $this->businessUtil->getDetails($business_id);

            $pos_settings = empty($business_details->pos_settings)
                ? $this->businessUtil->defaultPosSettings()
                : json_decode($business_details->pos_settings, true);

            return [
                'status'          => 'success',
                'locker_duration' => $pos_settings['pos_lock_after_duration'] ?? 1,
                'pin_disabled'    => !empty($pos_settings['disable_pos_pin']) && $pos_settings['disable_pos_pin'] == 1
            ];
        } catch (\Throwable $e) {

            \Log::error('POS Lock Check Failed', [
                'error' => $e->getMessage()
            ]);

            return CustomHandler::render(request(), $e);
        }
    }

    public function posPinAuthentication(Request $request)
    {
        try {
    
            $business_id = session('user.business_id');
    
            $this->authorizePosAccess($business_id);
    
            $pinInput = trim($request->input('pin'));
    
            // Empty PIN
            if (empty($pinInput)) {
                throw new ApplicationException('PIN is required.');
            }
    
            $business_details = $this->businessUtil->getDetails($business_id);
    
            $pos_settings = empty($business_details->pos_settings)
                ? $this->businessUtil->defaultPosSettings()
                : json_decode($business_details->pos_settings, true);
    
            $generalPin  = $pos_settings['pos_pin'] ?? null;
            $pinDisabled = !empty($pos_settings['disable_pos_pin']) && $pos_settings['disable_pos_pin'] == 1;
            $lockerTime  = $pos_settings['pos_lock_after_duration'] ?? 1;
    
            /*
            |--------------------------------------------------------------------------
            | Guard: No PIN configured anywhere
            |--------------------------------------------------------------------------
            */
            $userWithPinExists = User::where('business_id', $business_id)
                                      ->whereNotNull('pos_pin')
                                      ->exists();
    
            if (empty($generalPin) && !$userWithPinExists) {
                throw new ApplicationException(
                    'POS PIN appears to have not been set. Please seek assistance from the Administrator!'
                );
            }
    
            /*
            |--------------------------------------------------------------------------
            | Check General PIN
            |--------------------------------------------------------------------------
            */
            if (!empty($generalPin) && $pinInput === $generalPin) {
    
                $user_id = session('user.id');
                $user    = User::findOrFail($user_id);
    
                session([
                    'staffIDLoggedIn' => $user_id,
                    'staffLoggedIn'   => $user->first_name . ' ' . $user->last_name
                ]);
    
                return [
                    'status'          => 'success',
                    'message'         => 'POS unlock success!',
                    'staffLoggedIn'   => session('staffLoggedIn'),
                    'locker_duration' => $lockerTime,
                    'pin_disabled'    => $pinDisabled
                ];
            }
    
            /*
            |--------------------------------------------------------------------------
            | Check User-Based PIN
            |--------------------------------------------------------------------------
            */
            $user = User::where('business_id', $business_id)
                        ->where('pos_pin', $pinInput)
                        ->first();
    
            if (!$user) {
                throw new ApplicationException('You entered wrong pin. Please try again!');
            }
    
            session([
                'staffIDLoggedIn' => $user->id,
                'staffLoggedIn'   => $user->first_name . ' ' . $user->last_name
            ]);
    
            return [
                'status'          => 'success',
                'message'         => 'POS unlock success!',
                'staffLoggedIn'   => session('staffLoggedIn'),
                'locker_duration' => $lockerTime,
                'pin_disabled'    => $pinDisabled
            ];
    
        } catch (\Throwable $e) {
    
            \Log::error('POS PIN Authentication Failed', [
                'error'       => $e->getMessage(),
                'business_id' => session('user.business_id')
            ]);
    
            return CustomHandler::render(request(), $e); // Let global handler format JSON response
        }
    }
    
}
