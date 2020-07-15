<?php

namespace App\Http\Controllers\v2\client;

use App\EmailCustomContent;
use App\EmailTypeHead;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmailsContentStoreRequest;
use App\Http\Resources\General\Emails\EmailContentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailsController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function emailSettings()
    {
        return view('v2.client.settings.email-settings');
    }

    /**
     * @return JsonResponse
     */
    public function getDefaultEmails($type_id = null)
    {
        $data = EmailTypeHead::where([['system_email', 0], ['customizable', 1], ['status', 1]])

            ->when($type_id, function ($query, $type_id) {
                return $query->where('id', $type_id);
            })->with(
            [
                'defaultContents',
                'customContents' => function ($query) {
                    $query->where('user_account_id', auth()->user()->user_account_id);
                }
            ]
        )->orderBy('title')->get();

        $data->transform(function ($instance) {
            return [

                "id" => $instance->id,
                "type" => $instance->type,
                "temp_vars"=>getEmailTypeTempVars($instance->type),
                "title" => $instance->title,
                "icon" => $instance->icon,

                "for_whom" => EmailContentResource::collection(
                    $instance->customContents->count()
                        ? $instance->customContents
                        : $instance->defaultContents
                ),
            ];
        });

        //dd($data->toArray());
        return $this->apiSuccessResponse(200, $data);
    }

    public function getEmailTypes(){

        $data = EmailTypeHead::where([['system_email', 0], ['customizable', 1], ['status', 1]])->orderBy('title')->get();

        return $this->apiSuccessResponse(200, $data);
    }

    /**
     * @param EmailsContentStoreRequest $request
     * @return JsonResponse
     */
    public function updateDefaultEmail(EmailsContentStoreRequest $request)
    {
        try {
            $data = $request->validated();
            if (EmailCustomContent::updateOrCreate(
                [
                    'user_account_id' => auth()->user()->user_account_id,
                    'email_receiver_id' => $request->email_receiver_id,
                    'email_type_head_id' => $request->email_type_head_id
                ],
                ['content' => $data['email_content'], 'user_id' => auth()->user()->id])
            ) {
                $response = $this->apiSuccessResponse("200", null, "Updated Successfully!");
            } else {
                $response = $this->apiErrorResponse("Invalid Email", 500);
            }
        } catch (\Exception $e) {
             log_exception_by_exception_object($e,
                json_encode([
                    "file" => __FILE__,
                    "function" => __FUNCTION__,
                    "line" => __LINE__,
                ])
            );
            $response = $this->apiErrorResponse("Something Went Wrong!", 422, $e->getMessage());
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Validation\ValidationException
     */
    public function revertToDefaultEmail(Request $request)
    {

        $this->validate($request, ['email_receiver_id' => 'required|int', 'email_type_head_id' => 'required|int']);

        $email_receiver_id = $request->email_receiver_id;

        $email_type = EmailTypeHead::where('id', $request->email_type_head_id)->with(
            [
                'defaultContents' => function ($query) use ($email_receiver_id) {
                    $query->where('email_receiver_id', $email_receiver_id);
                },
                'customContents' => function ($query) use ($email_receiver_id) {
                    $query->where('user_account_id', auth()->user()->user_account_id)
                        ->where('email_receiver_id', $email_receiver_id);
                }
            ]
        )->first();

        if ($email_type->customContents->count()) {
            $email_type->customContents->first()->delete();
        }


        return EmailContentResource::collection($email_type->defaultContents);
    }
}
