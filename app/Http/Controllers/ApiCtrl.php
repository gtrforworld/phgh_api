<?php


namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\BonusTrx;
use App\Models\ManagerTrx;
use App\Models\PhTrx;
use App\Models\Referal;
use App\Models\AirdropUser;
use App\Models\AirdropRef;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;

class ApiCtrl extends Controller
{
    public function __construct()
    {
        $this->ph_roi = 30;
        $this->ref_bonus = 5;
        $this->manager_bonus = 5; //percent
        $this->gh_unlocked = 259200; //second
    }

    public function storeWallet($wallet, $referal, $status = 0, $is_manager = 0) {
        if(!$wallet) return response()->json(['status' => false, 'data' => 'Wallet not found']);
        if(!$referal) return response()->json(['status' => false, 'data' => 'Referal not found']);

        $data = Wallet::where("wallet", $wallet)->first();
        $newWallet = false;
        if(!$data) {
            $data = New Wallet;
            $newWallet = true;
            $data->wallet = $wallet;
            $data->referal = $referal;
        }

        $data->status = $status;
        if($is_manager) $data->is_manager = $is_manager;
        
        $countTotalRef = Wallet::where("referal", $wallet)->count();
        if($countTotalRef > 0) $data->total_referal = $countTotalRef;
        $data->save();

        if($newWallet) {
            $insert = New Referal;
            $insert->referal = $referal;
            $insert->user = $wallet;
            $insert->save();
        }

        $ref = Wallet::where("wallet", $referal)->first();
        if(!$ref) {
            $ref = New Wallet;
            $ref->wallet = $referal;
            $ref->status = 0;
        }
        $countTotalRef = Wallet::where("referal", $referal)->count();
        if($countTotalRef > 0) $ref->total_referal = $countTotalRef;
        $ref->save();

        return true;
    }

    public function storePH(Request $request) {
        try {
            if(!$request->id) return response()->json(['status' => false, 'data' => 'Id not found']);
            if(!$request->wallet) return response()->json(['status' => false, 'data' => 'Wallet not found']);
            if(!$request->referal) return response()->json(['status' => false, 'data' => 'Referal not found']);
            if(!$request->amount) return response()->json(['status' => false, 'data' => 'amount not found']);
    
            $this->storeWallet($request->wallet, $request->referal, 1);

            $checkPH = PhTrx::where("id", $request->id)->first();
            if($checkPH) {
                return response()->json(['status' => false, 'data' => 'PH already exists']);
            }
    
            $data = New PhTrx;
            if($request->hash) $data->hash = $request->hash;
            $data->id = $request->id;
            $data->wallet = $request->wallet;
            $data->referal = $request->referal;
            $data->amount = $request->amount;
            $data->gh_amount = $request->amount + ($request->amount * $this->ph_roi / 100);
            $data->gh_unlocked = date('Y-m-d H:i:s', strtotime('+' . $this->gh_unlocked . ' seconds'));
            $data->save();
    
            $checkWallet = Wallet::where("wallet", $data->referal)->first();
            if($checkWallet) {
                if($checkWallet->total_ph_active > 0 && $checkWallet->status) {
                    // qualified to send bonus ref & manager
    
                    // $bonusRef = New BonusTrx;
                    // $bonusRef->to_wallet = $data->referal;
                    // $bonusRef->from_wallet = $data->wallet;
                    // $bonusRef->ph_amount = $data->amount;
                    // $bonusRef->total_bonus = ($data->amount * $this->ref_bonus / 100);
                    // $bonusRef->save();
    
                    // $walletSponsor = Wallet::where("wallet", $bonusRef->to_wallet)->first();
                    // if($walletSponsor) {
                    //     $walletSponsor->total_bonus_ref = $walletSponsor->total_bonus_ref + $bonusRef->total_bonus;
                    //     $walletSponsor->save();
                    // }
                }
            }
    
            $walletPH = Wallet::where("wallet", $data->wallet)->first();
            if($walletPH) {
                $walletPH->total_ph = $walletPH->total_ph + $data->amount;
                $walletPH->total_gh = $walletPH->total_gh + $data->gh_amount;
                $walletPH->total_ph_active = $walletPH->total_ph_active + 1;
                $walletPH->save();
            }
    
            return response()->json(['status' => true, 'data' => $data]);
        } catch (\Throwable $th) {
            throw $th;
            return response()->json(['status' => false, 'data' => $th]);
        }
    }

    public function getPH(Request $request) {
        if(!$request->id) return response()->json(['status' => false, 'data' => 'Id not found']);
        if(!$request->wallet) return response()->json(['status' => false, 'data' => 'Wallet not found']);

        $ph = PhTrx::where("id", $request->id)->where("wallet", $request->wallet)->first();
        if($ph) {
            $ph->status = 1;
            if($request->hash) $ph->gh_hash = $request->hash;
            $ph->save();

            $walletPH = Wallet::where("wallet", $request->wallet)->first();
            if($walletPH) {
                $walletPH->claimed_gh = $walletPH->claimed_gh + $ph->gh_amount;
                $walletPH->total_ph_active = $walletPH->total_ph_active - 1;
                $walletPH->save();
            }

            // $checkWallet = Wallet::where("wallet", $ph->referal)->first();
            // if($checkWallet) {
            //     if($checkWallet->total_ph_active > 0 && $checkWallet->status && $checkWallet->is_manager) {
            //         // qualified to send bonus manager
            //         $bonusRef = New ManagerTrx;
            //         $bonusRef->to_wallet = $ph->referal;
            //         $bonusRef->from_wallet = $ph->wallet;
            //         $bonusRef->ph_amount = $ph->amount;
            //         $bonusRef->total_bonus = ($ph->amount * $this->manager_bonus / 100);
            //         $bonusRef->save();

            //         $checkWallet->total_bonus_manager = $checkWallet->total_bonus_manager + $bonusRef->total_bonus;
            //         $checkWallet->save();
            //     }
            // }
        }

        return response()->json(['status' => true, 'data' => $ph]);
    }

    public function updateWallet(Request $request) {
        if(!$request->wallet) return response()->json(['status' => false, 'data' => 'wallet not found']);
        $wallet = Wallet::where("wallet", $request->wallet)->first();
        if($wallet) {
            if($request->status) $wallet->status = $request->status;
            if($request->is_manager) $wallet->is_manager = $request->is_manager;
            if($request->total_ph) $wallet->total_ph = $request->total_ph;
            if($request->total_gh) $wallet->total_gh = $request->total_gh;
            if($request->total_bonus_ref) $wallet->total_bonus_ref = $request->total_bonus_ref;
            if($request->total_bonus_manager) $wallet->total_bonus_manager = $request->total_bonus_manager;
            $wallet->save();
        }

        return response()->json(['status' => true, 'data' => $wallet]);
    }

    public function bonusRef(Request $request) {
        if(!$request->wallet) return response()->json(['status' => false, 'data' => 'wallet not found']);
        if(!$request->amount) return response()->json(['status' => false, 'data' => 'amount not found']);
        if(!$request->from) return response()->json(['status' => false, 'data' => 'from not found']);
        if(!$request->bonus) return response()->json(['status' => false, 'data' => 'bonus not found']);

        $data = New BonusTrx;
        $data->to_wallet = $request->wallet;
        $data->ph_amount = $request->amount;
        $data->from_wallet = $request->from;
        $data->total_bonus = $request->bonus;
        $data->hash = $request->hash;
        $data->save();

        $wallet = Wallet::where("wallet", $data->to_wallet)->first();
        if($wallet) {
            $wallet->total_bonus_ref = $wallet->total_bonus_ref + $data->total_bonus;
            $wallet->save();
        }

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function bonusMan(Request $request) {
        if(!$request->wallet) return response()->json(['status' => false, 'data' => 'wallet not found']);
        if(!$request->amount) return response()->json(['status' => false, 'data' => 'amount not found']);
        if(!$request->from) return response()->json(['status' => false, 'data' => 'from not found']);
        if(!$request->bonus) return response()->json(['status' => false, 'data' => 'bonus not found']);

        $data = New ManagerTrx;
        $data->to_wallet = $request->wallet;
        $data->ph_amount = $request->amount;
        $data->from_wallet = $request->from;
        $data->total_bonus = $request->bonus;
        $data->hash = $request->hash;
        $data->save();

        $wallet = Wallet::where("wallet", $data->to_wallet)->first();
        if($wallet) {
            $wallet->total_bonus_manager = $wallet->total_bonus_manager + $data->total_bonus;
            $wallet->save();
        }

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function getWalletInfo(Request $request) {
        $data = Wallet::where("wallet", $request->wallet)->first();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function getBonusTransaction(Request $request) {
        $datas = BonusTrx::where("to_wallet", $request->wallet)->orderBy('id', 'DESC')->paginate(10);
        return response()->json(['status' => true, 'data' => $datas]);
    }

    public function getManagerTransaction(Request $request) {
        $datas = ManagerTrx::where("to_wallet", $request->wallet)->orderBy('id', 'DESC')->paginate(10);
        return response()->json(['status' => true, 'data' => $datas]);
    }

    public function getPHTransaction(Request $request) {
        $datas = PhTrx::where("wallet", $request->wallet)->orderBy('id', 'DESC')->get();
        return response()->json(['status' => true, 'data' => $datas]);
    }

    public function getTopReferal()
    {
        $datas = Wallet::select('wallet as waAddress', 'total_referal', 'total_ph')
                ->where("total_referal", ">=", 0)
                ->orderBy("total_referal", "desc")->limit(10)->get();
        return response()->json(['status' => true, 'data' => $datas]);
    }

    public function registerAirdrop(Request $request)
    {
        try {
            if(!$request->wallet)  return response()->json(['status' => false, 'data' => "Please provide wallet"]);
            if(!$request->referral)  return response()->json(['status' => false, 'data' => "Please provide referral"]);
            if(!$request->signature)  return response()->json(['status' => false, 'data' => "Please provide signature"]);
    
            $address = $request->wallet;
            $checkAddress = AirdropUser::where("address", $address)->first();
    
            $bonusAmount = 0.005;
            $bonusRefAmount = 0.0005;
    
            if(!$checkAddress) {
                $data = New AirdropUser;
                $data->address = $address;
                $data->ref_address = $request->referral;
                $data->hash = $request->signature;
                $data->bonus = $bonusAmount;
                $data->joined_date = date('Y-m-d H:i:s');
                $data->save();
            }
            else{
                if(!$checkAddress->hash) {
                    $checkAddress->ref_address = $request->referral;
                    $checkAddress->hash = $request->signature;
                    $checkAddress->bonus = $bonusAmount;
                    $checkAddress->joined_date = date('Y-m-d H:i:s');
                    $checkAddress->save();
                }
            }
    
            $checkRefIsRegistered = AirdropUser::where("address", $request->referral)->first();
            
            $checkBonusIsFromSameAddress = AirdropRef::where("from_address", $address)->first();
            if(!$checkBonusIsFromSameAddress) {
                $refBonus = New AirdropRef;
                $refBonus->from_address = $address;
                $refBonus->address = $request->referral;
                $refBonus->amount = $bonusRefAmount;
                $refBonus->save();
            }
            
            if(!$checkBonusIsFromSameAddress) {
                if($checkRefIsRegistered) {
                    // send bonus ref to him 
                    $checkRefIsRegistered->bonus_referral = $checkRefIsRegistered->bonus_referral + $bonusRefAmount;
                    $checkRefIsRegistered->save();
                }
                else{
                    // insert new user without ref 
                    $data = New AirdropUser;
                    $data->address = $request->referral;
                    $data->bonus_referral = $bonusRefAmount;
                    $data->save();
                }
            }
            return response()->json(['status' => true, 'data' => true]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['status' => false, 'data' => $th]);
        }
    }

    public function registerAirdropByAddress(Request $request)
    {
        if(!$request->wallet)  return response()->json(['status' => false, 'data' => "Please provide wallet"]);

        $user = AirdropUser::select('address AS a', 'bonus AS b', 'bonus_referral AS r')
            ->where("address", $request->wallet)
            ->where('ref_address', '!=', '')
            ->where('hash', '!=', '')
            ->first();

        if(!$user) {
            return response()->json(['status' => true, 'available' => true]);
        }
        else{
            return response()->json(['status' => true, 'available' => false, 'data' => $user]);
        }
    }
}