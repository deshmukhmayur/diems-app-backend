<?php

class NoticeDetail extends \Illuminate\Database\Eloquent\Model {
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'img_url',
        'end_date',
        'branch',
        'class',
        'division',
        'audience',
        'u_id',
        'created_at',
        'updated_at',
    ];

    protected $dates = ['created_at', 'updated_at', 'end_date', 'deleted_at'];
}

?>