<?php

namespace App\Traits;

trait DeleteChecks {
    function canDelete() {
        $checks = $this->deleteChecks;
        $errors = ['can' => true, 'errors' => []];
        foreach($checks as $check){
            if($this->{$check}()->exists()){
                $errors['can'] = false;
                $errors['errors'][] = $check; 
            }
        }
        return $errors;
    }
}
