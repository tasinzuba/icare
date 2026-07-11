@extends('errors.layout')

@section('title', 'Access Denied')
@section('code', '403')

@section('message')
    You don't have permission to access this page.
@endsection

@section('actions')
    @auth
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard</a>
    @else
        <a href="{{ url('/') }}" class="btn btn-primary">Go to Home</a>
        <a href="{{ route('login') }}" class="btn btn-secondary">Sign In</a>
    @endauth
@endsection
