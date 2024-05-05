<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function index()
    {

        $salaries = Salary::latest()->take(10)->get();

        return view('salary', get_defined_vars());
    }

    public function store(Request $request)
    {
        $salary = new Salary();
        $salary->amount = $request->amount;
        $salary->save();

        return back();
    }

    public function deleteSalary($id)
    {
        $entry = Salary::findOrFail($id);
        $entry->delete();

        return back();
    }

}
