<?php

class Feedback extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'subject',
        'message',
        'user_id',
        'user_type',
        'created_at',
        'updated_at'
    ];

    protected $dates = [ 'created_at', 'updated_at' ];
}