<?php

// Include Pagination Class File

// Use 
$pagination = new Pagination();
$pagination->requestPageKey = $page_key; // default page
$pagination->baseUrl = $baseUrl;
$pagination->totalRecords = $total_records;
$pagination->recordsPerPage = 10;
$pagination->currentPage = 1; //$current_page = isset( $_GET[$page_key] ) ?  (int) ($_GET[$page_key])  : 1;
$pagination->request = $request; // custome request
$pagination->render('<div class="panel-footer text-center">', '</div>');
