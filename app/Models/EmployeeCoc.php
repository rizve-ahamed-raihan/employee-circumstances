<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCoc extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'old_value',
        'new_value',
        'reason',
        'status',
        'effective_date',
        'approved_by',
    ];

    protected $casts = [
        'effective_date' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
