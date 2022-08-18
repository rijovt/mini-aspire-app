<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Loan;
use App\Http\Resources\LoanResource;
use App\Repayment;
use Carbon\Carbon;
use Auth;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //check if admin or customer to view loans
        if (Auth::user()->role !== 1)
            $loans = Auth::user()->loans()->get();
        else
            $loans = Loan::paginate();

        return LoanResource::collection($loans);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'term' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $loan = Loan::create([
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'term' => $request->term
        ]);
        if($loan){
            //create repaymet shedules
            $date = Carbon::now();
            $emi = round($request->amount/$request->term,5);
            $freq = 7;
            $i = 1; $emi_tot=0;
            while($i <= $request->term) {
                if($i == $request->term)
                    $emi = round($request->amount-$emi_tot,5);
                $date = $date->addDays($freq);
                Repayment::create(
                    [
                        'user_id' => $request->user()->id,
                        'loan_id' => $loan->id,
                        'amount' => $emi,
                        'due_on' => $date
                    ]
                );
                $emi_tot+=$emi;
                $i++;
            } 
        }

        return new LoanResource($loan);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function show(Loan $loan)
    {
        $user = Auth::user();
        //policy check to view the loan
        if ($user->can('view', $loan)) { 
            return new LoanResource($loan);
        } else {
            return response()->json(['message' => 'Unauthorized to view the Loan.'], 403);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function edit(Loan $loan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Loan $loan)
    {
        // check if user is admin or not
        if ($request->user()->role !== 1){
            return response()->json(['message' => 'Admins only can change loan staus.'], 403);
        }

        if ($loan->status != 'Pending'){
            return response()->json(['message' => 'Your loan status is not Pending.'], 403);
        }
        
        $loan->status = 'Approved';
        $loan->save();

        return new LoanResource($loan);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Loan $loan)
    {
        //
    }
}
