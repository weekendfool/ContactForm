@extends('layouts.app')

@section('title', 'お問い合わせ一覧')

@section('content')
    <div class="top-bar">
        <h1>お問い合わせ一覧</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-secondary">ログアウト</button>
        </form>
    </div>

    <form method="GET" action="{{ route('admin.contacts.index') }}" class="filter-form">
        <div class="filter-fields">
            <div>
                <label for="status">対応状況</label>
                <select id="status" name="status">
                    <option value="">すべて</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected($status === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="keyword">キーワード（お名前・件名）</label>
                <input type="text" id="keyword" name="keyword" value="{{ $keyword }}" placeholder="例: 返品、山田">
            </div>
        </div>
        <div class="actions">
            <button type="submit">絞り込む</button>
            <a class="btn btn-secondary" href="{{ route('admin.contacts.index') }}">クリア</a>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>お名前</th>
                <th>件名</th>
                <th>対応状況</th>
                <th>受付日時</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($contacts as $contact)
                <tr>
                    <td><a href="{{ route('admin.contacts.show', $contact) }}">{{ $contact->name }}</a></td>
                    <td>{{ $contact->subject }}</td>
                    <td>{{ $contact->status->label() }}</td>
                    <td>{{ $contact->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        @if ($status !== '' || $keyword !== '')
                            条件に一致するお問い合わせはありません。
                        @else
                            お問い合わせはまだありません。
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="actions">
        {{ $contacts->links() }}
    </div>
@endsection
