<?php

$request = body_params();

if ( !empty($request) && is_object($request) ) {
  $obj = (object)[];
  $obj->recebido = $request;

  $obj->info_request = (object)[];
  $obj->info_request->request_uri = $REQUEST_URI;
  $obj->info_request->data_hora = DATA_HORA_ATUAL;
  $obj->info_request->request_method = $REQUEST_METHOD;
  $obj->info_request->remote_addr = REMOTE_ADDR;

  $locations_ip = @json_decode(@file_get_contents( DOCUMENT_ROOT . "/logs/locations_ip.json"));

  foreach ($locations_ip as $location) { 
    if ( isset($location->$REMOTE_ADDR) ) {
      $obj->info_request->region_aprox = $location->$REMOTE_ADDR;
    }
  }

  if ( !isset($obj->info_request->region_aprox) ) {
  
    $resip = @file_get_contents('https://ip.seeip.org/geoip/' . REMOTE_ADDR);
    $infoip = @json_decode($resip);
    if ( !empty(trim($infoip->city.$infoip->region)) ) {
      $obj->info_request->region_aprox = mb_strtoupper("{$infoip->city} / {$infoip->region}", 'UTF-8');
      
      array_push($locations_ip, [REMOTE_ADDR => $obj->info_request->region_aprox]);
      @file_put_contents( DOCUMENT_ROOT . "/logs/locations_ip.json", json_encode($locations_ip));
    }
  }

  $file_name = date('Y-m-d_His');
  $file_name .= $request->id_usuario      > 0 ? '_U' . str_pad($request->id_usuario, 5, '0', STR_PAD_LEFT) : '';
  $file_name .= $request->id_proprietario > 0 ? '_P' . str_pad($request->id_proprietario, 5, '0', STR_PAD_LEFT) : '';
  $file_name .= '--' . str_replace('/', '-', $REQUEST_URI);

  @file_put_contents( DOCUMENT_ROOT . "/logs/requests/{$file_name}.json", json_encode($obj));
}