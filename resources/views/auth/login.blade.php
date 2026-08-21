@extends('layouts.app')

@section('title', '管理者ログイン')

@section('content')
    <h1>管理者ログイン</h1>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}">
        @error('email')
            <p class="error">{{ $message }}</p>
        @enderror

        <label for="password">パスワード</label>
        <input type="password" id="password" name="password">
        @error('password')
            <p class="error">{{ $message }}</p>
        @enderror

        <div class="actions">
            <button type="submit">ログイン</button>
        </div>
    </form>
@endsection
