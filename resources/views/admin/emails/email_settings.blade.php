@extends('layouts.admin')
@section('content')

    <style>
        @media (max-width: 470px) {
            .m-widget5 .m-widget5__item .m-widget5__content .m-widget5__stats2{
                width: 4rem;
            }

            .m-dropdown.m-dropdown--align-right .m-dropdown__wrapper
            {
                /*background-color: red;*/
                right: auto;

            }
            .m-dropdown__arrow
            {
                right: auto !important;

            }
        }
    </style>
    <div id="app">
        <emails></emails>
    </div>


@endsection
