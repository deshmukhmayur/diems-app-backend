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
        return $this->belongsTo('\DepartmentDetail', 'department_detail_id');
    }

    public function class() {
        return $this->hasOne('\ClassTeacherMapping');
    }

    public function mentees() {
        return $this->hasOne('\MentorBatchMapping');
    }
}

?>