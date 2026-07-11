@extends('errors.layout')

@section('title', 'Page Not Found')
@section('code', '404')

@section('message')
    The page you're looking for doesn't exist or has been moved.
@endsection

@section('actions')
    @auth
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard</a>
    @else
        <a href="{{ url('/') }}" class="btn btn-primary">Go to Home</a>
    @endauth
    <a href="#" onclick="goBack(); return false;" class="btn btn-secondary">Go Back</a>
@endsection
