<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_amount',
        'amount',
        'recurring',
        'frequency',
        'copied',
        'expense_date',
        'split',
        'split_percentage',
        'split_amount',
        'payee_id',
        'user_id',
        'category_id',
        'payee_paid',
        'paid_at',
        'reminder_sent_user',
        'reminder_sent_payee'
    ];

    protected $casts = [
        'name' => 'string',
        'original_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'recurring' => 'bool',
        'copied' => 'bool',
        'split' => 'bool',
        'frequency' => 'string',
        'expense_date' => 'datetime',
        'user_id' => 'int',
        'category_id' => 'int',
        'payee_paid' => 'bool',
        'paid_at' => 'datetime',
        'reminder_sent_user' => 'datetime',
        'reminder_sent_payee' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payee()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'expenses_tags');
    }
}
