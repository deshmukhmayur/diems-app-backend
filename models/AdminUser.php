<?php

class AdminUser extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'email',
        'password',
        'type'
    ];

    public $timestamps = false;
}

?>