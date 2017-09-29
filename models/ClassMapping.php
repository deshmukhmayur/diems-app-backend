<?php

class ClassMapping extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'department_detail_id',
        'class',
        'division'
    ];

    public $timestamps = false;

    public function students() {
        return $this->hasMany('\StudentDetail');
    }

    public function dept() {
        return $this->belongsTo('\DepartmentDetail', 'department_detail_id');
    }

    // public function staffMapping() {
    //     return $this->belongsTo('\ClassTeacherMapping');
    // }
}

?>