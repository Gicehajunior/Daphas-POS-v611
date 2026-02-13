<?php

namespace App\Http\Controllers\Custom;

use App\Mail;
use App\Utils\MailUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Controller;

class StripeCustomController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $mailUtil;

    /**
     * Constructor
     *
     * @param  mailUtil  $mailUtil
     * @return void
     */
    public function __construct(MailUtil $mailUtil)
    {
        $this->mailUtil = $mailUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('communication.mail.index');
    }
}