<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TodoRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Todo;

class ApiController extends Controller
{
    public function accessToken(Request $request)
    {
        $this->validate($request, [
        'email' => 'required',
        'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Authentication passed...

            return ["accessToken" => Auth::user()->createToken('Todo App')->accessToken];
        }
    }

    /**
     * Get a validator for an incoming Todo request.
     *
     * @param  array  $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator($request,$update = false)
    {
        $error = false;
        $errors = [];
        if($update){
            if(!isset($request['todo']) && !isset($request['description']) && !isset($request['category'])){
                $errors = "nothing to update";
            }

            if(isset($request['todo'])){
                if (empty($request['todo']) || $request['todo'] == '') {
                    $error = true;
                    array_push($errors, ["todo" => "Cannot be empty or less than 10"]);
                    
                }
            }

            if(isset($request['description'])){
                if (empty($request['description']) || $request['description'] == '') {
                    $error = true;
                    array_push($errors, ["description" => "Cannot be empty or less than 10"]);
                    
                }
            }

            if(isset($request['category'])){
                if (empty($request['category']) || $request['category'] == '') {
                    $error = true;
                    array_push($errors, ["category" => "Required"]);
                    
                }
            }
            
        }else{

            if (empty($request['todo']) || !isset($request['todo']) || $request['todo'] == '') {
                $error = true;
                array_push($errors, ["todo" => "Cannot be empty or less than 10"]);
            }

            if (empty($request['description']) || !isset($request['description']) || $request['description'] == '') {
                $error = true;
                array_push($errors, ["description" => "Cannot be empty or less than 10"]);
            }
            
            if (empty($request['category']) || !isset($request['category']) || $request['category'] == '') {
                array_push($errors, ["category" => "Required"]); 
                $error = true;      
            }
        }
        
        return [
            'error' => $error,
            'errors' => $errors
            ];
    
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function prepareResult($status, $data, $errors,$msg)
    {
        return ['status' => $status,'data'=> $data,'message' => $msg,'errors' => $errors];
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        return $this->prepareResult(true, $request->user()->todo()->get(), [],"All user todos");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,Todo $todo)
    {
        if($todo->user_id == $request->user()->id){
            return $this->prepareResult(true, $todo, [],"All results fetched");
        }else{
            return $this->prepareResult(false, [], "unauthorized","You are not authenticated to view this todo");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $error = $this->validator($request->all());
        if ($error['error']) {
            return $this->prepareResult(false, [], $error['errors'],"Error in creating todo");
        } else {
            $todo = $request->user()->todo()->Create($request->all());
            return $this->prepareResult(true, $todo, $error['errors'],"Todo created");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Todo $todo)
    {
        if($todo->user_id == $request->user()->id){
            $error =  $this->validator($request->all(),true);
            if ($error['error']) {
                return $this->prepareResult(false, [], $error['errors'],"error in updating data");
            } else {
                $todo = $todo->fill($request->all())->save();
                return $this->prepareResult(true, $todo, $error['errors'],"updating data");
            }
        }else{
            return $this->prepareResult(false, [], "unauthorized","You are not authenticated to edit this todo");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo)
    {
        if($todo->user_id == $request->user()->id){
            if ($todo->delete()) {
                return $this->prepareResult(true, [], []);
            }
        }else{
            return $this->prepareResult(false, [], "unauthorized","You are not authenticated to delete this todo");
        }        
    }
}
