<?php
return [
    "filters" => [
        "recordsPerPage" => 10,
        "page" => 1,
        'columns' => ['id', 'user_account_id'],
        'relations' => ['booking_info','properties_info'],
        "sort" => [
            "sortOrder" => "ASC",
            "sortColumn" => "name",
        ],
        "constraints" => [
            ["id" , ">" , 20],
            ["available_on_pms" , "=" , 1], //any other constraints
        ],
        "search" => [
            "searchInColumn" => [
                0 => "id",
                1 => "name",
                2 => "address",
                3 => "currency_code"], //any other columns
          "searchStr" => "search Text here", // any string to search
        ],
  ],
];

/*
* --- SAMPLE JSON TO RE-USE IN VUE Objects Begin ---
          filters:{
          recordsPerPage:10,
          page:1,
          columns: ["id","user_account_id"],
          relations: ["booking_info", "properties_info"],
          sort:{
            sortOrder:"ASC",
            sortColumn:"name",
          },
          constraints:[
            ["available_on_pms","=",1],
          ],
          search:{
            searchInColumn:["id","name","address","currency_code"],
            searchStr:""
          },
        }, //Datatable filters Object End
  * --- SAMPLE JSON TO RE-USE IN VUE Objects ENDS ---
 */
