<?php


namespace App\Traits\Resources\General;


use App\GuestImage;
use App\GuestImageDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

trait BookingDetail
{

    public function getGuestImages(){

        $guest_images = $this->guest_images;

        if(!empty($guest_images)){

            $img_arr = [];

            if($guest_images->where('type', 'passport')->count() > 0){

                $arr['title'] = 'Passport scan';
                $arr['count'] = $guest_images->where('type', 'passport')->count();
                array_push($img_arr, $arr);
            }

            if($guest_images->where('type', 'credit_card')->count() > 0){

                $arr['title'] = 'Credit Card scan';
                $arr['count'] = $guest_images->where('type', 'credit_card')->count();
                array_push($img_arr, $arr);
            }

            return $img_arr;
        }

        return [];
    }

    public function getBookingPayments(){

        $payments = [
            'pending_payments'=> [],
            'declined_payments'=> [],
            'accepted_payments'=> [],
        ];

    }

    public function getClassifiedImages($images){

        $response = [
            'documents_to_check'=>[],
            'accepted_documents'=>[],
            'rejected_documents'=>[],
            'deleted_documents'=>[],
            'all'=> []
        ];

        foreach($images as $img){

            $filtered = [
                'id'=> $img->id,
                'image'=> '/storage/uploads/guestImages/'.$img->image,
                'title'=> ucwords(str_replace('_', ' ', $img->type)),
                'uploaded_info'=> 'Uploaded on '.Carbon::parse($img->created_at)->format('M d, Y'),
                'status_info'=> Config::get('db_const.guest_images.status_info.'.$img->status).Carbon::parse($img->updated_at)->format('M d, Y'),
                'status'=> $img->status,
                'type' => $img->type,
                'client_action'=> !($img->type == 'selfie' || $img->type == 'signature')
            ];


            if ($img instanceof GuestImageDetail) {
                if (!empty($img->is_deleted)) {
                    array_push($response['deleted_documents'], $filtered);
                }
                continue;
            } elseif ($img->status == GuestImage::STATUS_PENDING ){
                array_push($response['documents_to_check'], $filtered);
            } elseif ($img->status == GuestImage::STATUS_REJECTED ){
                array_push($response['rejected_documents'], $filtered);
            } elseif ($img->status == GuestImage::STATUS_ACCEPTED){
                array_push($response['accepted_documents'], $filtered);
            }

            array_push($response['all'], $filtered);
        }

        return $response;

    }
}