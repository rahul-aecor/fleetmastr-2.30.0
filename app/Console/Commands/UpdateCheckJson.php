<?php

namespace App\Console\Commands;

use StdClass;
use Storage;
use Illuminate\Console\Command;

class UpdateCheckJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:checkjson';

    protected $inputFile;

    protected $outputPath;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command updates the existing check json as per the new structure.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        // $this->inputFile = storage_path('json/vc_json_sample.tsv');
        $this->inputFile = storage_path('json/vc_json.tsv');
        $this->outputPath = storage_path('json/output/');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->initFiles();
    }

    protected function initFiles()
    {
        $this->info('Trying to read input file at...');
        $this->info($this->inputFile);
        $handle = fopen($this->inputFile, "r");
        $output_fp = fopen($this->outputPath . 'output_queries.sql', 'w');
        if ($handle) {
            $recordCount = 0;
            while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
                $recordCount++;
                // FOR TESTING process only 20 records
                // if ($recordCount == 20) {
                //     break;
                // }
                // SKIP header record
                if ($recordCount == 1) {
                    continue;
                }
                if (count($data) === 3) {
                    $this->processRecord($data, $output_fp);
                }
                else {
                    $this->error('Skipping invalid record found at ' . $recordCount);
                }
            }
            fclose($handle);
            fclose($output_fp);
            $this->info('Total number of lines processed: ' . $recordCount);
        } 
        else {
            $this->error('Error while reading file.');
        } 
    }

    protected function processRecord($data, $output_fp)
    {
        $this->info('Processing for id ' . $data[0]);        
        $currentJson = json_decode($data[2]);        
        if (! is_object($currentJson)) {
            $this->error('No valid json found for id ' . $data[0] . '. Skipping...');
            return;
        }
        $processedJson = new StdClass;
        $processedJson->status = $currentJson->status;
        $processedJson->screens = new StdClass;
        $processedJson->screens->screen = [];
        $processedJson->total_defect = $currentJson->total_defect;   

        foreach ($currentJson->screens->screen as $screen) {

            $processedScreen = new StdClass;
            $processedScreen->_number = $screen->_number;
            $processedScreen->_type = $screen->_type;
            $processedScreen->answer = $screen->answer;
            $processedScreen->regno = $screen->regno;            
            $processedScreen->defect_count = 0;
            $processedScreen->prohibitional_defect_count = 0;
            $processedScreen->defects = new StdClass;
            $processedScreen->defects->defect = [];
            $processedScreen->options = new StdClass;
            $processedScreen->options->optionList = [];

            if ($screen->_type == "yesno") {                
                foreach ($screen->defects->defect as $defect) {
                    $processedDefect = new StdClass;                    
                    // $defect = $this->processDefect($defect);
                    // Set defect related data to be saved in the check
                    $processedDefect->id = $defect->id;
                    $processedDefect->imageString = $defect->imageString;
                    $processedDefect->image_exif = $defect->image_exif;
                    // $processedDefect->safety_notes = $defect->safety_notes;
                    $processedDefect->selected = $defect->selected;
                    if (isset($defect->defect_id)) {
                        $processedDefect->defect_id = $defect->defect_id;
                    }
                    $processedScreen->prohibitional_defect_count = $screen->prohibitional_defect_count;
                    $processedScreen->defect_count = $screen->defect_count;
                    // Push processed defect to the processed screen object
                    array_push($processedScreen->defects->defect, $processedDefect);
                } 
            }
            if ($screen->_type == "list") {
                foreach ($screen->options->optionList as $option) {
                    
                    $processedOption = new StdClass;
                    // $processedOption->text = $option->text;
                    $processedOption->defects = new StdClass;
                    $processedOption->defects->defect = [];
                    $processedOption->defect_count = 0;
                    $processedOption->prohibitional_defect_count = 0;
                    
                    foreach ($option->defects->defect as $defect) {
                        $processedOptionListDefect = new StdClass;
                        // $defect = $this->processDefect($defect);
                        $processedOptionListDefect->id = $defect->id;
                        $processedOptionListDefect->imageString = $defect->imageString;
                        $processedOptionListDefect->image_exif = $defect->image_exif;
                        // $processedOptionListDefect->safety_notes = $defect->safety_notes;
                        $processedOptionListDefect->selected = $defect->selected;
                        if (isset($defect->defect_id)) {
                            $processedOptionListDefect->defect_id = $defect->defect_id;
                        }                      
                        // Push processed defect to the processed screen object
                        array_push($processedOption->defects->defect, $processedOptionListDefect);
                    } 
                    $processedOption->prohibitional_defect_count = $option->prohibitional_defect_count;
                    $processedOption->defect_count = $option->defect_count;
                    \Log::info('updating ');
                    array_push($processedScreen->options->optionList, $processedOption);   
                }
            }
            if ($screen->_type == "multiselect") {
                foreach ($screen->options->optionList as $option) {
                    
                    $processedOption = new StdClass;
                    $processedOption->text = $option->text;
                    $processedOption->answer = $option->answer;
                    array_push($processedScreen->options->optionList, $processedOption);   
                }
            }
            // Push processed screen to processedJson object
            array_push($processedJson->screens->screen, $processedScreen);
        }

        $j = json_encode($processedJson);        
        fwrite($output_fp, "UPDATE checks SET new_json = '{$j}' WHERE id = {$data[0]};" . PHP_EOL);
    }
}
