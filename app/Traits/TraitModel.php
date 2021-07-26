<?php

namespace App\Traits;

use App\Action;
use App\Customer;
use App\Category;
use App\Dapertement;
use App\Staff;
use App\Ticket;
use Illuminate\Database\QueryException;

trait TraitModel
{
    public function get_last_code($type)
    {
        if ($type == "action") {
            $action = Action::orderBy('id', 'desc')
                ->first();
            if ($action && (strlen($action->code) == 8)) {
                $code = $action->code;
            } else {
                $code = acc_codedef_generate('ACT', 8);
            }
        }

        if ($type == "customer") {
            $customer = Customer::OrderMaps('id', 'desc')
                ->first();
            if ($customer) {
                $code = $customer->code;
            } else {
                $code = 0;
            }
        }

        if ($type == "category") {
            $category = Category::orderBy('id', 'desc')
                ->first();
            if ($category && (strlen($category->code) == 8)) {
                $code = $category->code;
            } else {
                $code = acc_codedef_generate('CAT', 8);
            }
        }

        if ($type == "dapertement") {
            $dapertement = Dapertement::orderBy('id', 'desc')
                ->first();
            if ($dapertement && (strlen($dapertement->code) == 8)) {
                $code = $dapertement->code;
            } else {
                $code = acc_codedef_generate('DAP', 8);
            }
        }

        if ($type == "staff") {
            $staff = Staff::orderBy('id', 'desc')
                ->first();
            if ($staff && (strlen($staff->code) == 8)) {
                $code = $staff->code;
            } else {
                $code = acc_codedef_generate('STF', 8);
            }
        }

        if ($type == "ticket") {
            $ticket = Ticket::orderBy('id', 'desc')
                ->first();
            if ($ticket && (strlen($ticket->code) == 8)) {
                $code = $ticket->code;
            } else {
                $code = acc_codedef_generate('TIC', 8);
            }
        }

        return $code;
    }

    public function acc_get_last_code($accounts_group_id)
    {
        $account = Account::where('accounts_group_id', $accounts_group_id)
            ->orderBy('code', 'desc')
            ->first();
        if ($account) {
            $code = $account->code;
        } else {
            $accounts_group = AccountsGroup::select('code')->where('id', $accounts_group_id)->first();
            $accounts_group_code = $accounts_group->code;
            $code = acc_codedef_generate($accounts_group_code, 5);
        }

        return $code;
    }

    public function mbr_get_last_code()
    {
        $account = Customer::where('type', 'member')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('MBR', 8);
        }

        return $code;
    }

    public function cst_get_last_code()
    {
        $account = Customer::where('type', '!=', 'member')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('CST', 8);
        }

        return $code;
    }

    public function prd_get_last_code()
    {
        $account = Production::where('type', 'production')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('PRD', 8);
        }

        return $code;
    }

    public function ord_get_last_code()
    {
        $account = Production::where('type', 'sale')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('ORD', 8);
        }

        return $code;
    }

    public function oag_get_last_code()
    {
        $account = Production::where('type', 'agent_sale')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('OAG', 8);
        }

        return $code;
    }

    public function top_get_last_code()
    {
        $account = Production::where('type', 'topup')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('TOP', 8);
        }

        return $code;
    }

    public function get_ref_exc($id, $ref_arr, $lev_max, $id_exc)
    {
        $customer = Customer::find($id);
        $ref_id = $customer->ref_id;
        if ($ref_id > 0 && $lev_max <= 9) {
            $referal = Customer::find($ref_id);
            $ref_status = $referal->status;
            if(($ref_id != $id_exc) && ($ref_status=='active')){
            array_push($ref_arr, $ref_id);
            }
            $lev_max++;
            return $this->get_ref_exc($ref_id, $ref_arr, $lev_max, $id_exc);
        } else {
            return $ref_arr;
        }
    }

    public function get_ref($id, $ref_arr, $lev_max)
    {
        $customer = Customer::find($id);
        $ref_id = $customer->ref_id;
        if ($ref_id > 0 && $lev_max <= 9) {
            $referal = Customer::find($ref_id);
            $ref_status = $referal->status;
            if($ref_status=='active'){
            array_push($ref_arr, $ref_id);
            }
            $lev_max++;
            return $this->get_ref($ref_id, $ref_arr, $lev_max);
        } else {
            return $ref_arr;
        }
    }
}
