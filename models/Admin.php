<?php

class AdminUser extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'id',
        'email',
        'password',
        'u_type'
    ];

    public $timestamps = false;
}

?>