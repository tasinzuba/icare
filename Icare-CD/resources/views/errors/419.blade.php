@extends('errors.layout')

@section('title', 'Session Expired')
@section('code', '419')

@section('message')
    Your session has expired. Please refresh the page and try again.
@endsection

@section('actions')
    <a href="javascript:window.location.reload()" class="btn btn-primary">Refresh Page</a>
    @auth
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Dashboard</a>
    @else
        <a href="{{ url('/') }}" class="btn btn-secondary">Go to Home</a>
    @endauth
@endsection
