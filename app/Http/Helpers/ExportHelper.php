<?php

namespace App\Http\Helpers;

use App\Models\Enemuration;
use Illuminate\Support\Arr;

class ExportHelper {

    public static function get_enum_value($id) {
        if (!isset($id) || !is_int($id)) return "";
        $enum = Enemuration::find($id);
        return isset($enum->value_enum) ? $enum->value_enum : "";
    }
    public static function get_enum_value_array($ids) {
        if (empty($ids) || !is_array($ids)) return "";
        $filtred_ids = Arr::where($ids, function ($value, $key) {
            return is_int($value);
        });
        $enums = Enemuration::whereIn("id_enemuration", $filtred_ids)->pluck("value_enum")->toArray();
        return count($enums) ? implode(", ", $enums) : "";
    }
    public static function to_exportable_array($data, $structure, $prefix = null, $mapping = null) {

        if (isset($prefix) && !is_string($prefix)) $prefix = null;
        if (isset($mapping) && !is_array($mapping)) $mapping = null;
        $array = array();

        foreach ($structure as $input => $type) {

            $value = null;

            if (is_string($type)) {

                if (isset($data[$input])) {

                    switch ($type) {
                        case "value":
                            $value = $data[$input];
                            break;
                        case "enum":
                            $value = self::get_enum_value($data[$input]);
                            break;
                        case "enum_array":
                            $value = self::get_enum_value_array($data[$input]);
                            break;
                        case "ref":
                            $input = $data[$input];
                            if (isset($data[$input])) $value = $data[$input];
                            else $value = "";
                            break;
                    }

                } else $value = "";
                

            } else if (isset($type["type"])) {

                if (($type["type"] == "map" || $type["type"] == "map_array") && !isset($data[$input])) $value = "";

                else if ($type["type"] == "map") {

                    if (is_bool($data[$input])) $data[$input] = (int) $data[$input];

                    if (isset($type["values"]) && is_array($type["values"]) && array_key_exists($data[$input], $type["values"]))
                        $value = $type["values"][$data[$input]];
                    else
                        $value = $data[$input];

                }

                else if ($type["type"] == "map_array") {
                    $data_array = $data[$input];
                    if (!empty($data_array) && is_array($data_array)) {
                        if (isset($type["values"]) && is_array($type["values"]))
                            $value = implode(", ", array_map(fn ($item): string => array_key_exists($item, $type["values"]) ? $type["values"][$item] : $item, $data_array));
                        else $value = implode(", ", $data_array);
                    }
                } 
                
                else if ($type["type"] == "list" && isset($type["prefix"]) && isset($type["structure"]) && is_array($type["structure"])) {
                    if (!isset($data[$input]) || !is_array($data[$input])) $data[$input] = [];
                    $list_prefix = $type["prefix"];
                    $item_structure = $type["structure"];
                    $count = isset($type["count"]) ? $type["count"] : count($data[$input]);
                    for ($index = 1; $index <= $count; $index++) {
                        $item_data = isset($data[$input][$index-1]) ? $data[$input][$index-1] : [];
                        $item_prefix = "$list_prefix-$index-";
                        $array += self::to_exportable_array($item_data, $item_structure, $item_prefix);
                    }
                }
                
                else if ($type["type"] == "child" && isset($type["structure"]) && is_array($type["structure"])) {
                    if (!isset($data[$input])) $data[$input] = [];
                    $child_prefix = isset($type["prefix"]) ? $type["prefix"] : (isset($prefix) ? $prefix : null);
                    $child_mapping = isset($type["mapping"]) ? $type["mapping"] : (isset($mapping) ? $mapping : null);
                    $child_structure = $type["structure"];
                    $array += self::to_exportable_array($data[$input], $child_structure, $child_prefix, $child_mapping);
                }

            }

            if (isset($value)) {
                if (isset($mapping) && array_key_exists($input, $mapping)) $input = $mapping[$input];
                if (isset($prefix)) $input = $prefix.$input;
                if (empty($array[$input])) $array[$input] = $value;
            }

        }

        return $array;
    }
    public static function get_headings($structure, $prefix = null, $mapping = null) {
        if (isset($prefix) && !is_string($prefix)) $prefix = null;
        if (isset($mapping) && !is_array($mapping)) $mapping = null;
        $array = array();

        foreach ($structure as $input => $type) {

            $value = null;

            if (is_string($type)) {

                $value = $input;

            } else if (isset($type["type"])) {

                if (($type["type"] == "map" || $type["type"] == "map_array")) $value = $input;
                
                else if ($type["type"] == "list" && isset($type["prefix"]) && isset($type["structure"]) && is_array($type["structure"])) {
                    $list_prefix = $type["prefix"];
                    $item_mapping = isset($type["mapping"]) ? $type["mapping"] : null;
                    $item_structure = $type["structure"];
                    $count = isset($type["count"]) ? $type["count"] : 0;
                    for ($index = 1; $index <= $count; $index++) {
                        $item_prefix = "$list_prefix-$index-";
                        $list_items = self::get_headings($item_structure, $item_prefix, $item_mapping);
                        foreach ($list_items as $item) array_push($array, $item);
                    }
                }
                
                else if ($type["type"] == "child" && isset($type["structure"]) && is_array($type["structure"])) {
                    $child_prefix = isset($type["prefix"]) ? $type["prefix"] : (isset($prefix) ? $prefix : null);
                    $child_mapping = isset($type["mapping"]) ? $type["mapping"] : (isset($mapping) ? $mapping : null);
                    $child_structure = $type["structure"];
                    $child_items = self::get_headings($child_structure, $child_prefix, $child_mapping);
                    foreach ($child_items as $item) array_push($array, $item);
                }

            }

            if (isset($value)) {
                if (isset($mapping) && array_key_exists($input, $mapping)) $value = $mapping[$value];
                if (isset($prefix)) $value = $prefix.$value;
                if (!in_array($value, $array)) array_push($array, $value);
            }

        }

        return $array;
    }
}