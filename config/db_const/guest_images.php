<?php


return [
    'status'=>[
        0=> 'Pending',
        1=> 'Accepted',
        2=> 'Rejected'
    ],
    'status_with_badge'=>[
        0=> ['text'=>'Pending', 'badge'=> 'badge badge-warning', 'display'=> true],
        1=> ['text'=> 'Accepted', 'badge'=> 'badge badge-success', 'display'=> true],
        2=> ['text'=> 'Rejected', 'badge'=> 'badge badge-danger', 'display'=> true]
    ],
    'status_info'=>[
        0=> 'Pending for ',
        1=> 'Accepted on ',
        2=> 'Rejected on '
    ]
];