<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateContactStatusRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    // お問い合わせ一覧表示（対応状況・キーワードで絞り込み可能）
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $keyword = $request->string('keyword')->toString();

        $contacts = Contact::query()
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($keyword !== '', fn ($query) => $query->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contacts.index', [
            'contacts' => $contacts,
            'statuses' => ContactStatus::cases(),
            'status' => $status,
            'keyword' => $keyword,
        ]);
    }

    // お問い合わせ詳細表示
    public function show(Contact $contact): View
    {
        return view('admin.contacts.show', [
            'contact' => $contact,
            'statuses' => ContactStatus::cases(),
        ]);
    }

    // 対応状況の更新
    public function update(UpdateContactStatusRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($request->validated());

        return redirect()
            ->route('admin.contacts.show', $contact)
            ->with('status_message', '対応状況を更新しました');
    }
}
