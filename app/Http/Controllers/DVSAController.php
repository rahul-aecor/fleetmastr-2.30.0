<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use View;
use Auth;
use Input;
use JavaScript;
use App\Models\Vehicle;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\DVSARepository;
use App\Custom\Facades\GridEncoder;

class DVSAController extends Controller
{
    public $title= 'Earned Recognition';

    public function __construct()
    {
        View::share('title', $this->title);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('dvsa.index');
    }
}
