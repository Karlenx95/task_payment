<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class SecondGateController extends Controller
{
    public function gateCallback(Request $request){

        $validator = Validator::make($request->all(),[
            'project' => 'required|integer',
            'invoice' => 'required|integer',
            'status' => 'required|string',
            'amount' => 'required|integer',
            'amount_paid' => 'required|integer',
            'rand' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response('Invalid data', 400);
        }

        $data = [
            "project"=>$request["project"],
            "invoice"=>$request["invoice"],
            "status"=>$request["status"],
            "amount"=>$request["amount"],
            "amount_paid"=>$request["amount_paid"],
            "rand" => $request["rand"],
            "timestamp"=>Carbon::now(),
        ];

        $payment = Payment::where('project', $data['project'])
            ->where('invoice', $data['invoice'])
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

            $signature = implode(".",$data).".".env('APP_KEY');
            $evalSignature = hash("MD5",$signature);

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
                "project"=>$request["project"],
                "invoice"=>$request["invoice"],
                "status"=>$request["status"],
                "amount"=>$request["amount"],
                "amount_paid"=>$request["amount_paid"],
                "provider"=>"GATE_2",
                'sign' => $evalSignature,
                'limit'=>env("GATE_2_LIMIT"),
            ]);

            return response('CREATED');
        }
    }
}
