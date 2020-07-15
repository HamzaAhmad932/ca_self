@extends('layouts.guestv2')

@section('page-title', $property_name.' - Goodbye')

@section('content')
<style type="text/css">
    .main {
        display: flex;width: 100%;
        flex-direction: column;
        align-items: center;
    }
    header {
        all: unset;
        margin: 50px 0;
        display: flex;
        width: 100%;
        justify-content: center;
    }

    h1 {
        max-width: 100%;
        max-height: 132px;
        height: auto;
    }
    main {
        display: flex;
        width: 30vw;
        flex-direction: column;
        align-items: center;
    }
    .inner-text {
        -webkit-box-direction: normal;
        -webkit-box-orient: vertical;
        display: flex;
        color: rgb(50, 45, 185);
        font-size: 16px;
        font-family: Nunito;
        text-align: center;
        flex-direction: column;
        margin: 20px 0px 10px;
    }
    .inner-nested {
        display: flex;
        font-weight: 700;
    }
    a.website_btn {
        height: 36px;
        cursor: pointer;
        user-select: none;
        box-shadow: rgba(255, 255, 255, 0.06) 0px 1px 0px 1px inset, rgba(22, 29, 37, 0.1) 0px 1px 0px 0px;
        background-color: rgb(19, 206, 102);
        width: 100%;
        color: rgb(255, 255, 255);
        margin-top: 20px;
        font-weight: 700;
        font-size: 16px;
        font-family: Nunito;
        border-radius: 3px;
        padding: 0px 20px;
        border-width: 3px;
        border-style: solid;
        border-color: rgb(89, 239, 167);
        border-image: initial;
    }
</style>

<div class="main">
    <header>
        @if($property_logo != 'no_image.png')
            <img src="{{ asset('storage/uploads/property_logos') }}/{{ $property_logo }}" width="200">
        @endif
    </header>
    <main>
        <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iNzFweCIgaGVpZ2h0PSI2NXB4IiB2aWV3Qm94PSIwIDAgNzEgNjUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8IS0tIEdlbmVyYXRvcjogU2tldGNoIDQ3LjEgKDQ1NDIyKSAtIGh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaCAtLT4KICAgIDx0aXRsZT5sdWdnYWdlLWltZzwvdGl0bGU+CiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4KICAgIDxkZWZzPjwvZGVmcz4KICAgIDxnIGlkPSLwn5e8UGVyc29uYWwtYXJlYSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9ImFmdGVyLWNoZWNrLW91dCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTEyNC4wMDAwMDAsIC0yMDIuMDAwMDAwKSI+CiAgICAgICAgICAgIDxnIGlkPSJsdWdnYWdlLWltZyIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTI1LjAwMDAwMCwgMjAyLjAwMDAwMCkiPgogICAgICAgICAgICAgICAgPGNpcmNsZSBpZD0iT3ZhbCIgZmlsbD0iIzUwRjBBNSIgY3g9IjM3LjUiIGN5PSIzMi41IiByPSIzMi41Ij48L2NpcmNsZT4KICAgICAgICAgICAgICAgIDxnIGlkPSJMdWdnYWdlLU91dGxpbmUtKDEpIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwLjAwMDAwMCwgMTEuMDAwMDAwKSIgc3Ryb2tlPSIjMzIyREI5IiBzdHJva2Utd2lkdGg9IjIiPgogICAgICAgICAgICAgICAgICAgIDxwYXRoIGQ9Ik01NC4xNDIsNTIgTDMuODU4LDUyIEMxLjcyNyw1MiAwLDUwLjI3MyAwLDQ4LjE0MiBMMCwxNC44NTggQzAsMTIuNzI3IDEuNzI3LDExIDMuODU4LDExIEw1NC4xNDIsMTEgQzU2LjI3MywxMSA1OCwxMi43MjcgNTgsMTQuODU4IEw1OCw0OC4xNDIgQzU4LDUwLjI3MyA1Ni4yNzMsNTIgNTQuMTQyLDUyIEw1NC4xNDIsNTIgWiIgaWQ9IlBhdGgiPjwvcGF0aD4KICAgICAgICAgICAgICAgICAgICA8cGF0aCBkPSJNNTgsMTcuODYgTDU4LDE0Ljg1OCBDNTgsMTIuNzI3IDU2LjI3MywxMSA1NC4xNDIsMTEgTDUxLjE0LDExIEM1MS43NjMsMTQuNDkgNTQuNTEsMTcuMjM3IDU4LDE3Ljg2IEw1OCwxNy44NiBaIiBpZD0iUGF0aCI+PC9wYXRoPgogICAgICAgICAgICAgICAgICAgIDxwYXRoIGQ9Ik01OCw0OC4xNDIgTDU4LDQ1LjE0IEM1NC41MSw0NS43NjMgNTEuNzYzLDQ4LjUxIDUxLjE0LDUyIEw1NC4xNDIsNTIgQzU2LjI3Myw1MiA1OCw1MC4yNzMgNTgsNDguMTQyIEw1OCw0OC4xNDIgWiIgaWQ9IlBhdGgiPjwvcGF0aD4KICAgICAgICAgICAgICAgICAgICA8cGF0aCBkPSJNMCwxNy44NiBDMy40OSwxNy4yMzcgNi4yMzcsMTQuNDkgNi44NiwxMSBMMy44NTgsMTEgQzEuNzI3LDExIDAsMTIuNzI3IDAsMTQuODU4IEwwLDE3Ljg2IEwwLDE3Ljg2IFoiIGlkPSJQYXRoIj48L3BhdGg+CiAgICAgICAgICAgICAgICAgICAgPHBhdGggZD0iTTAsNDUuMTQgTDAsNDguMTQyIEMwLDUwLjI3MyAxLjcyNyw1MiAzLjg1OCw1MiBMNi44Niw1MiBDNi4yMzcsNDguNTEgMy40OSw0NS43NjMgMCw0NS4xNCBMMCw0NS4xNCBaIiBpZD0iUGF0aCI+PC9wYXRoPgogICAgICAgICAgICAgICAgICAgIDxwYXRoIGQ9Ik0xOSwxMSBMMTksMTAgQzE5LDQuNDg2IDIzLjQ4NiwwIDI5LDAgQzM0LjUxNCwwIDM5LDQuNDg2IDM5LDEwIEwzOSwxMSBMMTksMTEgTDE5LDExIFoiIGlkPSJQYXRoIj48L3BhdGg+CiAgICAgICAgICAgICAgICAgICAgPHBhdGggZD0iTTM1LDExIEwzNSwxMCBDMzUsNi43IDMyLjMsNCAyOSw0IEMyNS43LDQgMjMsNi43IDIzLDEwIEwyMywxMSBMMzUsMTEgTDM1LDExIFoiIGlkPSJQYXRoIj48L3BhdGg+CiAgICAgICAgICAgICAgICAgICAgPHBvbHlnb24gaWQ9IlBhdGgiIHBvaW50cz0iMTAgOCAxNiA4IDE2IDUyIDEwIDUyIj48L3BvbHlnb24+CiAgICAgICAgICAgICAgICAgICAgPHBvbHlnb24gaWQ9IlBhdGgiIHBvaW50cz0iNDIgOCA0OCA4IDQ4IDUyIDQyIDUyIj48L3BvbHlnb24+CiAgICAgICAgICAgICAgICAgICAgPHBvbHlnb24gaWQ9IlBhdGgiIHBvaW50cz0iMjcuMjA3IDQxLjA4OSAxOC44MTggMzcuNTQ5IDIxLjE3OCAzMS45NTYgMjkuNTY3IDM1LjQ5NiI+PC9wb2x5Z29uPgogICAgICAgICAgICAgICAgICAgIDxwb2x5Z29uIGlkPSJQYXRoIiBwb2ludHM9IjIzIDI3IDIzIDMyLjcyNSAyOS41NjcgMzUuNDk2IDI4LjkzMiAzNyAzOCAzNyAzOCAyNyI+PC9wb2x5Z29uPgogICAgICAgICAgICAgICAgICAgIDxwb2x5Z29uIGlkPSJQYXRoIiBwb2ludHM9IjM1LjU1NiAyNyAzOCAyNS44NTkgMzQuNTM2IDE4LjQ0IDIzLjQwOCAyMy42MzYgMjQuOTc4IDI3Ij48L3BvbHlnb24+CiAgICAgICAgICAgICAgICA8L2c+CiAgICAgICAgICAgIDwvZz4KICAgICAgICA8L2c+CiAgICA8L2c+Cjwvc3ZnPg==" class="">
        <div class="inner-text">
            @if($is_cancelled)
                <span>Your booking is cancelled.</span>
                <div class="inner-nested">
                    <span>{{--We hope seeing you back!--}}We hope to see you again soon!</span>
                </div>
            @else
                <span>It was great having you</span>
                <div class="inner-nested">
                    <span>We hope seeing you again!</span>
                </div>
            @endif
        </div>  
        @if($website)
            <a class="website_btn" href="{{ $website }}" style="text-decoration: none;text-align: center;"><span style="text-align: center;vertical-align: middle;">Continue to our website</span></a>
        @endif
    </main>
</div>


@endsection
@section('ajax_script')
    <script type="application/javascript">
        $(document).ready(function() {

            
        });
    </script>
@endsection
