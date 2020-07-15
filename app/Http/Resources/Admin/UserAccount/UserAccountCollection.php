<?php

namespace App\Http\Resources\Admin\UserAccount;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserAccountCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->transform(function ($item) {
                return [
                    'user_account' => $item,
                    'successful' => number_format($item->successful_transactions->sum('price'),2),
                    'failed' => number_format($item->failed_transactions->sum('price'),2),
                    'scheduled' => number_format($item->scheduled_transactions->sum('price'),2),
                ];
            }),
            'user_role' => auth()->user()->roles->pluck('name'),
            'user_type' => auth()->user()->user_account->account_type,
            'links' => [],
        ];
    }
}
