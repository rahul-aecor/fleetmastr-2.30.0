<?php
/**
 * @file
 * JqGrid JSON Encoder.
 *
 * All LaravelJqGrid code is copyright by the original authors and released under the MIT License.
 * See LICENSE.
 */

namespace App\Custom\Encoder;

// use Mgallegos\LaravelJqgrid\Encoders\RequestedDataInterface;
// use Mgallegos\LaravelJqgrid\Repositories\RepositoryInterface;
use App\Custom\Encoder\RequestedDataInterface;
use App\Custom\Repositories\RepositoryInterface;
use Maatwebsite\Excel\Excel;
use Exception;

class JqGridJsonEncoder implements RequestedDataInterface {

    /**
    * Maatwebsite\Excel\Excel
    * @var Excel
    */
    protected $Excel;

    /**
    * Construct Excel
    * @param  Maatwebsite\Excel\Excel $Excel
    */
    public function __construct(Excel $Excel)
    {
            $this->Excel = $Excel;
    }

    /**
     * Echo in a jqGrid compatible format the data requested by a grid.
     *
     * @param RepositoryInterface $dataRepository
     *  An implementation of the RepositoryInterface
     * @param  array $postedData
     *  All jqGrid posted data
     * @return string
     *  String of a jqGrid compatible data format: xml, json, jsonp, array, xmlstring, jsonstring.
     */
    public function encodeRequestedData(RepositoryInterface $Repository,  $postedData)
    {
        // $page = $postedData['page']; // get the requested page
        // $limit = $postedData['rows']; // get how many rows we want to have into the grid
        // $sidx = $postedData['sidx']; // get index row - i.e. user click to sort
        // $sord = $postedData['sord']; // get the direction
        if(isset($postedData['page']))
        {
            $page = $postedData['page']; // get the requested page
        }
        else
        {
            $page = 1;
        }

        if(isset($postedData['rows']))
        {
            $limit = $postedData['rows']; // get how many rows we want to have into the grid
        }
        else
        {
            $limit = null;
        }

        if(isset($postedData['sidx']))
        {
            $sidx = $postedData['sidx']; // get index row - i.e. user click to sort
        }
        else
        {
            $sidx = null;
        }

        if(isset($postedData['sord']))
        {
            $sord = $postedData['sord']; // get the direction
        }

        if(isset($postedData['filters']) && !empty($postedData['filters']))
        {
            $filters = json_decode(str_replace('\'','"',$postedData['filters']), true);
        }

        if(!$sidx || empty($sidx))
        {
            $sidx = null;
            $sord = null;
        }

        if(isset($filters['rules']) && is_array($filters['rules']))
        {
            foreach ($filters['rules'] as &$filter)
            {
                switch ($filter['op'])
                {
                    case 'eq': //equal
                        $filter['op'] = '=';
                        break;
                    case 'ne': //not equal
                        $filter['op'] = '!=';
                        break;
                    case 'lt': //less
                        $filter['op'] = '<';
                        break;
                    case 'le': //less or equal
                        $filter['op'] = '<=';
                        break;
                    case 'gt': //greater
                        $filter['op'] = '>';
                        break;
                    case 'ge': //greater or equal
                        $filter['op'] = '>=';
                        break;
                    case 'bw': //begins with
                        $filter['op'] = 'like';
                        $filter['data'] = $filter['data'] . '%';
                        break;
                    case 'bn': //does not begin with
                        $filter['op'] = 'not like';
                        $filter['data'] = $filter['data'] . '%';
                        break;
                    case 'in': //is in
                        $filter['op'] = 'is in';
                        break;
                    case 'ni': //is not in
                        $filter['op'] = 'is not in';
                        break;
                    case 'ew': //ends with
                        $filter['op'] = 'like';
                        $filter['data'] = '%' . $filter['data'];
                        break;
                    case 'en': //does not end with
                        $filter['op'] = 'not like';
                        $filter['data'] = '%' . $filter['data'];
                        break;
                    case 'cn': //contains
                        $filter['op'] = 'like';
                        $filter['data'] = '%' . $filter['data'] . '%';
                        break;
                    case 'nc': //does not contains
                        $filter['op'] = 'not like';
                        $filter['data'] = '%' . $filter['data'] . '%';
                        break;
                    case 'nn': //is not null
                        $filter['op'] = 'is not null';
                        break;
                    case 'nu': //is null
                        $filter['op'] = 'is null';
                        break;
                }
            }
        }
        else
        {
            $filters['rules'] = array();
        }

        if(! isset($filters['groupOp'])) {
            $filters['groupOp'] = 'AND';
        }
        $groupBy = isset($filters['groupBy']) ? $filters['groupBy'] : null;
        $count = $Repository->getTotalNumberOfRows($filters['rules'], $filters['groupOp'], $groupBy);

        if(empty($limit))
        {
            $limit = $count;
        }

        if(!is_int($count))
        {
            throw new Exception('The method getTotalNumberOfRows must return an integer');
        }

        if( $count > 0 )
        {
            $totalPages = ceil($count/$limit);
        }
        else
        {
            $totalPages = 0;
        }

        if ($page > $totalPages)
        {
            $page = $totalPages;
        }

        if ($limit < 0 )
        {
            $limit = 0;
        }

        $start = $limit * $page - $limit;

        if ($start < 0)
        {
            $start = 0;
        }
        
        //$limit = $limit * $page;
        //$limit = $limit * $totalPages; 
        if(empty($postedData['pivotRows']))
        {
            // $rows = $Repository->getRows($limit, $start, $sidx, $sord, $filters['rules'], null, null, false, $filters['groupOp'], $groupBy);
            $rows = $Repository->getRows($limit, $start, $sidx, $sord, $filters['rules'], null, null, false, $filters['groupOp'], $groupBy);
        }
        else
        {
            $rows = json_decode($postedData['pivotRows'], true);
        }

        if(!is_array($rows) || (isset($rows[0]) && !is_array($rows[0])))
        {
            throw new Exception('The method getRows must return an array of arrays, example: array(array("column1"  =>  "1-1", "column2" => "1-2"), array("column1" => "2-1", "column2" => "2-2"))');
        }

        if(isset($postedData['exportFormat']))
        {
            if($postedData['name'] == 'user'){

                $rows = array_map(function($row){
                    if(str_contains($row['email'],'-imastr.com')){
                        $row['email'] = '';
                    }
                    if($row['username'] == ''){
                        $row['username'] = $row['email'];
                    }
                    return $row;
                }, $rows);
            }
            $this->Excel->create($postedData['name'], function($Excel) use ($rows, $postedData)
            {
                foreach (json_decode($postedData['fileProperties'], true) as $key => $value)
                {
                    $method = 'set' . ucfirst($key);

                    $Excel->$method($value);
                }

                $Excel->sheet($postedData['name'], function($Sheet) use ($rows, $postedData)
                {
                    $columnCounter = 0;
                    $rows_empty = [];

                    foreach (json_decode($postedData['model'], true) as $a => $model)
                    {
                        if(isset($model['hidden']) && $model['hidden'] !== true)
                        {
                            $columnCounter++;
                        }

                        /*if(isset($model['hidedlg']) && $model['hidedlg'] === true)
                        {
                            continue;
                        }*/

                        if(empty($postedData['pivot']))
                        {
                            if (count($rows) === 0) {
                                if(!isset($model['hidden']) || !$model['hidden'] === true) {
                                    if(isset($model['label'])) {
                                        $rows_empty = array_add($rows_empty, $model['label'], '');
                                    }
                                    else {
                                        $rows_empty = array_add($rows_empty, $model['name'], '');
                                    }    
                                }
                            }
                            else {
                                foreach ($rows as $b => &$row)
                                {
                                    if(isset($model['hidden']) && $model['hidden'] === true)
                                    {
                                        unset($row[$model['name']]);
                                    }
                                    else
                                    {
                                        if(isset($model['label']))
                                        {
                                            // if(isset($row[$model['name']])) {
                                                $row = array_add($row, $model['label'], $row[$model['name']]);
                                            // }
                                            unset($row[$model['name']]);
                                        }
                                        else
                                        {
                                            $temp = $row[$model['name']];
                                            unset($row[$model['name']]);
                                            $row = array_add($row, $model['name'], $temp);
                                        }
                                    }
                                }
                            }
                        }

                        if(isset($model['align']) && isset($model['hidden']) && $model['hidden'] !== true)
                        {
                            $Sheet->getStyle($this->num_to_letter($columnCounter, true))->getAlignment()->applyFromArray(
                                    array('horizontal' => $model['align'])
                            );
                        }
                    }

                    foreach (json_decode($postedData['sheetProperties'], true) as $key => $value)
                    {
                        $method = 'set' . ucfirst($key);

                        $Sheet->$method($value);
                    }
                    
                    if (count($rows) === 0) {
                        $rows = [$rows_empty];
                    }

                    $Sheet->fromArray($rows);

                    $Sheet->row(1, function($Row) {
                      $Row->setFontWeight('bold');
                    });
                });
            })->export($postedData['exportFormat']);
        }
        else
        {
                echo json_encode(array('page' => $page, 'total' => $totalPages, 'records' => $count, 'rows' => $rows));
        }
    }

    static function toExcel($lableArray, $dataArray, $otherParams,$output = 'xlsx',$download='yes',$columnFormat=''){
        $excelCreateObj = \Excel::create(str_slug($otherParams['sheetTitle']), function($excel) use($lableArray, $dataArray, $otherParams,$columnFormat) {
            $excel->setTitle($otherParams['sheetTitle']);
            $excel->sheet($otherParams['sheetName'], function($sheet) use($lableArray, $dataArray, $otherParams,$columnFormat) {
                $sheet->row(1, $lableArray);
                $sheet->row(1, function($row){

                    $row->setBackground("#1f844c");
                    $row->setFontColor('#ffffff');
                    $row->setFontWeight('bold');
                    $row->setFontFamily('Arial');
                    $row->setFontSize(10);
                });
                 if($columnFormat!=''){
                $sheet->setColumnFormat($columnFormat); 
                 }
                $row_no = 2;
                foreach ($dataArray as $data) {
                     
                    $sheet->row($row_no, $data);
                    $row_no++;
                }
                
                if($otherParams['boldLastRow']){
                    $sheet->row(($row_no-1), function($row){
                        $row->setFontWeight('bold');
                    });
                }
            });
        });
        if($download == 'yes'){
         $excelCreateObj->export($output);   
        }else{
           $excelCreateObj->store($output);  
        }

    }

    /**
    * Takes a number and converts it to a-z,aa-zz,aaa-zzz, etc with uppercase option
    *
    * @access   public
    * @param    int number to convert
    * @param    bool    upper case the letter on return?
    * @return   string  letters from number input
    */
    protected function num_to_letter($num, $uppercase = FALSE)
    {
        $num -= 1;

        $letter =   chr(($num % 26) + 97);
        $letter .=  (floor($num/26) > 0) ? str_repeat($letter, floor($num/26)) : '';
        return      ($uppercase ? strtoupper($letter) : $letter);
    }
}
