<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TodoRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Todo;
use App\User;

class ApiController extends Controller
{
    public function accessToken(Request $request)
    {
        $validate = $this->validations($request,"login");
        if($validate["error"]){
            return $this->prepareResult(false, [], $validate['errors'],"Error while validating user"); 
        }
        $user = User::where("email",$request->email)->first();
        if($user){
            if (Hash::check($request->password,$user->password)) {
                return $this->prepareResult(true, ["accessToken" => $user->createToken('Todo App')->accessToken], [],"User Verified");
            }else{
                return $this->prepareResult(false, [], ["password" => "Wrong passowrd"],"Password not matched");  
            }
        }else{
            return $this->prepareResult(false, [], ["email" => "Unable to find user"],"User not found");
        }
        
    }

    /**
     * Get a validator for an incoming Todo request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $type
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validations($request,$type){
        $errors = [];
        $error = false;
        if($type == "login"){
            $validator = Validator::make($request->all(),[
            'email' => 'required|email|max:255',
            'password' => 'required',
            ]);
            if($validator->fails()){
                $error = true;
                $errors = $validator->errors();
            }
        }elseif($type == "create todo"){
            $validator = Validator::make($request->all(),[
                'todo' => 'required',
                'description' => 'required',
                'category' => 'required'
            ]);
            if($validator->fails()){
                $error = true;
                $errors = $validator->errors();
            }
        }elseif($type == "update todo"){
            $validator = Validator::make($request->all(),[
                'todo' => 'filled',
                'description' => 'filled',
                'category' => 'filled'
            ]);
            if($validator->fails()){
                $error = true;
                $errors = $validator->errors();
            }
        }
        return ["error" => $error,"errors"=>$errors];
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
        $error = $this->validations($request,"create todo");
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
           $error = $this->validations($request,"update todo");
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
                return $this->prepareResult(true, [], [],"Todo deleted");
            }
        }else{
            return $this->prepareResult(false, [], "unauthorized","You are not authenticated to delete this todo");
        }        
    }
}
