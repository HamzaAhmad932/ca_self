@extends('layouts.admin')
@section('content')

    <!-- Begin Portlet -->
    <div class="m-portlet m-portlet--full-height ">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <h3 class="m-portlet__head-text">
                        All Notifications
                    </h3>
                </div>
            </div>

        </div>
        <div class="m-portlet__body">
            <div class="tab-content">
                <div class="tab-pane active" id="m_widget2_tab1_content">
                    <!--Begin-Timeline 3 -->
                    <div class="m-timeline-3">
                        <div class="m-timeline-3__items">


                                <div id="app-7">

                                        <todo-item
                                                v-for="item in allNotificationList"
                                                v-bind:todo="item"
                                                >
                                        </todo-item>

                                </div>



                        </div>
                    </div>
                    <!--End-Timeline 3 -->

                </div>

            </div>
        </div>
    </div>
    <!--End-Portlet-->


@endsection

@section('ajax_script')

    <script type="application/javascript">


        Vue.component('todo-item', {

            props: ['todo'],
            template:`
            <div class="m-timeline-3__item m-timeline-3__item--info" @click="markAs($event)">
                <span class="m-timeline-3__item-time">@{{ todo.created_at | moment("h:mm") }}</span>
                <div class="m-timeline-3__item-desc">
                <span class="m-timeline-3__item-text">
                    @{{todo.data.userData}}
                </span>
                    <br>
                    <span class="m-timeline-3__item-user-name">
                    <a href="#" class="m-link m-link--metal m-timeline-3__item-link">
                    @{{ todo.created_at | moment("dddd, MMMM Do YYYY") }}
                </a>
            </span>
            </div>
        </div>
        `
        })

        var app7 = new Vue({
            el: '#app-7',
            data: {
                allNotificationList: @json(auth()->user()->Notifications)
            },
            mounted(){

                console.log(@json(auth()->user()->Notifications));

            }
                    })




  </script>
@endsection