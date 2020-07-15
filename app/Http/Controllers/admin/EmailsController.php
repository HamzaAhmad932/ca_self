<?php

namespace App\Http\Controllers\admin;

use App\EmailDefaultContent;
use App\EmailTypeHead;
use App\Http\Requests\EmailsContentStoreRequest;
use App\Http\Resources\General\Emails\EmailHeadsResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmailsController extends Controller
{

    public function __construct() {

    }

    public function getDefaultEmails(){
        $data = EmailHeadsResource::collection(EmailTypeHead::where('system_email', 0)->get());
        return $this->apiSuccessResponse(200,$data);
    }

    public function updateDefaultEmail(EmailsContentStoreRequest $request){
        try{
            $data=$request->validated();
            $email = EmailDefaultContent::find($data['id']);
            if(!empty($email)){
                $email->content = $data['email_content'];
                $email->save();
                $response = $this->apiSuccessResponse("200",null,"Updated Successfully!");
            }else{
                $response = $this->apiErrorResponse("Invalid Email",500);
            }
        }catch (\Exception $e){
            dump($e->getMessage());
            log_exception_by_exception_object($e,[
                "file"=>__FILE__,
                "function"=>__FUNCTION__,
                "line"=>__LINE__,
            ]);
            $response = $this->apiErrorResponse("Something Went Wrong!",422,$e->getMessage());
        }
        return $response;
    }
}
