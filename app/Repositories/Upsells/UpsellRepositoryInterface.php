<?php


namespace App\Repositories\Upsells;


use App\Http\Requests\UpsellStoreRequest;

interface UpsellRepositoryInterface
{
    public function getUpsellTypes(int $user_account_id = 0,$get_active_only=true,$serve_id=0);
    public function storeUpsells(UpsellStoreRequest $request);
    public function bridgeAllPropertiesWithRooms(int $user_account_id, string $model, int $serve_id = 0);
    public function getUserUpsells(int $user_account_id, int $upsell_id);
    public function changeUpsellStatus(int $upsell_id, bool $status);
}
