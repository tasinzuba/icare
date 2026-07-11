@extends('errors.layout')

@section('title', 'Under Maintenance')
@section('code', '503')

@section('message')
    We're currently performing maintenance. Please check back soon.
@endsection

@section('actions')
    <a href="javascript:window.location.reload()" class="btn btn-primary">Check Status</a>
@endsection
