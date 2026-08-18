@extends('layouts.app')

@section('title', '送信完了')

@section('content')
    <h1>お問い合わせありがとうございました</h1>
    <p>内容を確認の上、担当者よりご連絡いたします。</p>

    <div class="actions">
        <a class="btn" href="{{ route('contact.create') }}">トップへ戻る</a>
    </div>
@endsection
