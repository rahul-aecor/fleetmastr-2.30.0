<?php

namespace App\Presenters;

use Pingpong\Presenters\Presenter;

class VehicleTypePresenter extends Presenter
{
    public function vehicle_category_to_display()
    {
        if (strtolower($this->vehicle_category) == 'hgv') {
            return 'HGV';
        }
        if (strtolower($this->vehicle_category) == 'non-hgv') {
            return 'Non-HGV';
        }
    }

    public function vehicle_sub_category_to_display()
    {
        if (strtolower($this->vehicle_subcategory) == '') {
            return 'None';
        }
        else{
            $all_upper = !preg_match("/[a-z]/", $this->vehicle_subcategory);
            if ($all_upper) {
                return $this->vehicle_subcategory;
            }
            return ucwords(strtolower($this->vehicle_subcategory));
        }
    }

    public function vehicle_type_image_url($type)
    {
        $images = (array) json_decode($this->model_picture);        
        return isset($images[$type]) ? $images[$type] : "";
    }   

    public function displayDimensions()
    {
        $dimensions = "";
        if ($this->length != null) {
            $dimensions = $dimensions." L-".number_format($this->length).";";
        }
        if ($this->width != null) {
            $dimensions = $dimensions." W-".number_format($this->width).";";
        }
        if ($this->height != null) {
            $dimensions = $dimensions." H-".number_format($this->height).";";
        }
        return $dimensions;
    }
}
