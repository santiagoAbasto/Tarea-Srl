<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminCrud;
use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NewsletterSubscriberController extends Controller
{
    use HandlesAdminCrud;

    protected function modelClass(): string
    {
        return NewsletterSubscriber::class;
    }

    protected function indexQuery(Request $request): Builder
    {
        return NewsletterSubscriber::query()->orderByDesc('subscribed_at')->orderByDesc('id');
    }

    protected function storeRules(Request $request): array
    {
        return $this->rules();
    }

    protected function updateRules(Request $request, \Illuminate\Database\Eloquent\Model $model): array
    {
        return $this->rules((int) $model->id);
    }

    protected function rules(?int $ignoreId = null): array
    {
        return [
            'email' => ['required', 'email', Rule::unique('newsletter_subscribers', 'email')->ignore($ignoreId)],
            'is_active' => ['nullable', 'boolean'],
            'subscribed_at' => ['nullable', 'date'],
        ];
    }
}
