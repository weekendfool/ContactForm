@extends('layouts.app')

@section('title', '入力内容の確認')

@section('content')
    <h1>入力内容の確認</h1>

    <dl>
        <dt>お名前</dt>
        <dd>{{ $input['name'] }}</dd>

        <dt>メールアドレス</dt>
        <dd>{{ $input['email'] }}</dd>

        <dt>件名</dt>
        <dd>{{ $input['subject'] }}</dd>

        <dt>お問い合わせ内容</dt>
        <dd>{{ $input['message'] }}</dd>
    </dl>

    <form method="POST" action="{{ route('contact.store') }}">
        @csrf
        <input type="hidden" name="name" value="{{ $input['name'] }}">
        <input type="hidden" name="email" value="{{ $input['email'] }}">
        <input type="hidden" name="subject" value="{{ $input['subject'] }}">
        <input type="hidden" name="message" value="{{ $input['message'] }}">

        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('contact.create') }}">修正する</a>
            <button type="submit">送信する</button>
        </div>
    </form>
@endsection
