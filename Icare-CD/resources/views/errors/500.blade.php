@extends('errors.layout')

@section('title', 'Server Error')
@section('code', '500')

@section('message')
    Something went wrong on our end. Please try again later.
@endsection

@section('actions')
    <a href="javascript:window.location.reload()" class="btn btn-primary">Try Again</a>
    @auth
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Dashboard</a>
    @else
        <a href="{{ url('/') }}" class="btn btn-secondary">Go to Home</a>
    @endauth
@endsection
