<?php

class SubjectDetail extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = [
        'id',
        'name'
    ];

    public $timestamps = false;

    public function getClassTest() {
        return $this->hasMany('\ClassTestDetail');
    }

    public function marks() {
        return $this->hasMany('\ClassTestDetail');
    }

    public function subTeacherMapping() {
        return $this->hasMany('\SubjectTeacherMapping');
    }
}

?>