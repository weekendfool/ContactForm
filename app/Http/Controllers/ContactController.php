<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    // 入力フォーム表示
    public function create(): View
    {
        return view('contacts.create');
    }

    // 入力内容を検証し、確認画面を表示
    public function confirm(StoreContactRequest $request): View
    {
        return view('contacts.confirm', [
            'input' => $request->validated(),
        ]);
    }

    // 確認画面からの送信を検証し、DBへ保存
    public function store(StoreContactRequest $request): RedirectResponse
    {
        Contact::create($request->validated());

        session(['contact_submitted' => true]);

        return redirect()->route('contact.complete');
    }

    // 完了メッセージを表示。直接アクセスされた場合は入力画面へ戻す
    public function complete(): View|RedirectResponse
    {
        if (! session()->pull('contact_submitted')) {
            return redirect()->route('contact.create');
        }

        return view('contacts.complete');
    }
}
