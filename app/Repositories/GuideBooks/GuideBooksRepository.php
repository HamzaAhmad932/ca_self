<?php


namespace App\Repositories\GuideBooks;



use App\GuideBook;
use App\GuideBookType;
use App\PropertyInfo;
use App\Repositories\TermsAndConditions\TermsAndConditionsRepository;
use App\TermsAndCondition;
use App\TermsAndConditionRentalBridge;
use Illuminate\Support\Facades\Auth;
use mysql_xdevapi\Exception;

class GuideBooksRepository
{
    /**
     * @param int $user_account_id
     * @return mixed
     */
    public function getTypes(int $user_account_id=0){
        return GuideBookType::where('is_user_defined',0)
            ->orWhere('user_account_id', $user_account_id)
            ->get(['id', 'title', 'is_user_defined']);
    }
    /** Create New
     * @param array $data
     * @return mixed
     */
    public function  create(array $data)
    {
        $newTerm=Auth::user()->user_account->guideBooks()->create([
            'user_id'=>Auth::user()->id,
            'internal_name'=>$data['internal_name'],
            'text_content'=>$data['text_content'],
            'icon'=>$data['icon'],
            'guide_book_type_id'=>$data['type_id'],
            'status'=>$data['status'],
        ]);
        resolve(TermsAndConditionsRepository::class)->attachProperties($data['selected_properties'],PropertyInfo::GUIDE_BOOK,$newTerm->id);
       return true;
    }



    /**Update Old
     * @param array $data
     * @return bool
     */
    public  function  update(array $data)
    {

        GuideBook::where('id',$data['serve_id'])->update([
            'internal_name'=>$data['internal_name'],
            'text_content'=>$data['text_content'],
            'icon'=>$data['icon'],
            'guide_book_type_id'=>$data['type_id'],
            'status'=>$data['status'],
        ]);
        resolve(TermsAndConditionsRepository::class)->attachProperties($data['selected_properties'],PropertyInfo::GUIDE_BOOK,$data['serve_id'],true);
        return true;
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateStatus(int $id, array $data){
        GuideBook::where('id',$id)->update($data);
        return true;
    }


    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        return true;
    }

    /** Get A Single Record
     * @param int $id
     * @return mixed
     */
    public function getOne(int $id)
    {
        return Auth::user()->user_account->guideBooks()->where('id',$id)->get();
    }


    /** All Records
     * @return mixed
     */
    public function getAll()
    {
        return Auth::user()->user_account->guideBooks;
    }

    public function getGuideBooksByPropertyAndRoomIds(int $user_account_id, int $property_id, int $room_id)
    {
        $where = [
            ['user_account_id',$user_account_id],
            ['status', GuideBook::STATUS_ACTIVE]
        ];

        $guide_books = get_related_records(['id','guide_book_type_id'], PropertyInfo::GUIDE_BOOK, $where, $property_id, $room_id);

        $guide_book_id = $guide_books->pluck('id');
        $guide_book_type_ids = $guide_books->pluck('guide_book_type_id');

        return GuideBookType::whereIn('id',$guide_book_type_ids)->with(['guideBooks'=> function($query) use ($guide_book_id) {
            $query->whereIn('id', $guide_book_id);
        }])->orderBy('priority', 'desc')->get();
    }

    /** Guide Book Types Methods  */
    /**
     * @param array $data
     * @return bool
     */
    public function  createType(array $data)
    {
        $user = Auth::user();
        GuideBookType::create([
            'user_id'=>$user->id,
            'user_account_id'=>$user->user_account->id,
            'is_user_defined'=>1,
            'title'=>$data['title'],
            'icon'=>$data['icon'],
            'priority'=>$data['priority']
        ]);
        return true;
    }

    /**
     * @param array $data
     * @return bool
     */
    public  function  updateType(array $data)
    {

        GuideBookType::where('id',$data['serve_id'])->update([
            'title'=>$data['title'],
            'icon'=>$data['icon'],
            'priority'=>$data['priority']
        ]);
        return true;
    }
    /**
     * @param int $id
     * @return bool
     */
    public function deleteType(int $id)
    {
        GuideBookType::find($id)->delete();
        return true;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getOneType(int $id)
    {
        return GuideBookType::where([
                ['user_account_id', auth()->user()->user_account_id],
                ['id', $id]
            ])->get();
    }
}
