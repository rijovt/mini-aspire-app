<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Repayment extends Model
{
    protected $fillable = ['user_id', 'loan_id', 'amount','paid_amount','due_on','status'];
    
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function loan(){
        return $this->belongsTo(Loan::class);
    }
}
