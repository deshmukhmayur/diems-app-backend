<?php

class StaffDetail extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'name',
        'department_detail_id',
        'mob_no',
        'email',
        'password'
    ];

    public function dept() {
        return $this->belongsTo('\DepartmentDetail');
    }
}

?>