@extends('v2.guest.app_pre_checkin')

@section('page_content')
            <div class="tp-header" style="background: #f9fafc !important;">
                <h4 class="mb-0">{!! $terms['checkbox_text'] !!}</h4>
            </div>
            <div class="gp-box" style="padding-top: 4rem !important;">
                <div class="gp-box-content box-hv">
                    <div class="gp-inset">
                        <div class="tac-content-wrapper">
                            {!! $terms['text_content'] !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="gp-footer" style="background-color: #f9fafc !important;">
                <div class="row">
                </div>
            </div>
@endsection()
