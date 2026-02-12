<?php

namespace App\Http\Controllers\Custom;

use App\Business;
use App\Currency;
use App\Notifications\TestEmailNotification;
use App\System;
use App\TaxRate;
use App\Unit;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\RestaurantUtil;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use App\Rules\ReCaptcha;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class BusinessCustomController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | BusinessCustomController
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new business/business as well as their
    | validation and creation.
    |
    */

    /**
     * All Utils instance.
     */
    protected $businessUtil;

    protected $restaurantUtil;

    protected $moduleUtil;

    protected $mailDrivers;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(BusinessUtil $businessUtil, RestaurantUtil $restaurantUtil, ModuleUtil $moduleUtil)
    {
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil; 
    }

    public function downloadESDAClassApiBridgerScriptLibrary() {
        $file = public_path('api-lib/aclas_api.php');

        return response()->download($file, "Aclas-API-ESD-Bridger.php");
    }
}
