<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = ['user_id', 'amount', 'term', 'status'];
    
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function repayment(){
        return $this->hasMany(Repayment::class);
    }

    public function loanBalance(){
        $bal = $this->amount - $this->repayment()->sum('paid_amount');
        return $bal;
    }
}
