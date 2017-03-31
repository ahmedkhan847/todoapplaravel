@extends('layouts.app')
@section('title', 'Home ')
@section('content')
 <div class="row">
      <div class="col-md-9">
    <ul class="list-group">
    @if($todos != false)
          @foreach ($todos as $todo)

    <li class="list-group-item"><a id="view{{$todo->id}}" class="secondary-content" href="{{url('/todo/'.$todo->id)}}"><span class="glyphicon glyphicon-triangle-right"></span></a><a id="edit{{$todo->id}}" class="secondary-content" href="{{url('/todo/'.$todo->id).'/edit'}}"><span class="glyphicon glyphicon-pencil"></span></a><a id="delete{{$todo->id}}" href="#" class="secondary-content" onclick="event.preventDefault();
                                            document.getElementById('delete-form').submit();"><span class="glyphicon glyphicon-trash"></span></a><form id="delete-form" action="{{url('/todo/'.$todo->id)}}" method="POST" style="display: none;">
                         {{ method_field('DELETE') }}{{ csrf_field() }}
                            </form> {{$todo->todo}} 
                                            </li>

@endforeach
@else
<li class="list-group-item"> No Todo added yet <a href="{{ url('/todo/create') }}"> click here</a> to add new todo. </li>
@endif
    </ul>
    </div>

      <div class="col-md-3">
          <img class="img-responsive img-circle" src="{{asset('storage/'.$image)}}">
      </div>
    </div>
@endsection