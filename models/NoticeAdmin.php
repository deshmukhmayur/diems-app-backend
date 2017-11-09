<?php

class NoticeAdminUser extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'id',
        'email',
        'password',
        'type',
        'department_detail_id'
    ];

    public $timestamps = false;

    public function dept() {
        return $this->belongsTo('\DepartmentDetail', 'department_detail_id');
    }
}

?>