@extends('layouts.app')

@section('title', 'お問い合わせ詳細')

@section('content')
    <h1>お問い合わせ詳細</h1>

    @if (session('status_message'))
        <p class="flash">{{ session('status_message') }}</p>
    @endif

    <dl>
        <dt>お名前</dt>
        <dd>{{ $contact->name }}</dd>

        <dt>メールアドレス</dt>
        <dd>{{ $contact->email }}</dd>

        <dt>件名</dt>
        <dd>{{ $contact->subject }}</dd>

        <dt>お問い合わせ内容</dt>
        <dd>{{ $contact->message }}</dd>

        <dt>受付日時</dt>
        <dd>{{ $contact->created_at->format('Y-m-d H:i') }}</dd>
    </dl>

    <form method="POST" action="{{ route('admin.contacts.update', $contact) }}">
        @csrf
        @method('PATCH')

        <label for="status">対応状況</label>
        <select id="status" name="status">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected($contact->status === $status)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="error">{{ $message }}</p>
        @enderror

        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('admin.contacts.index') }}">一覧に戻る</a>
            <button type="submit">更新する</button>
        </div>
    </form>
@endsection
