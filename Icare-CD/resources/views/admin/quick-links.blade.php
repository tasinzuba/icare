@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Quick Admin Links</h1>
    
    <div class="card">
        <div class="card-body">
            <h3>Test Management</h3>
            <ul>
                <li><a href="{{ route('admin.test-categories.index') }}">Test Categories</a></li>
                <li><a href="{{ route('admin.test-sets.index') }}">Test Sets</a></li>
                <li><a href="{{ route('admin.questions.index') }}">Questions</a></li>
            </ul>
        </div>
    </div>
</div>
@endsection
