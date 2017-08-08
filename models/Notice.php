<?php

class NoticeDetail extends \Illuminate\Database\Eloquent\Model {
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
}

?>