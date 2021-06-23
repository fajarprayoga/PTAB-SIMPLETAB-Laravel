<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomerApi;
use App\Http\Controllers\Controller;
use App\Ledger;
use App\LogNotif;
use App\Mail\MemberEmail;
use App\Mail\ResetEmail;
use App\Member;
use App\NetworkFee;
use App\Order;
use App\OrderDetails;
use App\OrderPoint;
use App\Package;
use App\Product;
use App\Traits\TraitModel;
use Auth;
use Hashids;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Validator;
use Berkayk\OneSignal\OneSignalClient;
use OneSignal;

class CustomersApiController extends Controller
{

    use TraitModel;
    private $onesignal_client;

    public function __construct()
    {
        $this->onesignal_client = new OneSignalClient(env('ONESIGNAL_APP_ID_MEMBER'), env('ONESIGNAL_REST_API_KEY_MEMBER'), '');
    }

    public function logsUpdate($id)
    {
        $logs = LogNotif::find($id);

        $logs->status = 'read';
        $logs->save();
        return response()->json([
            'success' => true,
            'message' => 'Update Log Status is success.',
        ]);

    }

    public function logsUnread(Request $request)
    {
        $logs = LogNotif::where('customers_id', $request->customers_id)
            ->where('status', 'unread')
            ->get();
        return response()->json([
            'success' => true,
            'count' => $logs->count(),
        ]);
    }

    public function logs(Request $request)
    {
        $logs = LogNotif::where('customers_id', $request->customers_id)
            ->orderBy("id", "desc")
            ->get();
        if (!empty($logs)) {
            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Log is empty',
            ], 401);
        }
    }

    public function upImg($id, Request $request)
    {
        $member = Member::find($id);
        $img_path = "/public/images/users";
        if ($request->img != null) {
            $resource = $request->img;
            $name = strtolower($member->code);
            // $img_nama=$request->img->filename;
            // $filename_arr = explode(".", $filename);
            // $filename_count=count($filename_arr);
            // //return $img_nama;
            // $file_ext=$filename_arr[$filename_count-1];
            $file_ext=$request->img->extension();
            $name = str_replace(" ", "-", $name);
            $img_name = $img_path . "/" . $name . "-" . $member->id . ".".$file_ext;
             
            //unlink old
            $resource->move(\base_path() . $img_path, $img_name);
            $member->img = $img_name;
            $member->save();
            return response()->json([
                'success' => true,
                'message' => 'Update Image Profile is Success.',
                'data' => $member,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Image is null',
            ], 401);
        }
    }

    public function resetUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }

        $user = Member::where('email', $request->input('email'))->first();

        if (empty($user)) {
            $message = 'Reset gagal, Email tidak dikenali.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $password = passw_gnr(7);
            $password_ency = bcrypt($password);
            $user->password = $password_ency;
            $user->save();
            foreach ($user as $key => $value) {
                $user->password_raw = $password;
            }
            Mail::to($request->input('email'))->send(new ResetEmail($user));
            $message = 'Reset berhasil, Password baru telah terkirim ke Email.';
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }
    }

    public function members()
    {
        try {
            $members = CustomerApi::select('*')
                ->get();
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kosong.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    public function downline($id)
    {
        $user = CustomerApi::where('ref_id', $id)
            ->where('type', 'member')
            ->orderBy('id', 'DESC')
            ->get();
        if (!empty($user)) {
            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data is empty.',
            ], 401);
        }
    }

    public function membershow(Request $request)
    {
        $user = CustomerApi::where('phone', $request->phone)->first();
        if (!empty($user)) {
            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Phone Number',
            ], 401);
        }
    }

    public function login()
    {

        $user = CustomerApi::where('email', request('email'))
            ->where('type', 'member')
            ->first();
        if ((Hash::check(request('password'), $user->password)) && ($user->status_block == 0)) {
            Auth::login($user);
            if (request('id_onesignal') != null) {
                $user->id_onesignal = request('id_onesignal');
                $user->save();
            }
            $success['token'] = Auth::user()->createToken('authToken')->accessToken;
            //After successfull authentication, notice how I return json parameters
            $user->ref_link = "http://usadhabhakti.com/member?ref=" . Hashids::encode($user->id);
            return response()->json([
                'success' => true,
                'token' => $success,
                'user' => $user,
            ]);
        } else {
            //if authentication is unsuccessfull, notice how I return json parameters
            $message = 'Email & Password yang Anda masukkan salah. Salah memasukkan Email & Password lebih dari 3x maka Account akan otomatis di blokir.';
            if ($user->status_block == 1) {
                $message = 'Your Account is temporary blocked.';
            }
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 401);
        }
    }

    public function loginagent()
    {

        $user = CustomerApi::where('email', request('email'))
            ->where('type', 'agent')
            ->first();
        if ((Hash::check(request('password'), $user->password)) && ($user->status_block == 0)) {
            Auth::login($user);
            if (request('id_onesignal') != null) {
                $user->id_onesignal = request('id_onesignal');
                $user->save();
            }
            $success['token'] = Auth::user()->createToken('authToken')->accessToken;
            //After successfull authentication, notice how I return json parameters
            return response()->json([
                'success' => true,
                'token' => $success,
                'user' => $user,
            ]);
        } else {
            //if authentication is unsuccessfull, notice how I return json parameters
            $message = 'Email & Password yang Anda masukkan salah. Salah memasukkan Email & Password lebih dari 3x maka Account akan otomatis di blokir.';
            if ($user->status_block == 1) {
                $message = 'Your Account is temporary blocked.';
            }
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 401);
        }
    }

    public function userBlock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }

        $user = Member::where('email', $request->input('email'))->first();
        if (empty($user)) {
            $message = 'Update Gagal.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $user->status_block = '1';
            $user->save();
            //response
            $message = 'Update Berhasil.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $user,
            ]);
        }
    }

    /**
     * Register api.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateprofile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
            //'phone' => 'required|unique:customers|regex:/(0)[0-9]{10}/',
            'phone' => 'required',
            //'email' => 'required|email|unique:customers',
            'email' => 'required|email',
            'password' => 'required',
            'address' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }
        $input = $request->all();
        $member = Member::find($input['id']);
        $password_raw = $input['password'];
        $input['password'] = bcrypt($input['password']);
        $member->password = $input['password'];
        $member->name = $input['name'];
        $member->phone = $input['phone'];
        $member->email = $input['email'];
        $member->address = $input['address'];
        try {
            $member->save();
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate Email or Phone Number.',
            ], 401);
        }

        foreach ($member as $key => $value) {
            $member->password_raw = $password_raw;
        }
        $member->ref_link = "http://usadhabhakti.com/member?ref=" . Hashids::encode($member->id);
        Mail::to($request->input('email'))->send(new MemberEmail($member));
        return response()->json([
            'success' => true,
            'data' => $member,
        ]);
    }

    /**
     * Register api.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            //'phone' => 'required|unique:customers|regex:/(0)[0-9]{10}/',
            'phone' => 'required',
            //'email' => 'required|email|unique:customers',
            'email' => 'required|email',
            'password' => 'required',
            'register' => 'required',
            'address' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }
        $input = $request->all();
        $last_code = $this->mbr_get_last_code();
        $code = acc_code_generate($last_code, 8, 3);
        $password_raw = $input['password'];
        $input['password'] = bcrypt($input['password']);
        $input['code'] = $code;
        $input['type'] = 'member';
        $input['status'] = 'pending';
        if (!isset($input['customers_id'])) {
            $ref_def_id = Member::select('id')
                ->Where('def', '=', '1')
                ->get();
            $referals_id = $ref_def_id[0]->id;
            $input['parent_id'] = $referals_id;
            $input['ref_id'] = $referals_id;
        }

        try {
            $user = CustomerApi::create($input);
            $member = $user;
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate Email or Phone Number.',
            ], 401);
        }

        $success['token'] = $user->createToken('appToken')->accessToken;
        foreach ($user as $key => $value) {
            $user->password_raw = $password_raw;
        }
        Mail::to($request->input('email'))->send(new MemberEmail($user));
        return response()->json([
            'success' => true,
            'token' => $success,
            'user' => $user,
        ]);
    }

    /**
     * Register api.
     *
     * @return \Illuminate\Http\Response
     */
    public function registerDownline(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            //'phone' => 'required|unique:customers|regex:/(0)[0-9]{10}/',
            'phone' => 'required',
            //'email' => 'required|email|unique:customers',
            'email' => 'required|email',
            'password' => 'required',
            'register' => 'required',
            'address' => 'required',
            'ref_id' => 'required',
            'package_id' => 'required',
            'agents_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }

        /* point balance */
        //get point referal
        $points_id = 1;
        $points_debit = OrderPoint::where('customers_id', '=', $request->ref_id)
            ->where('type', '=', 'D')
            ->sum('amount');
        $points_credit = OrderPoint::where('customers_id', '=', $request->ref_id)
            ->where('type', '=', 'C')
            ->sum('amount');
        $points_balance = $points_debit - $points_credit;

        //get package price & cogs
        $package = Product::select('price', 'cogs')
            ->where('id', '=', $request->input('package_id'))
            ->get();
        $package = json_decode($package, false);
        $cogs_total = $package[0]->cogs;
        $total = $package[0]->price;
        $profit = $total - $cogs_total;

        if ($points_balance >= $total) {
            $input = $request->all();
            $last_code = $this->mbr_get_last_code();
            $code = acc_code_generate($last_code, 8, 3);
            $password_raw = $input['password'];
            $input['password'] = bcrypt($input['password']);
            $input['code'] = $code;
            $input['type'] = 'member';
            $input['status'] = 'pending';
            $input['parent_id'] = $input['ref_id'];

            try {
                $user = CustomerApi::create($input);
                $member = $user;
            } catch (QueryException $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate Email or Phone Number.',
                ], 401);
            }

            //init
            $register = $request->input('register');
            $memo = 'Aktivasi Member ' . $member->code . "-" . $member->name;
            /* proceed ledger */
            $data = ['register' => $register, 'title' => $memo, 'memo' => $memo, 'status' => 'pending'];
            $ledger = Ledger::create($data);
            $ledger_id = $ledger->id;
            //set ledger entry arr
            //get cashback 01
            //CBA 1
            $networkfee_row = NetworkFee::select('*')
                ->Where('code', '=', 'CBA01')
                ->get();
            $networkfee = (($networkfee_row[0]->amount) / 100) * $total;
            //CBA 2
            $networkfee2_row = NetworkFee::select('*')
                ->Where('code', '=', 'CBA02')
                ->get();
            $networkfee2 = (($networkfee2_row[0]->amount) / 100) * $total;
            //LEV 1
            $lev_fee_row = NetworkFee::select('*')
                ->Where('code', '=', 'LEV')
                ->get();
            $lev_fee_total = (($lev_fee_row[0]->amount) / 100) * $total;
            $lev_fee = ((($lev_fee_row[0]->amount) / 100) * $total) / 9;
            $ref_arr = array();
            $ref_arr = $this->get_ref_exc($member->id, $ref_arr, 1, $member->ref_id);
            $lev_fee_res = (9 - count($ref_arr)) * $lev_fee;
            $lev_fee_com = $lev_fee_total - $lev_fee_res;
            //set ref fee
            $ref_fee_row = NetworkFee::select('*')
                ->Where('code', '=', 'REF')
                ->get();
            $ref_fee = (($ref_fee_row[0]->amount) / 100) * $total;
            //set cashback member 1
            $cashback_mbr_row = NetworkFee::select('*')
                ->Where('code', '=', 'CBM01')
                ->get();
            $cashback_mbr = (($cashback_mbr_row[0]->amount) / 100) * $total;
            //set cashback member 2
            $cashback_mbr2_row = NetworkFee::select('*')
                ->Where('code', '=', 'CBM02')
                ->get();
            $cashback_mbr2 = (($cashback_mbr2_row[0]->amount) / 100) * $total;
            //total disc
            $total_disc = $networkfee_row[0]->amount + $networkfee2_row[0]->amount + $lev_fee_row[0]->amount + $ref_fee_row[0]->amount + $cashback_mbr_row[0]->amount + $cashback_mbr2_row[0]->amount;
            $amount_disc = (($total_disc) / 100) * $total;
            $cba1 = $networkfee;
            $cba2 = $networkfee2;
            //set profit
            $profit_com = $lev_fee_res;
            //set account
            $acc_points = '67'; //utang poin
            $acc_res_cashback = '70';
            $acc_profit = '71';
            $reserve_amount = $cba2 + $ref_fee + $lev_fee_com + $cashback_mbr + $profit_com;
            $points_amount = $reserve_amount - $profit_com;
            $accounts = array($acc_points, $acc_res_cashback, $acc_profit);
            $amounts = array($points_amount, $reserve_amount, $profit_com);
            $types = array('C', 'D', 'C');
            //ledger entries
            for ($account = 0; $account < count($accounts); $account++) {
                if ($accounts[$account] != '') {
                    $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
                }
            }

            /*update member */
            $member->status = 'active';
            $member->save();
            /*set order*/
            //set def
            $referal_id = $request->input('ref_id');
            $agents_id = $request->input('agents_id');
            $warehouses_id = 1;
            $com_row = Member::select('*')
                ->where('def', '=', '1')
                ->get();
            $com_id = $com_row[0]->id;

            //set order
            $last_code = $this->get_last_code('order-agent');
            $order_code = acc_code_generate($last_code, 8, 3);
            $data = array('memo' => $memo, 'total' => $total, 'type' => 'agent_sale', 'status' => 'pending', 'ledgers_id' => $ledger_id, 'customers_id' => $referal_id, 'agents_id' => $agents_id, 'payment_type' => 'point', 'code' => $order_code, 'register' => $register);
            $order = Order::create($data);
            //set order products
            $order->products()->attach($request->input('package_id'), ['quantity' => 1, 'price' => $total, 'cogs' => $cogs_total]);
            //set order order details (inventory stock)
            $package_items = Package::with('products')
                ->where('id', $request->input('package_id'))
                ->get();
            $package_items = json_decode($package_items, false);
            $package_items = $package_items[0]->products;
            //loop items
            foreach ($package_items as $key => $value) {
                $order->productdetails()->attach($value->id, ['quantity' => $value->pivot->quantity, 'type' => 'D', 'status' => 'onhold', 'warehouses_id' => $warehouses_id, 'owner' => $member->id]);
                $order->productdetails()->attach($value->id, ['quantity' => $value->pivot->quantity, 'type' => 'C', 'status' => 'onhold', 'warehouses_id' => $warehouses_id, 'owner' => $agents_id]);
            }

            //set trf points from member to Usadha Bhakti
            $order->points()->attach($points_id, ['amount' => $total, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Penambahan Poin dari (Pending Order) ' . $memo, 'customers_id' => $com_id]);
            $order->points()->attach($points_id, ['amount' => $total, 'type' => 'C', 'status' => 'onhand', 'memo' => 'Pemotongan Poin dari ' . $memo, 'customers_id' => $referal_id]);

            //set trf points cashback agent
            $order->points()->attach($points_id, ['amount' => $cba2, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Penambahan Poin (Cashback Agen 02) dari ' . $memo, 'customers_id' => $agents_id]);
            //set trf points from member to agent
            $order->points()->attach($points_id, ['amount' => $total, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Penambahan Poin dari (Penjualan Paket) ' . $memo, 'customers_id' => $agents_id]);

            //set ref fee
            $order->points()->attach($points_id, ['amount' => $ref_fee, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Poin Komisi (Refferal) dari ' . $memo, 'customers_id' => $member->ref_id]);

            //set level fee
            foreach ($ref_arr as $key => $value) {
                $order->points()->attach($points_id, ['amount' => $lev_fee, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Poin Komisi (Tingkat) dari ' . $memo, 'customers_id' => $value]);
            }
            // $order->points()->attach($points_id, ['amount' => $lev_fee_res, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Sisa komisi Tingkat) dari ' . $memo, 'customers_id' => $com_id]);

            //set cashback member fee
            $order->points()->attach($points_id, ['amount' => $cashback_mbr, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Penambahan Poin (Cashback Belanja) dari ' . $memo, 'customers_id' => $member->id]);

            // //set profit
            // $profit_com = $profit - $ref_fee - $lev_fee_total - $cashback_mbr;
            // $order->points()->attach($points_id, ['amount' => $profit_com, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Keuntungan (Profit) dari ' . $memo, 'customers_id' => $com_id]);
            
            //push notif to agent
            $user_os = CustomerApi::find($agents_id);
            $id_onesignal = $user_os->id_onesignal;
            $memo = 'Order Masuk dari ' . $memo;
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $agents_id, 'memo' => $memo];
            $logs = LogNotif::create($data);
            //push notif
            OneSignal::sendNotificationToUser(
                $memo,
                $id_onesignal,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null
            );

            foreach ($user as $key => $value) {
                $user->password_raw = $password_raw;
            }

            Mail::to($request->input('email'))->send(new MemberEmail($user));

            return response()->json([
                'success' => true,
                'user' => $user,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Saldo Poin Member Tidak Mencukupi.',
            ], 401);
        }
    }

    /**
     * Register api.
     *
     * @return \Illuminate\Http\Response
     */
    public function registerAgent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            //'phone' => 'required|unique:customers|regex:/(0)[0-9]{10}/',
            'phone' => 'required',
            //'email' => 'required|email|unique:customers',
            'email' => 'required|email',
            'password' => 'required',
            'register' => 'required',
            'address' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }
        $input = $request->all();
        $last_code = $this->get_last_code('agent');
        $code = acc_code_generate($last_code, 8, 3);
        $password_raw = $input['password'];
        $input['password'] = bcrypt($input['password']);
        $input['code'] = $code;
        $input['type'] = 'agent';
        $input['status'] = 'pending';
        $input['parent_id'] = 0;
        $input['ref_id'] = 0;

        try {
            $user = CustomerApi::create($input);
            $member = $user;
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate Email or Phone Number.',
            ], 401);
        }

        $success['token'] = $user->createToken('appToken')->accessToken;
        foreach ($user as $key => $value) {
            $user->password_raw = $password_raw;
        }
        Mail::to($request->input('email'))->send(new MemberEmail($user));
        return response()->json([
            'success' => true,
            'token' => $success,
            'user' => $user,
        ]);
    }

    public function activate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'package_id' => 'required',
            'agents_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        } else {
            //set member
            $member = Member::find($request->input('id'));
            //get point member
            $points_id = 1;
            $points_debit = OrderPoint::where('customers_id', '=', $request->input('id'))
                ->where('type', '=', 'D')
                ->sum('amount');
            $points_credit = OrderPoint::where('customers_id', '=', $request->input('id'))
                ->where('type', '=', 'C')
                ->sum('amount');
            $points_balance = $points_debit - $points_credit;

            //get package price & cogs
            $package = Product::select('price', 'cogs')
                ->where('id', '=', $request->input('package_id'))
                ->get();
            $package = json_decode($package, false);
            $cogs_total = $package[0]->cogs;
            $total = $package[0]->price;
            $profit = $total - $cogs_total;

            //get stock agent, loop package
            $stock_status = 'true';
            $package_items = Package::with('products')
                ->where('id', $request->input('package_id'))
                ->get();
            $package_items = json_decode($package_items, false);
            $package_items = $package_items[0]->products;
            //loop items
            foreach ($package_items as $key => $value) {
                //get qty package product & compare sum stock
                $stock_debit = OrderDetails::where('owner', '=', $request->input('agents_id'))
                    ->where('type', '=', 'D')
                    ->where('status', '=', 'onhand')
                    ->where('products_id', $value->id)
                    ->sum('quantity');
                $stock_credit = OrderDetails::where('owner', '=', $request->input('agents_id'))
                    ->where('type', '=', 'C')
                    ->where('status', '=', 'onhand')
                    ->where('products_id', $value->id)
                    ->sum('quantity');
                $stock_balance = $stock_debit - $stock_credit;
                if ($stock_balance < $value->pivot->quantity) {
                    $stock_status = 'false';
                }
            }

            //compare total to point belanja
            if ($points_balance >= $total && $member->status == 'pending') {
                //init
                $register = date("Y-m-d");
                $memo = 'Aktivasi Member ' . $member->code . "-" . $member->name;
                /* proceed ledger */
                $data = ['register' => $register, 'title' => $memo, 'memo' => $memo, 'status' => 'pending'];
                $ledger = Ledger::create($data);
                $ledger_id = $ledger->id;
                //set ledger entry arr
                //get cashback 01
                //CBA 1
                $networkfee_row = NetworkFee::select('*')
                    ->Where('code', '=', 'CBA01')
                    ->get();
                $networkfee = (($networkfee_row[0]->amount) / 100) * $total;
                //CBA 2
                $networkfee2_row = NetworkFee::select('*')
                    ->Where('code', '=', 'CBA02')
                    ->get();
                $networkfee2 = (($networkfee2_row[0]->amount) / 100) * $total;
                //LEV 1
                $lev_fee_row = NetworkFee::select('*')
                    ->Where('code', '=', 'LEV')
                    ->get();
                $lev_fee_total = (($lev_fee_row[0]->amount) / 100) * $total;
                $lev_fee = ((($lev_fee_row[0]->amount) / 100) * $total) / 9;
                $ref_arr = array();
                $ref_arr = $this->get_ref_exc($member->id, $ref_arr, 1, $member->ref_id);
                $lev_fee_res = (9 - count($ref_arr)) * $lev_fee;
                $lev_fee_com = $lev_fee_total - $lev_fee_res;
                //set ref fee
                $ref_fee_row = NetworkFee::select('*')
                    ->Where('code', '=', 'REF')
                    ->get();
                $ref_fee = (($ref_fee_row[0]->amount) / 100) * $total;
                //set cashback member 1
                $cashback_mbr_row = NetworkFee::select('*')
                    ->Where('code', '=', 'CBM01')
                    ->get();
                $cashback_mbr = (($cashback_mbr_row[0]->amount) / 100) * $total;
                //set cashback member 2
                $cashback_mbr2_row = NetworkFee::select('*')
                    ->Where('code', '=', 'CBM02')
                    ->get();
                $cashback_mbr2 = (($cashback_mbr2_row[0]->amount) / 100) * $total;
                //total disc
                $total_disc = $networkfee_row[0]->amount + $networkfee2_row[0]->amount + $lev_fee_row[0]->amount + $ref_fee_row[0]->amount + $cashback_mbr_row[0]->amount + $cashback_mbr2_row[0]->amount;
                $amount_disc = (($total_disc) / 100) * $total;
                $cba1 = $networkfee;
                $cba2 = $networkfee2;
                //set profit
                $profit_com = $lev_fee_res;
                //set account
                $acc_points = '67'; //utang poin
                $acc_res_cashback = '70';
                $acc_profit = '71';
                $reserve_amount = $cba2 + $ref_fee + $lev_fee_com + $cashback_mbr + $profit_com;
                $points_amount = $reserve_amount - $profit_com;
                $accounts = array($acc_points, $acc_res_cashback, $acc_profit);
                $amounts = array($points_amount, $reserve_amount, $profit_com);
                $types = array('C', 'D', 'C');
                //ledger entries
                for ($account = 0; $account < count($accounts); $account++) {
                    if ($accounts[$account] != '') {
                        $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
                    }
                }

                /*update member */
                $member->status = 'active';
                $member->save();
                /*set order*/
                //set def
                $referal_id = $request->input('id');
                $agents_id = $request->input('agents_id');
                $warehouses_id = 1;
                $com_row = Member::select('*')
                    ->where('def', '=', '1')
                    ->get();
                $com_id = $com_row[0]->id;

                //set order
                $last_code = $this->get_last_code('order-agent');
                $order_code = acc_code_generate($last_code, 8, 3);
                $data = array('memo' => $memo, 'total' => $total, 'type' => 'agent_sale', 'status' => 'pending', 'ledgers_id' => $ledger_id, 'customers_id' => $referal_id, 'agents_id' => $agents_id, 'payment_type' => 'point', 'code' => $order_code, 'register' => $register);
                $order = Order::create($data);
                //set order products
                $order->products()->attach($request->input('package_id'), ['quantity' => 1, 'price' => $total, 'cogs' => $cogs_total]);
                //set order order details (inventory stock)
                $package_items = Package::with('products')
                    ->where('id', $request->input('package_id'))
                    ->get();
                $package_items = json_decode($package_items, false);
                $package_items = $package_items[0]->products;
                //loop items
                foreach ($package_items as $key => $value) {
                    $order->productdetails()->attach($value->id, ['quantity' => $value->pivot->quantity, 'type' => 'D', 'status' => 'onhold', 'warehouses_id' => $warehouses_id, 'owner' => $member->id]);
                    $order->productdetails()->attach($value->id, ['quantity' => $value->pivot->quantity, 'type' => 'C', 'status' => 'onhold', 'warehouses_id' => $warehouses_id, 'owner' => $agents_id]);
                }

                //set trf points from member to Usadha Bhakti
                $order->points()->attach($points_id, ['amount' => $total, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Penambahan Poin dari (Pending Order) ' . $memo, 'customers_id' => $com_id]);
                $order->points()->attach($points_id, ['amount' => $total, 'type' => 'C', 'status' => 'onhand', 'memo' => 'Pemotongan Poin dari ' . $memo, 'customers_id' => $referal_id]);

                //set trf points cashback agent
                $order->points()->attach($points_id, ['amount' => $cba2, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Penambahan Poin (Cashback Agen 02) dari ' . $memo, 'customers_id' => $agents_id]);
                //set trf points from member to agent
                $order->points()->attach($points_id, ['amount' => $total, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Penambahan Poin dari (Penjualan Paket) ' . $memo, 'customers_id' => $agents_id]);

                //set ref fee
                $order->points()->attach($points_id, ['amount' => $ref_fee, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Poin Komisi (Refferal) dari ' . $memo, 'customers_id' => $member->ref_id]);
                //set level fee
                foreach ($ref_arr as $key => $value) {
                    $order->points()->attach($points_id, ['amount' => $lev_fee, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Poin Komisi (Tingkat) dari ' . $memo, 'customers_id' => $value]);
                }
                // $order->points()->attach($points_id, ['amount' => $lev_fee_res, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Sisa komisi Tingkat) dari ' . $memo, 'customers_id' => $com_id]);
                //set cashback member fee
                $order->points()->attach($points_id, ['amount' => $cashback_mbr, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Penambahan Poin (Cashback Belanja) dari ' . $memo, 'customers_id' => $member->id]);
                // //set profit
                // $profit_row = NetworkFee::select('*')
                //     ->Where('code', '=', 'PRF01')
                //     ->get();
                // //$profit_com = (($profit_row[0]->amount) / 100) * $total;
                // $profit_com = $profit - $ref_fee - $lev_fee_total - $cashback_mbr;
                // $order->points()->attach($points_id, ['amount' => $profit_com, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Keuntungan (Profit) dari ' . $memo, 'customers_id' => $com_id]);

                //push notif to agent
                $user_os = CustomerApi::find($agents_id);
                $id_onesignal = $user_os->id_onesignal;
                $memo = 'Order Masuk dari ' . $memo;
                $register = date("Y-m-d");
                //store to logs_notif
                $data = ['register' => $register, 'customers_id' => $agents_id, 'memo' => $memo];
                $logs = LogNotif::create($data);
                //push notif
                OneSignal::sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Aktivasi Member Berhasil!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Poin atau Stok Barang Tidak Mencukupi! atau Member sudah aktif! Poin Balance: ' . $points_balance . " Total package: " . $total . " Stok Agent: " . $stock_balance . " Member Satus: " . $member->status,
                ], 401);
            }
        }

    }

    public function activateAgent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'package_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        } else {
            //set member
            $member = Member::find($request->input('id'));
            //get point member
            $points_id = 1;
            $points_debit = OrderPoint::where('customers_id', '=', $request->input('id'))
                ->where('type', '=', 'D')
                ->sum('amount');
            $points_credit = OrderPoint::where('customers_id', '=', $request->input('id'))
                ->where('type', '=', 'C')
                ->sum('amount');
            $points_balance = $points_debit - $points_credit;

            //get package price & cogs
            $package = Product::select('price', 'cogs')
                ->where('id', '=', $request->input('package_id'))
                ->get();
            $package = json_decode($package, false);
            $cogs_total = $package[0]->cogs;
            $total = $package[0]->price;
            $profit = $total - $cogs_total;

            //compare total to point belanja
            if ($points_balance >= $total && $member->status == 'pending') {
                //init
                $register = date("Y-m-d");
                $memo = 'Aktivasi Agen ' . $member->code . "-" . $member->name;
                /* proceed ledger */
                $data = ['register' => $register, 'title' => $memo, 'memo' => $memo];
                $ledger = Ledger::create($data);
                $ledger_id = $ledger->id;
                //set ledger entry arr
                $acc_inv_stock = '20';
                $acc_sale = '44';
                $acc_exp_cogs = '45';
                $acc_points = '67'; //utang poin
                $total_pay = $total;
                $accounts = array($acc_inv_stock, $acc_exp_cogs, $acc_sale);
                $amounts = array($cogs_total, $cogs_total, $total);
                $types = array('C', 'D', 'C');
                //if agent get cashback
                $customer_row = CustomerApi::select('*')
                    ->Where('id', '=', $request->input('id'))
                    ->get();
                if ($customer_row[0]->type == 'agent') {
                    //get cashback 01
                    $acc_disc = 68;
                    $acc_res_cashback = 70;
                    //CBA 1
                    $networkfee_row = NetworkFee::select('*')
                        ->Where('code', '=', 'CBA01')
                        ->get();
                    //CBA 2
                    $networkfee2_row = NetworkFee::select('*')
                        ->Where('code', '=', 'CBA02')
                        ->get();
                    //LEV 1
                    $lev_fee_row = NetworkFee::select('*')
                        ->Where('code', '=', 'LEV')
                        ->get();
                    //set ref fee
                    $ref_fee_row = NetworkFee::select('*')
                        ->Where('code', '=', 'REF')
                        ->get();
                    //set cashback member 1
                    $cashback_mbr_row = NetworkFee::select('*')
                        ->Where('code', '=', 'CBM01')
                        ->get();
                    //set cashback member 2
                    $cashback_mbr2_row = NetworkFee::select('*')
                        ->Where('code', '=', 'CBM02')
                        ->get();
                    $cba1 = (($networkfee_row[0]->amount) / 100) * $total;
                    $cba2 = (($networkfee2_row[0]->amount) / 100) * $total;
                    $total_disc = $networkfee_row[0]->amount + $networkfee2_row[0]->amount + $lev_fee_row[0]->amount + $ref_fee_row[0]->amount + $cashback_mbr_row[0]->amount + $cashback_mbr2_row[0]->amount;
                    $amount_disc = (($total_disc) / 100) * $total;
                    $amount_res_cashback = $amount_disc - $cba1;
                    $total_pay = $total - $cba1;
                    //$acc_points = '67';
                    //push array jurnal
                    array_push($accounts, $acc_disc, $acc_res_cashback, $acc_points);
                    array_push($amounts, $amount_disc, $amount_res_cashback, $total_pay);
                    array_push($types, "D", "C", "D");
                }
                //ledger entries
                for ($account = 0; $account < count($accounts); $account++) {
                    if ($accounts[$account] != '') {
                        $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
                    }
                }

                /*update member */
                $member->status = 'active';
                $member->save();
                /* set order, order products, order details (inventory stock), order points */
                //set def
                $ref_def_id = CustomerApi::select('id')
                    ->Where('def', '=', '1')
                    ->get();
                $owner_def = $ref_def_id[0]->id;
                $customers_id = $request->input('id');
                $warehouses_id = 1;
                //set order
                $last_code = $this->get_last_code('order');
                $order_code = acc_code_generate($last_code, 8, 3);
                $data = array('memo' => $memo, 'total' => $total, 'type' => 'sale', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $customers_id, 'payment_type' => 'point', 'code' => $order_code, 'register' => $register);
                $order = Order::create($data);
                //set order products
                $order->products()->attach($request->input('package_id'), ['quantity' => 1, 'price' => $total]);
                //set order order details (inventory stock)
                $package_items = Package::with('products')
                    ->where('id', $request->input('package_id'))
                    ->get();
                $package_items = json_decode($package_items, false);
                $package_items = $package_items[0]->products;
                //loop items
                foreach ($package_items as $key => $value) {
                    $order->productdetails()->attach($value->id, ['quantity' => $value->pivot->quantity, 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                    $order->productdetails()->attach($value->id, ['quantity' => $value->pivot->quantity, 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $owner_def]);
                }
                //set trf points from customer to Usdha Bhakti
                // $order->points()->attach($points_id, ['amount' => $total_pay, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Penambahan Poin dari ' . $memo, 'customers_id' => $owner_def]);
                $order->points()->attach($points_id, ['amount' => $total_pay, 'type' => 'C', 'status' => 'onhand', 'memo' => 'Pemotongan Poin dari ' . $memo, 'customers_id' => $customers_id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Aktivasi Agen Berhasil!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Poin atau Stok Barang Tidak Mencukupi! atau Member sudah aktif! Poin Balance: ' . $points_balance . " Total package: " . $total . " Agen Satus: " . $member->status,
                ], 401);
            }
        }

    }

    public function logout(Request $res)
    {
        if (Auth::user()) {
            $user = Auth::user()->token();
            $user->revoke();

            return response()->json([
                'success' => true,
                'message' => 'Logout successfully',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unable to Logout',
            ]);
        }
    }

    public function agents()
    {
        $agents = CustomerApi::select('*')
            ->where('type', 'agent')
            ->get();

        // return $agents;

        if (is_null($agents)) {
            $message = 'Data not found.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $message = 'Data retrieved successfully.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $agents,
            ]);
        }
    }

    public function agentshow($id)
    {
        $agent = CustomerApi::find($id);

        //Check if agent found or not.
        if (is_null($agent)) {
            $message = 'Product not found.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        }
        $message = 'Product retrieved successfully.';
        $status = true;
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $agent,
        ]);
    }

}
