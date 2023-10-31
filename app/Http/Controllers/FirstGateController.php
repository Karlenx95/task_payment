<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FirstGateController extends Controller
{
    public function gateCallback(Request $request){

        $validator = Validator::make($request->all(),[
            'merchant_id' => 'required|integer',
            'payment_id' => 'required|integer',
            'status' => 'required|string',
            'amount' => 'required|integer',
            'amount_paid' => 'required|integer',
            'sign' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response('Invalid data', 400);
        }

        $data = [
            "merchant_id"=>$request["merchant_id"],
            "payment_id"=>$request["payment_id"],
            "status"=>$request["status"],
            "amount"=>$request["amount"],
            "amount_paid"=>$request["amount_paid"],
        ];

        $payment = Payment::where('merchant_id', $data['merchant_id'])
            ->where('payment_id', $data['payment_id'])
            ->first();

        if ($payment) {
            $payment->limit -= $data['amount_paid'];
            if($payment->limit >= 0){
                $payment->update([
                    'status' => $data['status'],
                    'limit' => $payment->limit,
                ]);

                return response('UPDATED');
            }else{
                return response('You have limit expired, transaction will be done after 2 days', 200);
            }
        } else {
            ksort($data);

            $signature = implode(":",$data).":".env('MERCHANT_KEY');
            $evalSignature = hash("sha256",$signature);

            $user = User::where('name',$request['name'])
                ->where('email',$request['email'])
                ->first();

            if(!$user){
                $user = User::create([
                    'name'=>$request['name'],
                    'email'=>$request['email']
                ]);
            }

            Payment::create([
                'user_id'=>$user->id,
                "merchant_id"=>$request["merchant_id"],
                "payment_id"=>$request["payment_id"],
                "status"=>PaymentStatus::CREATED,
                "amount"=>$request["amount"],
                "amount_paid"=>$request["amount_paid"],
                'sign' => $evalSignature,
                'provider' => "GATE_1",
                'limit'=>env("GATE_1_LIMIT"),
            ]);

            return response('CREATED');
        }
    }
}
