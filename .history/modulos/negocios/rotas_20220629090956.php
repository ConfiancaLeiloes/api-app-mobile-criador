<?php

// str_replace(['-'], '_', $array[0])


$app->get('/negocios/' . get_last_path_rota(), ClienteController::class . ':teste2');
