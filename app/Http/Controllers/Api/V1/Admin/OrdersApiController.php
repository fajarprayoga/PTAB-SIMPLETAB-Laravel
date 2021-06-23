<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Customer;
use App\Http\Controllers\Controller;
use App\Ledger;
use App\LogNotif;
use App\Mail\OrderEmail;
use App\Member;
use App\NetworkFee;
use App\Order;
use App\OrderPoint;
use App\Package;
use App\Product;
use App\Traits\TraitModel;
use Berkayk\OneSignal\OneSignalClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use OneSignal;

class OrdersApiController extends Controller
{
    use TraitModel;
    private $onesignal_client;

    public function __construct()
    {
        $this->onesignal_client = new OneSignalClient(env('ONESIGNAL_APP_ID_MEMBER'), env('ONESIGNAL_REST_API_KEY_MEMBER'), '');
    }

    public function test($id)
    {
        $order = Order::find($id);
        //get relate point
        //$return_out="";
        $order_points_arr = OrderPoint::where('orders_id', $order->id)->get();
        foreach ($order_points_arr as $order_points_id) {
            //push notif
            $user_os = Customer::find($order_points_id->customers_id);
            $id_onesignal = $user_os->id_onesignal;
            if(!empty($id_onesignal)){
            //$return_out .="-".$id_onesignal;
            $memo = $order_points_id->memo;
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $order_points_id->customers_id, 'memo' => $memo];
            $logs = LogNotif::create($data);
            //push notif
            if($user_os->type=='agent'){
                OneSignal::sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }else{
                $this->onesignal_client->sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }
            }
        }
        //return $return_out;
    }
    
    public function orderCancel($id)
    {
        /*update order status */
        $order = Order::find($id);
        if ($order->status == 'pending') {
            $order->status = 'closed';
            $order->status_delivery = 'pending';
            $order->save();
            //update pivot points
            $points_id = 1;
            $order->points()->updateExistingPivot($points_id, [
                'status' => 'onhold',
            ]);
            //update pivot products details
            $ids = $order->productdetails()->allRelatedIds();
            foreach ($ids as $products_id) {
                $order->productdetails()->updateExistingPivot($products_id, ['status' => 'onhold']);
            }
            //update ledger
            $ledger = Ledger::find($order->ledgers_id);
            $ledger->status = 'closed';
            $ledger->save();
            //push notif to member
            $user = Customer::find($order->customers_id);
            $id_onesignal = $user->id_onesignal;
            $memo = 'Hallo ' . $user->name . ', Order ' . $order->code . ' telah dibatalkan.';
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $order->customers_id, 'memo' => $memo];
            $logs = LogNotif::create($data);
            //push notif
            $this->onesignal_client->sendNotificationToUser(
                $memo,
                $id_onesignal,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null
            );
            //push notif to agent
            $user_os = Customer::find($order->agents_id);
            $id_onesignal = $user_os->id_onesignal;
            $memo = 'Hallo ' . $user_os->name . ', Order ' . $order->code . ' telah dibatalkan.';
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $order->agents_id, 'memo' => $memo];
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
            //response
            $message = 'Pesanan Sudah Dibatalkan.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $message = 'Pembatalan Gagal.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        }
    }

    public function orderAgentProcess($id)
    {
        /*update order status */
        $order = Order::find($id);
        if ($order->status == 'pending') {
            $order->status = 'approved';
            $order->status_delivery = 'process';
            $order->save();
            //push notif
            $user = Customer::find($order->customers_id);
            $id_onesignal = $user->id_onesignal;
            $memo = 'Hallo ' . $user->name . ', Order ' . $order->code . ' sudah diproses.';
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $order->customers_id, 'memo' => $memo];
            $logs = LogNotif::create($data);
            //push notif
            $this->onesignal_client->sendNotificationToUser(
                $memo,
                $id_onesignal,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null
            );
            $message = 'Proses Order Berhasil.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $message = 'Proses Order Gagal.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        }
    }

    public function deliveryAgentUpdate($id)
    {
        /*update order status */
        $order = Order::find($id);
        if ($order->status == 'approved' && $order->status_delivery == 'process') {
            $order->status_delivery = 'delivered';
            $order->save();
            //push notif
            $user = Customer::find($order->customers_id);
            $id_onesignal = $user->id_onesignal;
            $memo = 'Hallo ' . $user->name . ', Order ' . $order->code . ' sudah dikirimkan.';
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $order->customers_id, 'memo' => $memo];
            $logs = LogNotif::create($data);
            //push notif
            $this->onesignal_client->sendNotificationToUser(
                $memo,
                $id_onesignal,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null
            );
            $message = 'Update Delivery Status Berhasil.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $message = 'Update Delivery Status Gagal.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        }
    }

    public function deliveryMemberUpdate($id)
    {
        /*update order status */
        $order = Order::find($id);
        if ($order->status == 'approved' && $order->status_delivery == 'delivered') {
            $order->status_delivery = 'received';
            $order->save();
            //set trf points from Usadha Bhakti to Agent
            $com_row = Member::select('*')
                ->where('def', '=', '1')
                ->get();
            $com_id = $com_row[0]->id;
            $points_id = 1;
            $memo = $order->memo;
            $total = $order->total;
            $order->points()->attach($points_id, ['amount' => $total, 'type' => 'C', 'status' => 'onhand', 'memo' => 'Balik Poin dari ' . $memo, 'customers_id' => $com_id]);
            //update pivot points
            $order->points()->updateExistingPivot($points_id, [
                'status' => 'onhand',
            ]);
            //update pivot products details
            $ids = $order->productdetails()->allRelatedIds();
            foreach ($ids as $products_id) {
                $order->productdetails()->updateExistingPivot($products_id, ['status' => 'onhand']);
            }
            //update ledger
            $ledger = Ledger::find($order->ledgers_id);
            $ledger->status = 'approved';
            $ledger->save();

            //get relate point
            $order_points_arr = OrderPoint::where('orders_id', $order->id)->get();
            foreach ($order_points_arr as $order_points_id) {
                //push notif
                $user_os = Customer::find($order_points_id->customers_id);
                $id_onesignal = $user_os->id_onesignal;
                if(!empty($id_onesignal)){
                $memo = $order_points_id->memo;
                $register = date("Y-m-d");
                //store to logs_notif
                $data = ['register' => $register, 'customers_id' => $order_points_id->customers_id, 'memo' => $memo];
                $logs = LogNotif::create($data);
                //push notif
                if($user_os->type=='agent'){
                    OneSignal::sendNotificationToUser(
                        $memo,
                        $id_onesignal,
                        $url = null,
                        $data = null,
                        $buttons = null,
                        $schedule = null
                    );
                }else{
                    $this->onesignal_client->sendNotificationToUser(
                        $memo,
                        $id_onesignal,
                        $url = null,
                        $data = null,
                        $buttons = null,
                        $schedule = null
                    );
                }
            }
            }

            //push notif to agent
            $user = Customer::find($order->agents_id);
            $id_onesignal = $user->id_onesignal;
            $memo = 'Hallo ' . $user->name . ', Order ' . $order->code . ' sudah diterima pelanggan.';
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $order->agents_id, 'memo' => $memo];
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
            //response
            $message = 'Pesanan Sudah Diterima.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $message = 'Update Delivery Status Gagal.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        }
    }

    public function history($id)
    {
        $orders = Order::with('customers')
            ->with('products')
            ->with('productdetails')
            ->with('agents')
            ->where('customers_id', '=', $id)
            ->where(function ($query) {
                $query->where('type', 'agent_sale')
                    ->orWhere('type', 'sale');
            })
            ->orderBy('id', 'DESC')
            ->get();

        //Check if history found or not.
        if (is_null($orders)) {
            $message = 'History Order not found.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $message = 'History retrieved successfully.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $orders,
            ]);
        }
    }

    public function historyAgent($id)
    {
        $orders = Order::with('customers')
            ->with('products')
            ->with('productdetails')
            ->where('agents_id', $id)
            ->where('type', 'agent_sale')
            ->orderBy('id', 'DESC')
            ->get();

        //Check if history found or not.
        if (is_null($orders)) {
            $message = 'History Order not found.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $message = 'History retrieved successfully.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $orders,
            ]);
        }
    }

    public function storeAgent(Request $request)
    {
        //get total
        $total = 0;
        $cogs_total = 0;
        $data = json_encode($request->all());
        $package = json_decode($data, false);
        $cart_arr = $package->cart;
        $count_cart = count($cart_arr);
        for ($i = 0; $i < $count_cart; $i++) {
            $total += $cart_arr[$i]->quantity * $cart_arr[$i]->price;
            $product = Product::find($cart_arr[$i]->products_id);
            $cogs_total += $cart_arr[$i]->quantity * $product->cogs;
        }

        /* set customer & point balance */
        $customer = Customer::find($request->customers_id);
        //get point customer
        $points_id = 1;
        $points_debit = OrderPoint::where('customers_id', '=', $request->customers_id)
            ->where('type', '=', 'D')
            ->sum('amount');
        $points_credit = OrderPoint::where('customers_id', '=', $request->customers_id)
            ->where('type', '=', 'C')
            ->sum('amount');
        $points_balance = $points_debit - $points_credit;

        //compare total to points_balance
        if ($points_balance >= $total) {
            /* proceed ledger */
            $memo = 'Transaksi Marketplace Agen ' . $customer->code . "-" . $customer->name;
            $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo];
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
            $customer_row = Customer::select('*')
                ->Where('id', '=', $request->input('customers_id'))
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

            /* set order, order products, order details (inventory stock), order points */
            //set def
            $ref_def_id = Customer::select('id')
                ->Where('def', '=', '1')
                ->get();
            $owner_def = $ref_def_id[0]->id;
            $customers_id = $request->customers_id;
            $warehouses_id = 1;
            //set order
            $last_code = $this->get_last_code('order');
            $order_code = acc_code_generate($last_code, 8, 3);
            $register = $request->register;
            $data = array('memo' => $memo, 'total' => $total, 'type' => 'sale', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $customers_id, 'payment_type' => 'point', 'code' => $order_code, 'register' => $register);
            $order = Order::create($data);
            for ($i = 0; $i < $count_cart; $i++) {
                //set order products
                $order->products()->attach($cart_arr[$i]->products_id, ['quantity' => $cart_arr[$i]->quantity, 'price' => $cart_arr[$i]->price]);
                //set order order details (inventory stock)
                //check if package
                $products_type = Product::select('type')
                    ->where('id', $cart_arr[$i]->products_id)
                    ->get();
                $products_type = json_decode($products_type, false);
                if ($products_type[0]->type == 'package') {
                    $package_items = Package::with('products')
                        ->where('id', $cart_arr[$i]->products_id)
                        ->get();
                    $package_items = json_decode($package_items, false);
                    $package_items = $package_items[0]->products;
                    //loop items
                    foreach ($package_items as $key => $value) {
                        $order->productdetails()->attach($value->id, ['quantity' => $cart_arr[$i]->quantity * $value->pivot->quantity, 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $owner_def]);
                        $order->productdetails()->attach($value->id, ['quantity' => $cart_arr[$i]->quantity * $value->pivot->quantity, 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                    }
                } else {
                    $order->productdetails()->attach($cart_arr[$i]->products_id, ['quantity' => $cart_arr[$i]->quantity, 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $owner_def]);
                    $order->productdetails()->attach($cart_arr[$i]->products_id, ['quantity' => $cart_arr[$i]->quantity, 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                }
            }
            //set trf points from customer to Usdha Bhakti
            // $order->points()->attach($points_id, ['amount' => $total_pay, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Penambahan Poin dari ' . $memo, 'customers_id' => $owner_def]);
            $order->points()->attach($points_id, ['amount' => $total_pay, 'type' => 'C', 'status' => 'onhand', 'memo' => 'Pemotongan Poin dari ' . $memo, 'customers_id' => $customers_id]);

            //send invoice email
            $customer = Customer::find($customers_id);
            Mail::to($customer->email)->send(new OrderEmail($order->id, $customers_id));

            return response()->json([
                'success' => true,
                'message' => 'Aktivasi Member Berhasil!',
                'email' => $customer->email,
                'order_id' => $order->id,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Saldo Poin Member Tidak Mencukupi.',
            ], 401);
        }
    }

    public function store(Request $request)
    {
        //get total
        $total = 0;
        $cogs_total = 0;
        $profit = 0;
        $data = json_encode($request->all());
        $package = json_decode($data, false);
        $cart_arr = $package->cart;
        $count_cart = count($cart_arr);
        for ($i = 0; $i < $count_cart; $i++) {
            $total += $cart_arr[$i]->quantity * $cart_arr[$i]->price;
            $product = Product::find($cart_arr[$i]->products_id);
            $cogs_total += $cart_arr[$i]->quantity * $product->cogs;
        }
        $profit = $total - $cogs_total;

        //set member & point balance
        $member = Customer::find($request->customers_id);
        //get point member
        $points_id = 1;
        $points_debit = OrderPoint::where('customers_id', '=', $request->customers_id)
            ->where('type', '=', 'D')
            ->where('status', '=', 'onhand')
            ->sum('amount');
        $points_credit = OrderPoint::where('customers_id', '=', $request->customers_id)
            ->where('type', '=', 'C')
            ->where('status', '=', 'onhand')
            ->sum('amount');
        $points_balance = $points_debit - $points_credit;

        //compare total to point belanja
        if ($points_balance >= $total) {
            /* proceed ledger */
            $memo = 'Transaksi Marketplace Member ' . $member->code . "-" . $member->name;
            $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo, 'status' => 'pending'];
            $ledger = Ledger::create($data);
            $ledger_id = $ledger->id;
            //set ledger entry arr
            $profit_inactive = 0;
            //CBA 1
            $networkfee_row = NetworkFee::select('*')
                ->Where('code', '=', 'CBA01')
                ->get();
            $networkfee_amount = $networkfee_row[0]->amount;
            $networkfee = ($networkfee_amount / 100) * $total;
            //CBA 2
            $networkfee2_row = NetworkFee::select('*')
                ->Where('code', '=', 'CBA02')
                ->get();
            $networkfee2_amount = $networkfee2_row[0]->amount;
            $networkfee2 = ($networkfee2_amount / 100) * $total;
            //LEV 1
            $lev_fee_row = NetworkFee::select('*')
                ->Where('code', '=', 'LEV')
                ->get();
            $lev_fee_amount = $lev_fee_row[0]->amount;
            $lev_fee_total = ($lev_fee_amount / 100) * $total;
            //LEV 2
            $lev_fee2_row = NetworkFee::select('*')
                ->Where('code', '=', 'LEV02')
                ->get();
            $lev_fee_total2 = (($lev_fee2_row[0]->amount) / 100) * $total;
            $lev_fee = ((($lev_fee2_row[0]->amount) / 100) * $total) / 9;
            $ref_arr = array();
            $ref_arr = $this->get_ref($member->id, $ref_arr, 1);
            $lev_fee_res = (9 - count($ref_arr)) * $lev_fee;
            $lev_fee_com = $lev_fee_total2 - $lev_fee_res;
            $lev_fee_prof = $lev_fee_total - $lev_fee_com;
            //set ref fee
            $ref_fee_row = NetworkFee::select('*')
                ->Where('code', '=', 'REF')
                ->get();
            $ref_fee_amount = $ref_fee_row[0]->amount;
            $ref_fee = ($ref_fee_amount / 100) * $total;
            //set cashback member 1
            $cashback_mbr_row = NetworkFee::select('*')
                ->Where('code', '=', 'CBM01')
                ->get();
            $cashback_mbr_amount = $cashback_mbr_row[0]->amount;
            $cashback_mbr = ($cashback_mbr_amount / 100) * $total;
            //set cashback member 2
            $cashback_mbr2_row = NetworkFee::select('*')
                ->Where('code', '=', 'CBM02')
                ->get();
            $cashback_mbr2_amount = $cashback_mbr2_row[0]->amount;
            $cashback_mbr2 = ($cashback_mbr2_amount / 100) * $total;
            //total disc
            $total_disc = $networkfee_amount + $networkfee2_amount + $lev_fee_amount + $ref_fee_amount + $cashback_mbr_amount + $cashback_mbr2_amount;
            $amount_disc = (($total_disc) / 100) * $total;
            $cba1 = $networkfee;
            $cba2 = $networkfee2;
            //set profit
            $profit_com = $lev_fee_prof + $ref_fee;
            //set account
            $acc_points = '67'; //utang poin
            $acc_res_cashback = '70';
            $acc_profit = '71';
            $reserve_amount = $cba2 + $lev_fee_com + $cashback_mbr + $cashback_mbr2 + $profit_com;
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

            /*set order*/
            //set def
            $customers_id = $request->customers_id;
            $agents_id = $request->agents_id;
            $warehouses_id = 1;
            $com_row = Member::select('*')
                ->where('def', '=', '1')
                ->get();
            $com_id = $com_row[0]->id;
            //set order
            $last_code = $this->get_last_code('order-agent');
            $order_code = acc_code_generate($last_code, 8, 3);
            $register = $request->register;
            $data = array('memo' => $memo, 'total' => $total, 'type' => 'agent_sale', 'status' => 'pending', 'ledgers_id' => $ledger_id, 'customers_id' => $customers_id, 'agents_id' => $agents_id, 'payment_type' => 'point', 'code' => $order_code, 'register' => $register);
            $order = Order::create($data);
            for ($i = 0; $i < $count_cart; $i++) {
                //set order products
                $order->products()->attach($cart_arr[$i]->products_id, ['quantity' => $cart_arr[$i]->quantity, 'price' => $cart_arr[$i]->price]);
                //set order order details (inventory stock)
                //check if package
                $products_type = Product::select('type')
                    ->where('id', $cart_arr[$i]->products_id)
                    ->get();
                $products_type = json_decode($products_type, false);
                if ($products_type[0]->type == 'package') {
                    $package_items = Package::with('products')
                        ->where('id', $cart_arr[$i]->products_id)
                        ->get();
                    $package_items = json_decode($package_items, false);
                    $package_items = $package_items[0]->products;
                    //loop items
                    foreach ($package_items as $key => $value) {
                        $order->productdetails()->attach($value->id, ['quantity' => $cart_arr[$i]->quantity * $value->pivot->quantity, 'type' => 'C', 'status' => 'onhold', 'warehouses_id' => $warehouses_id, 'owner' => $agents_id]);
                        $order->productdetails()->attach($value->id, ['quantity' => $cart_arr[$i]->quantity * $value->pivot->quantity, 'type' => 'D', 'status' => 'onhold', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                    }
                } else {
                    $order->productdetails()->attach($cart_arr[$i]->products_id, ['quantity' => $cart_arr[$i]->quantity, 'type' => 'C', 'status' => 'onhold', 'warehouses_id' => $warehouses_id, 'owner' => $agents_id]);
                    $order->productdetails()->attach($cart_arr[$i]->products_id, ['quantity' => $cart_arr[$i]->quantity, 'type' => 'D', 'status' => 'onhold', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                }
            }
            //set trf points from member to Usadha Bhakti
            $order->points()->attach($points_id, ['amount' => $total, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Penambahan Poin dari (Pending Order) ' . $memo, 'customers_id' => $com_id]);
            $order->points()->attach($points_id, ['amount' => $total, 'type' => 'C', 'status' => 'onhand', 'memo' => 'Pemotongan Poin dari ' . $memo, 'customers_id' => $customers_id]);

            //set trf points from usadha to agent
            $order->points()->attach($points_id, ['amount' => $cba2, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Penambahan Poin (Cashback Agen 2) dari ' . $memo, 'customers_id' => $agents_id]);

            //set trf points from member to agent
            $order->points()->attach($points_id, ['amount' => $total, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Penambahan Poin dari (Penjualan Paket) ' . $memo, 'customers_id' => $agents_id]);

            // //set ref fee
            // $ref_fee_row = NetworkFee::select('*')
            //     ->Where('code', '=', 'REF')
            //     ->get();
            // $ref_fee = (($ref_fee_row[0]->amount) / 100) * $profit;
            // $order->points()->attach($points_id, ['amount' => $ref_fee, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Refferal) dari '.$memo, 'customers_id' => $member->ref_id]);

            //set level fee
            foreach ($ref_arr as $key => $value) {
                $order->points()->attach($points_id, ['amount' => $lev_fee, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Poin Komisi (Tingkat) dari ' . $memo, 'customers_id' => $value]);
            }
            // $order->points()->attach($points_id, ['amount' => $lev_fee_res, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Sisa komisi Tingkat) dari ' . $memo, 'customers_id' => $com_id]);

            //set cashback member fee
            $cashback_mbr_total = $cashback_mbr + $cashback_mbr2;
            $order->points()->attach($points_id, ['amount' => $cashback_mbr_total, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Penambahan Poin (Cashback Belanja) dari ' . $memo, 'customers_id' => $member->id]);
            // //set profit
            // $profit_row = NetworkFee::select('*')
            //     ->Where('code', '=', 'PRF02')
            //     ->get();
            // //$profit_com = (($profit_row[0]->amount) / 100) * $total;
            // $profit_com = $profit - $lev_fee_total - $cashback_mbr;
            // $order->points()->attach($points_id, ['amount' => $profit_com, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Keuntungan (Profit) dari ' . $memo, 'customers_id' => $com_id]);

            //push notif to agent
            $user_os = Customer::find($agents_id);
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

            //send invoice email
            //get agent email
            $agent = Customer::find($agents_id);
            Mail::to($agent->email)->send(new OrderEmail($order->id, $agents_id));

            return response()->json([
                'success' => true,
                'message' => 'Pembelian Member Berhasil!',
                'email' => $agent->email,
                'order_id' => $order->id,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Saldo Poin Member Tidak Mencukupi.',
            ], 401);
        }
    }

}
