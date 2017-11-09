<?php

class DepartmentDetail extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'id',
        'name'
    ];

    public $timestamps = false;

    public function classes() {
        return $this->hasMany('\ClassMapping');
    }

    public function students() {
        return $this->hasManyThrough('\ClassMapping', '\StudentDetail');
    }

    public function staff() {
        return $this->hasMany('\StaffDetail');
    }

    public function noticeAdmins() {
        return $this->hasMany('\NoticeAdminUser');
    }
}

?>