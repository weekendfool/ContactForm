@extends('layouts.app')

@section('title', 'お問い合わせフォーム')

@section('content')
    <h1>お問い合わせフォーム</h1>

    <form method="POST" action="{{ route('contact.confirm') }}">
        @csrf

        <label for="name">お名前</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}">
        @error('name')
            <p class="error">{{ $message }}</p>
        @enderror

        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}">
        @error('email')
            <p class="error">{{ $message }}</p>
        @enderror

        <label for="subject">件名</label>
        <input type="text" id="subject" name="subject" value="{{ old('subject') }}">
        @error('subject')
            <p class="error">{{ $message }}</p>
        @enderror

        <label for="message">お問い合わせ内容</label>
        <textarea id="message" name="message">{{ old('message') }}</textarea>
        @error('message')
            <p class="error">{{ $message }}</p>
        @enderror

        <div class="actions">
            <button type="submit">確認画面へ</button>
        </div>
    </form>
@endsection
